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
        $inventories = Inventory::with('product: id, name, sku, unit')
            ->when($query, fn($w) =>
            $w->whereHas('product', fn($p) =>
            $p->where('name', 'like', "%$query%")->orWhere('sku', 'like', "%$query%")))
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Invetories/Index', [
            'inventories' => $inventories,
            'filters' => ['query' => $query],
        ]);
    }

    public function adjustForm(Product $product)
    {
        $inventory = Inventory::firstOrCreate(
            ['product_id' => $product->id],
            ['qty' => 0]
        );
        return Inertia::render('Inventories/Adjust', [
            'inventory' => $inventory,
            'product' => $product->only(['id', 'name', 'sku', 'unit']),
        ]);
    }

    public function asjust(Request $request, Product $product)
    {
        $data = $request->validate([
            'qty_change' => ['required', 'integer', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($product, $data) {
            $inv = Inventory::lockForUpdate()->firstOrCreate(
                ['product_id' => $product->id],
                ['qty' => 0],
            );
            $inv->qty += $data['qty_change'];
            $inv->save();

            StockMovement::create([
                'product_id' => $product->id,
                'qty_change' => $data['qty_change'],
                'type' => $data['qty_change'] > 0 ? 'ADJUSMENT_IN' : 'ADJUSMENT_OUT',
                'source_code' => 'Manual',
                'source_id' => null,
                'note' => $data['note'] ?? null,
                'created_at' => now(),
            ]);
        });

        return redirect()->route('inventories.index')->with('success', 'Inventory adjusted successfully');
    }
}
