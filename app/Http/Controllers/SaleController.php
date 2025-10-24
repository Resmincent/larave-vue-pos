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
use App\Models\Tax;
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
            'customer' => function ($q) {
                $q->select('id', 'user_id', 'phone', 'address')
                    ->with(['user:id,name']);
            },
            'user:id,name',
        ])
            ->when($query, fn($w) => $w->where('code', 'like', "%{$query}%"))
            ->orderByDesc('sold_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('sales/Index', [
            'sales'   => $sales,
            'filters' => ['query' => $query],
            'methods' => PaymentMethod::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
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
            'products' => Product::get(['id', 'sku', 'name', 'unit', 'sell_price', 'is_active']),
            'methods'  => PaymentMethod::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'taxes'    => Tax::orderBy('name')->get(['id', 'name', 'rate']),
            'code'     => Sale::generateCode(),
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            abort(401, 'Anda harus login sebagai cashier/user.');
        }

        $data = $request->validate([
            'customer_id'         => 'nullable|exists:customers,id',
            'code'                => 'required|string|unique:sales,code',
            'status'              => 'required|in:' . implode(',', [
                Sale::STATUS_OPEN,
                Sale::STATUS_PAID,
                Sale::STATUS_VOID
            ]),
            'note'                => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.qty'         => 'required|integer|min:1',
            'items.*.sell_price'  => 'required|numeric|min:0',
            'items.*.discount'    => 'nullable|numeric|min:0',
            'items.*.tax_id'      => 'nullable|exists:taxes,id',
            'payments'            => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payments.*.amount'            => 'required|numeric|min:0',
            'payments.*.note'              => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            // siapkan map pajak sekalian untuk hindari N+1
            $taxRates = collect($data['items'])
                ->pluck('tax_id')
                ->filter()
                ->unique()
                ->values();

            $taxMap = $taxRates->isNotEmpty()
                ? Tax::whereIn('id', $taxRates)->pluck('rate', 'id') // [tax_id => rate]
                : collect();

            $subtotal      = 0;
            $discountTotal = 0;
            $taxTotal      = 0;

            // hitung semua angka dan siapkan payload sale_items
            $saleItemsPayload = [];
            foreach ($data['items'] as $it) {
                $qty        = (int) ($it['qty'] ?? 0);
                $price      = (float) ($it['sell_price'] ?? 0);
                $discount   = (float) ($it['discount'] ?? 0);
                $taxId      = $it['tax_id'] ?? null;

                $lineBase   = ($price * $qty) - $discount;

                // pajak dihitung dari persentase (Tax.rate)
                $taxRate    = $taxId ? (float) ($taxMap[$taxId] ?? 0) : 0;
                $taxAmount  = round($lineBase * ($taxRate / 100));

                $lineTotal  = $lineBase + $taxAmount;

                $subtotal      += ($price * $qty);
                $discountTotal += $discount;
                $taxTotal      += $taxAmount;

                $saleItemsPayload[] = [
                    'product_id' => $it['product_id'],
                    'qty'        => $qty,
                    'price'      => $price,
                    'discount'   => $discount,
                    'tax_id'     => $taxId,
                    'line_total' => $lineTotal,
                ];
            }

            $grandTotal = $subtotal - $discountTotal + $taxTotal;
            $paidTotal  = collect($data['payments'] ?? [])->sum('amount');
            $changeDue  = max(0, $paidTotal - $grandTotal);
            $soldAt     = $data['status'] === Sale::STATUS_PAID ? now() : null;

            $sale = Sale::create([
                'customer_id'    => $data['customer_id'] ?? null,
                'code'           => $data['code'] ?: Sale::generateCode(),
                'status'         => $data['status'],
                'subtotal'       => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total'      => $taxTotal,
                'grand_total'    => $grandTotal,
                'paid_total'     => $paidTotal,
                'change_due'     => $changeDue,
                'user_id'        => Auth::id(),
                'sold_at'        => $soldAt,
                'note'           => $data['note'] ?? null,
            ]);

            // simpan item
            foreach ($saleItemsPayload as $row) {
                SaleItem::create($row + ['sale_id' => $sale->id]);
            }

            // potong stok jika PAID
            if ($data['status'] === Sale::STATUS_PAID) {
                $this->reduceStockForPaidSale($sale);
            }

            // simpan pembayaran
            foreach (($data['payments'] ?? []) as $p) {
                Payment::create([
                    'payment_method_id' => $p['payment_method_id'],
                    'sale_id'           => $sale->id,
                    'purchase_id'       => null,
                    'amount'            => $p['amount'],
                    'paid_at'           => now(),
                    'cash_session_id'   => null,
                    'note'              => $p['note'] ?? null,
                ]);
            }
        });

        return redirect()->route('sales.index')->with('success', 'Sale created successfully');
    }

    public function show(Sale $sale)
    {
        $sale->load([
            'customer:id,user_id,phone,address',
            'customer.user:id,name,email',
            'user:id,name',
            'saleItems:id,sale_id,product_id,qty,price,discount,tax_id,line_total',
            'saleItems.product:id,name,sku,unit',
            'saleItems.taxs:id,name,rate', // sesuai nama method di model SaleItem
        ]);

        return Inertia::render('sales/Show', [
            'sale' => $sale,
        ]);
    }

    public function void(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            if ($sale->status === Sale::STATUS_PAID) {
                $items = $sale->saleItems()->get(); // <-- perbaikan: pakai saleItems()

                foreach ($items as $item) {
                    $inv = Inventory::lockForUpdate()
                        ->firstOrCreate(['product_id' => $item->product_id], ['qty' => 0]);

                    // kembalikan stok
                    $inv->qty += $item->qty;
                    $inv->save();

                    StockMovement::create([
                        'product_id'  => $item->product_id,
                        'qty_change'  => $item->qty,
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

        return redirect()->route('sales.show', $sale)->with('success', 'Transaksi dibatalkan (VOID).');
    }

    /* ====================== Helper ====================== */

    private function reduceStockForPaidSale(Sale $sale): void
    {
        $sale->loadMissing('saleItems');

        foreach ($sale->saleItems as $index => $it) {
            $inv = Inventory::lockForUpdate()
                ->firstOrCreate(['product_id' => $it->product_id], ['qty' => 0]);

            if ($inv->qty < $it->qty) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "items.$index.qty" =>
                    "Stok produk tidak mencukupi. Stok tersedia: {$inv->qty}, diminta: {$it->qty}.",
                ]);
            }

            $inv->qty -= $it->qty;
            $inv->save();

            StockMovement::create([
                'product_id'  => $it->product_id,
                'qty_change'  => -$it->qty,
                'type'        => 'SALE',
                'source_type' => Sale::class,
                'source_id'   => $sale->id,
                'note'        => 'Sale PAID: ' . $sale->code,
                'created_at'  => now(),
            ]);
        }
    }

    public function pay(Request $request, Sale $sale)
    {
        // validasi pembayaran baru yang masuk
        $data = $request->validate([
            'payments'                        => 'required|array|min:1',
            'payments.*.payment_method_id'    => 'required|exists:payment_methods,id',
            'payments.*.amount'               => 'required|numeric|min:0',
            'payments.*.note'                 => 'nullable|string',
        ]);

        DB::transaction(function () use ($sale, $data) {
            // lock baris sale agar aman dari race condition
            $sale = Sale::whereKey($sale->id)->lockForUpdate()->first();

            if ($sale->status === Sale::STATUS_VOID) {
                abort(422, 'Transaksi sudah VOID dan tidak bisa dibayar.');
            }
            if ($sale->status === Sale::STATUS_PAID) {
                abort(422, 'Transaksi sudah PAID.');
            }

            $wasOpen = ($sale->status === Sale::STATUS_OPEN);

            // simpan pembayaran baru
            $additionalPaid = collect($data['payments'])->sum('amount');
            foreach ($data['payments'] as $p) {
                Payment::create([
                    'payment_method_id' => $p['payment_method_id'],
                    'sale_id'           => $sale->id,
                    'purchase_id'       => null,
                    'amount'            => $p['amount'],
                    'paid_at'           => now(),
                    'cash_session_id'   => null,
                    'note'              => $p['note'] ?? null,
                ]);
            }

            // hitung total bayar terbaru & tentukan status
            $newPaidTotal = ($sale->paid_total ?? 0) + $additionalPaid;
            $changeDue    = max(0, $newPaidTotal - $sale->grand_total);
            $newStatus    = $newPaidTotal >= $sale->grand_total
                ? Sale::STATUS_PAID
                : Sale::STATUS_OPEN;

            // update kolom-kolom sale
            $sale->update([
                'paid_total' => $newPaidTotal,
                'change_due' => $changeDue,
                'status'     => $newStatus,
                'sold_at'    => $newStatus === Sale::STATUS_PAID ? now() : $sale->sold_at,
            ]);

            // potong stok HANYA ketika berpindah dari OPEN → PAID
            if ($wasOpen && $newStatus === Sale::STATUS_PAID) {
                $this->reduceStockForPaidSale($sale);
            }
        });

        return redirect()->route('sales.index', $sale)
            ->with('success', 'Pembayaran disimpan. Status transaksi diperbarui.');
    }
}
