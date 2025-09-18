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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->string('query');
        $sales = Sale::with(['customer', 'user'])->when($query, fn($w) => $w->where('code', 'like', "%$query%"))
            ->orderBy('sold_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => ['query' => $query],
        ]);
    }

    public function create()
    {
        return Inertia::render('Sales/Create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'sku', 'sell_price', 'unit']),
            'methods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code'])
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'code' => 'required|string|unique:sales,code',
            'status' => 'required|in:' . implode(
                ',',
                [
                    Sale::STATUS_OPEN,
                    Sale::STATUS_PAID,
                    Sale::STATUS_VOID
                ]
            ),
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.sell_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullabel|numeric|min:0',
            'payments' => 'required|array|min:1',
            'payments.*.method_id' => 'required|exists:payment_methods,id',
            'payments.*.amount' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $subtotal = 0;
            $discountTotal = 0;
            $taxTotal = 0;

            foreach ($data['items'] as &$it) {
                $it['discount'] = $it['discount'] ?? 0;
                $it['tax'] = $it['tax'] ?? 0;
                $it['line_total'] = ($it['sell_price'] * $it['qty'] - $it['discount'] + $it['tax']);
                $subtotal += ($it['sell_price'] * $it['qty']);
                $discountTotal += $it['discount'];
                $taxTotal += $it['tax'];
            }

            $grandTotal = $subtotal - $discountTotal + $taxTotal;

            $paidTotal = 0;
            foreach (($data['payments'] ?? []) as $paid) {
                $paidTotal += $paid['amount'];
            }

            $changeDue = max(0, $paidTotal - $grandTotal);

            $sale = Sale::create([
                'customer_id' => $data['customer_id'],
                'code' => $data['code'],
                'status' => $data['status'],
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'paid_total' => $paidTotal,
                'change_due' => $changeDue,
                'user_id' => Auth::id(),
                'sold_at' => $data['status'] === Sale::STATUS_PAID ? now() : null,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($data['items'] as $it) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $it['product_id'],
                    'qty' => $it['qty'],
                    'price' => $it['sell_price'],
                    'discount' => $it['discount'],
                    'tax' => $it['tax'],
                    'line_total' => $it['line_total'],
                ]);
            }

            // Custom validation: prevent minus stok kalau status = PAID
            if ($data['status'] === Sale::STATUS_PAID) {
                foreach ($data['items'] as $index => $it) {
                    $inv = Inventory::where('product_id', $it['product_id'])->first();
                    $available = $inv?->qty ?? 0;

                    if ($available < $it['qty']) {
                        return back()->withErrors([
                            "items.$index.qty" => "Stok produk tidak mencukupi. Stok tersedia: {$available}, diminta: {$it['qty']}."
                        ])->withInput();
                    }
                    $inv->qty -= $it['qty'];
                    $inv->save();


                    StockMovement::create([
                        'product_id' => $it['product_id'],
                        'qty_change' => $it['qty'],
                        'type' => 'IN',
                        'source_type' => Sale::class,
                        'source_id' => $sale->id,
                        'note' => 'Sale paid : ' . $sale->code,
                        'created_at' => now(),
                    ]);
                }
            }

            foreach (($data['payments'] ?? []) as $p) {
                Payment::create([
                    'payment_method_id' => $p['payment_method_id'],
                    'sale_id' => $sale->id,
                    'purchase_id' => null,
                    'amount' => $p['amount'],
                    'paid_at' => now(),
                    'cash_session_id' => null,
                    'note' => $p['note'] ?? null,
                ]);
            }
        });

        return redirect()->route('sales.index')->with('success', 'Sale created successfully');
    }

    public function show(Sale $sale)
    {
        $sale->load([
            'customer:id,name',
            'user:id,name',
            'items.product:id,name,sku,unit',
        ]);
        return Inertia(
            'Sales/Show',
            [
                'sale' => $sale
            ]
        );
    }

    public function void(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            if ($sale->status === Sale::STATUS_PAID) {
                $items = $sale->items()->get();
                foreach ($items as $item) {
                    $inv = Inventory::lockForUpdate()
                        ->firstOrCreate(
                            ['product_id' => $item['product_id']],
                            ['qty' => 0]
                        );

                    $inv->qty += $item['qty'];
                    $inv->save();

                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'qty_change' => $item->qty,
                        'type' => 'RETURN_SALE',
                        'source_type' => 'Sale',
                        'source_id' => $sale->id,
                        'note' => 'VOID penjualan',
                        'created_at' => now(),
                    ]);
                }
            }

            $sale->update(['status' => Sale::STATUS_VOID]);
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Transaksi dibatalkan (VOID).');
    }
}
