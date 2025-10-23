<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supply;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->string('query');
        $purchases = Purchase::with(['supplier', 'user:id,name'])
            ->when($query, fn($w) => $w->where('code', 'like', "%$query%"))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia('purchases/Index', [
            'filters' => ['query' => $query],
            'purchases' => $purchases,
        ]);
    }

    public function create()
    {
        return Inertia('purchases/Create', [
            'suppliers' => Supply::select('id', 'user_id')
                ->with(['user:id,name,email'])
                ->orderBy(
                    User::select('name')->whereColumn('users.id', 'suppliers.user_id')
                )
                ->get(),
            'products' => Product::orderBy('name')->get(['id', 'name', 'sku', 'cost_price', 'sell_price', 'unit']),
            'statuses' => [
                ['label' => 'Draft', 'value' => Purchase::STATUS_DRAFT],
                ['label' => 'Received', 'value' => Purchase::STATUS_RECEIVED],
                ['label' => 'Cancelled', 'value' => Purchase::STATUS_CANCELLED],
            ],
            'taxes' => Tax::orderBy('name')->get(['id', 'name', 'rate']),
            'code' => Purchase::generateCode(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'code' => 'required|string|unique:purchases,code',
            'status' => 'required|in:' . implode(
                ',',
                [
                    Purchase::STATUS_DRAFT,
                    Purchase::STATUS_RECEIVED,
                    Purchase::STATUS_CANCELLED
                ]
            ),
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax_id'      => 'nullable|exists:taxes,id',
        ]);

        DB::transaction(function () use ($data) {

            $taxRates = collect($data['items'])
                ->pluck('tax_id')
                ->filter()
                ->unique()
                ->values();

            $taxMap = $taxRates->isNotEmpty()
                ? Tax::whereIn('id', $taxRates)->pluck('rate', 'id') // [tax_id => rate]
                : collect();

            $subtotal = 0;
            $discountTotal = 0;
            $taxTotal = 0;

            foreach ($data['items'] as &$it) {
                $qty        = (int) ($it['qty'] ?? 0);
                $price      = (float) ($it['cost_price'] ?? 0);
                $discount   = (float) ($it['discount'] ?? 0);
                $taxId      = $it['tax_id'] ?? null;

                $lineBase   = ($price * $qty) - $discount;
                $taxRate    = $taxId ? (float) ($taxMap[$taxId] ?? 0) : 0;
                $taxAmount  = round($lineBase * ($taxRate / 100));

                $lineTotal  = $lineBase + $taxAmount;

                $subtotal      += ($price * $qty);
                $discountTotal += $discount;
                $taxTotal      += $taxAmount;
            }

            $paymentItemsPayload[] = [
                'product_id' => $it['product_id'],
                'qty'        => $qty,
                'price'      => $price,
                'discount'   => $discount,
                'tax_id'     => $taxId,
                'line_total' => $lineTotal,
            ];

            $grandTotal = $subtotal - $discountTotal + $taxTotal;

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'] ?? null,
                'user_id' => Auth::id(),
                'code' => $data['code'] ?: Purchase::generateCode(),
                'status' => $data['status'],
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'received_at' => $data['status'] === Purchase::STATUS_RECEIVED ? now() : null,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($paymentItemsPayload as $row) {
                PurchaseItem::create($row + ['purchase_id' => $purchase->id]);
            }

            if ($purchase->status === Purchase::STATUS_RECEIVED) {
                $inv = Inventory::lockForUpdate()->firstOrCreate(['product_id' => $it['product_id']], ['qty' => 0]);
                $inv->qty += $it['qty'];
                $inv->save();

                StockMovement::create([
                    'product_id' => $it['product_id'],
                    'qty_change' => $it['qty'],
                    'type' => 'IN',
                    'source_type' => Purchase::class,
                    'source_id' => $purchase->id,
                    'note' => 'Purchase received : ' . $purchase->code,
                    'created_at' => now(),
                ]);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase created successfully');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load([
            'supplier:id,name',
            'user:id,name',
            'items.product:id,name,sku,unit',
        ]);
        return Inertia(
            'purchases/Show',
            [
                'purchase' => $purchase,
            ]
        );
    }

    public function updateStatus(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([Purchase::STATUS_RECEIVED, Purchase::STATUS_CANCELLED]),
            ],
        ]);

        // Cek apakah status purchase received
        if ($purchase->status !== Purchase::STATUS_RECEIVED) {
            return back()->with('error', 'Hanya pembelian berstatus Draft yang bisa diubah.');
        }

        // Cek apakah itemsnya kosong atau 0
        $itemsCount = $purchase->purchaseItems()->count();
        if ($itemsCount === 0) {
            return back()->with('error', 'Tidak bisa mengubah status karena tidak ada item pada pembelian ini.');
        }

        DB::transaction(function () use ($purchase, $validated) {
            $target = $validated['status'];

            if ($target === Purchase::STATUS_RECEIVED) {
                // Ambil item yang diperlukan untuk update stok
                $items = $purchase->items()->get(['product_id', 'qty']);

                foreach ($items as $it) {
                    // Kunci baris inventory per product untuk konsistensi
                    $inv = Inventory::where('product_id', $it->product_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$inv) {
                        $inv = Inventory::create([
                            'product_id' => $it->product_id,
                            'qty'        => 0,
                        ]);
                    }

                    $inv->qty += (int) $it->qty;
                    $inv->save();

                    // Catat pergerakan stok (IN)
                    StockMovement::create([
                        'product_id'  => $it->product_id,
                        'qty_change'  => (int) $it->qty,
                        'type'        => 'IN',
                        'source_type' => Purchase::class,
                        'source_id'   => $purchase->id,
                        'note'        => 'Purchase received: ' . $purchase->code,
                    ]);
                }

                // Update status & timestamp penerimaan
                $purchase->update([
                    'status'      => Purchase::STATUS_RECEIVED,
                    'received_at' => now(),
                ]);
            } elseif ($target === Purchase::STATUS_CANCELLED) {
                $purchase->update([
                    'status'      => Purchase::STATUS_CANCELLED,
                    'received_at' => null,
                ]);
            }
        });


        return redirect()
            ->route('purchases.index')
            ->with('success', 'Status purchase berhasil diubah ke ' . strtoupper($validated['status']));
    }
}
