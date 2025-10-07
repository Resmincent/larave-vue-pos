<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->string('query');

        $sales = Sale::with([
            // ambil customer dgn user-nya (nama ada di users)
            'customer' => function ($q) {
                $q->select('id', 'user_id')
                    ->with(['user:id,name']);
            },
            'user:id,name', // cashier
        ])
            ->when($query, fn($w) => $w->where('code', 'like', "%{$query}%"))
            ->orderByDesc('sold_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('sales/Index', [
            'sales'   => $sales,
            'filters' => ['query' => $query],
        ]);
    }

    public function create()
    {
        return Inertia::render('sales/Create', [
            'customers' => Customer::select('id', 'user_id')
                ->with(['user:id,name,email'])
                ->orderBy(
                    User::select('name')->whereColumn('users.id', 'customers.user_id')
                )
                ->get(),
            'products' => Product::get(['id', 'sku', 'name', 'unit', 'sell_price']),
            'methods'  => PaymentMethod::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'code'     => Sale::generateCode(),
        ]);
    }



    public function store(Request $request)
    {
        // pastikan cashier (user login) ada
        if (!Auth::check()) {
            abort(401, 'Anda harus login sebagai cashier/user.');
        }

        $data = $request->validate([
            'customer_id'         => 'nullable|exists:customers,id',
            'code'                => 'required|string|unique:sales,code',
            'status'              => 'required|in:' . implode(',', [
                Sale::STATUS_OPEN,
                Sale::STATUS_PAID,
                Sale::STATUS_VOID,
            ]),
            'note'                => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.qty'         => 'required|integer|min:1',
            'items.*.sell_price'  => 'required|numeric|min:0',
            'items.*.discount'    => 'nullable|numeric|min:0',
            'items.*.tax'         => 'nullable|numeric|min:0',
            'payments'            => 'required|array|min:1',
            // konsistenkan key dengan yang dipakai saat create Payment
            'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payments.*.amount'            => 'required|numeric|min:0',
            'payments.*.note'              => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $subtotal      = 0;
            $discountTotal = 0;
            $taxTotal      = 0;

            foreach ($data['items'] as &$it) {
                $it['discount']   = $it['discount'] ?? 0;
                $it['tax']        = $it['tax'] ?? 0;
                $it['line_total'] = ($it['sell_price'] * $it['qty'] - $it['discount'] + $it['tax']);

                $subtotal      += ($it['sell_price'] * $it['qty']);
                $discountTotal += $it['discount'];
                $taxTotal      += $it['tax'];
            }

            $grandTotal = $subtotal - $discountTotal + $taxTotal;

            $paidTotal = collect($data['payments'] ?? [])->sum('amount');
            $changeDue = max(0, $paidTotal - $grandTotal);

            // OPEN => sold_at null, PAID => now()
            $soldAt = $data['status'] === Sale::STATUS_PAID ? now() : null;

            $sale = Sale::create([
                'customer_id'    => $data['customer_id'] ?? null,
                'code'           => $data['code'],
                'status'         => $data['status'],
                'subtotal'       => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total'      => $taxTotal,
                'grand_total'    => $grandTotal,
                'paid_total'     => $paidTotal,
                'change_due'     => $changeDue,
                'user_id'        => Auth::id(), // cashier wajib ada
                'sold_at'        => $soldAt,    // bisa null untuk OPEN
                'note'           => $data['note'] ?? null,
            ]);

            $data['code'] = $data['code'] ?: Sale::generateCode();


            foreach ($data['items'] as $it) {
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $it['product_id'],
                    'qty'        => $it['qty'],
                    'price'      => $it['sell_price'],
                    'discount'   => $it['discount'],
                    'tax'        => $it['tax'],
                    'line_total' => $it['line_total'],
                ]);
            }

            // Kurangi stok hanya ketika PAID
            if ($data['status'] === Sale::STATUS_PAID) {
                foreach ($data['items'] as $index => $it) {
                    // kunci baris stok
                    $inv = Inventory::lockForUpdate()
                        ->firstOrCreate(['product_id' => $it['product_id']], ['qty' => 0]);

                    $available = $inv->qty;

                    if ($available < $it['qty']) {
                        // lempar exception agar transaksi rollback
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "items.$index.qty" =>
                            "Stok produk tidak mencukupi. Stok tersedia: {$available}, diminta: {$it['qty']}.",
                        ]);
                    }

                    $inv->qty -= $it['qty'];
                    $inv->save();

                    // Movement keluar gudang (OUT) — qty negatif atau type OUT
                    StockMovement::create([
                        'product_id'  => $it['product_id'],
                        'qty_change'  => -$it['qty'],
                        'type'        => 'OUT',
                        'source_type' => Sale::class,
                        'source_id'   => $sale->id,
                        'note'        => 'Sale PAID: ' . $sale->code,
                        'created_at'  => now(),
                    ]);
                }
            }

            foreach (($data['payments'] ?? []) as $p) {
                Payment::create([
                    'payment_method_id' => $p['payment_method_id'], // disamakan dengan validasi
                    'sale_id'           => $sale->id,
                    'purchase_id'       => null,
                    'amount'            => $p['amount'],
                    'paid_at'           => now(),
                    'cash_session_id'   => null,
                    'note'              => $p['note'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale created successfully');
    }

    public function show(Sale $sale)
    {
        $sale->load([
            'customer:id,name',
            'user:id,name',
            'items.product:id,name,sku,unit',
        ]);

        return Inertia::render('sales/Show', [
            'sale' => $sale,
        ]);
    }

    public function void(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            if ($sale->status === Sale::STATUS_PAID) {
                $items = $sale->items()->get();

                foreach ($items as $item) {
                    $inv = Inventory::lockForUpdate()
                        ->firstOrCreate(['product_id' => $item->product_id], ['qty' => 0]);

                    // kembalikan stok
                    $inv->qty += $item->qty;
                    $inv->save();

                    StockMovement::create([
                        'product_id'  => $item->product_id,
                        'qty_change'  => $item->qty, // balikkan stok
                        'type'        => 'RETURN_SALE',
                        'source_type' => Sale::class,
                        'source_id'   => $sale->id,
                        'note'        => 'VOID penjualan: ' . $sale->code,
                        'created_at'  => now(),
                    ]);
                }
            }

            $sale->update(['status' => Sale::STATUS_VOID]);
        });

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Transaksi dibatalkan (VOID).');
    }
}
