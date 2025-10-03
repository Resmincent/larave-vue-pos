<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;


class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->string('query');

        $products = Product::with(['inventory:id,product_id,qty'])
            ->when(
                $query,
                fn($q) =>
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%")
            )
            ->orderBy('id',)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('inventories/Index', [
            'inventories' => $products,
            'filters' => ['query' => $query],
        ]);
    }

    public function adjustForm(Product $product)
    {
        $inventory = Inventory::firstOrCreate(
            ['product_id' => $product->id],
            ['qty' => 0]
        );

        return Inertia::render('inventories/Index', [
            'inventory' => $inventory,
            'product' => $product->only(['id', 'name', 'sku', 'unit']),
        ]);
    }

    public function adjust(Request $request, Product $product)
    {
        $data = $request->validate([
            'qty_change' => ['required', 'integer', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($product, $data) {
            // Lock record supaya tidak race condition
            $inv = Inventory::where('product_id', $product->id)->lockForUpdate()->first();

            if (!$inv) {
                $inv = Inventory::create([
                    'product_id' => $product->id,
                    'qty' => 0,
                ]);
            }

            // Validasi stok tidak boleh minus
            $newQty = $inv->qty + $data['qty_change'];
            if ($newQty < 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'qty_change' => 'Stock tidak boleh kurang dari 0',
                ]);
            }

            // Update stok
            $inv->qty = $newQty;
            $inv->save();

            // Catat pergerakan stok
            StockMovement::create([
                'product_id'  => $product->id,
                'qty_change'  => $data['qty_change'],
                'type'        => 'ADJUSTMENT',
                'source_code' => 'Manual',
                'source_id'   => null,
                'note'        => $data['note'] ?? null,
            ]);
        });

        return redirect()->route('inventories.index')
            ->with('success', 'Inventory adjusted successfully');
    }
}
