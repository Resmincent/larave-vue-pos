<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $request->string('query');
        $products = Product::with(['category', 'tax'])
            ->when($query, fn($w) => $w->where('name', 'like', "%$query%"))
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'products/Index',
            [
                'products' => $products,
                'filters' => ['query' => $query],
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('products/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'taxes' => Tax::orderBy('name')->get(['id', 'name', 'rate']),
            'units' => ['pcs', 'kg', 'litre', 'pack', 'box'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => 'nullable|string|max:20',
            'name' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'sell_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'unit' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);

        Product::create($data);
        return redirect()->route('products.index')->with('success', 'Product created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return Inertia::render('products/Edit', [
            'product' => $product->load(['category', 'tax']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'taxes' => Tax::orderBy('name')->get(['id', 'name', 'rate']),
            'units' => ['pcs', 'kg', 'litre', 'pack', 'box'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'sku' => 'nullable|string|max:20',
            'name' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'tax_id' => 'nullable|exists:taxes,id',
            'sell_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'unit' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);

        $product->update($data);
        return redirect()->route('products.index')->with('success', 'Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back()->with('success', 'Product deleted successfully');
    }
}
