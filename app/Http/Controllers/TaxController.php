<?php

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaxController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search');
        $taxes = Tax::when($search, fn($query) => $query->where('name', 'like', "%$search%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Taxes/Index', [
            'taxes' => $taxes,
            'filters' => ['search' => $search],
        ]);
    }

    public function create()
    {
        return Inertia::render('Taxes/Create', [
            'taxes' => Tax::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'rate' => 'required|numeric|between:0,100',
        ]);
        Tax::create($data);
        return redirect()->route('taxes.index')->with('success', 'Tax created successfully');
    }

    public function edit(Tax $tax)
    {
        return Inertia::render('Taxes/Edit', [
            'tax' => $tax
        ]);
    }

    public function update(Request $request, Tax $tax)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'rate' => 'required|numeric|between:0,100',
        ]);
        $tax->update($data);
        return redirect()->route('taxes.index')->with('success', 'Tax updated successfully');
    }

    public function destroy(Tax $tax)
    {
        $tax->delete();
        return redirect()->back()->with('success', 'Tax deleted successfully');
    }
}
