<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->string('query');
        $suppliers = Supply::with('user')->when($query, fn($w) => $w->where('name', 'like', "%$query%"))
            ->orderBy(Supply::select('name')->join('users', 'users.id', '=', 'suppliers.user_id'))
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('supplier/Index', [
            'suppliers' => $suppliers,
            'filters' => ['query' => $query],
        ]);
    }

    public function create()
    {
        return Inertia::render('supplier/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // 1. Buat user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // 2. Assign role Supplier
        $user->assignRole('Supplier');

        // 3. Buat profile supplier
        Supply::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully');
    }

    public function edit(Supply $supplier)
    {
        return Inertia::render('supplier/Edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(Request $request, Supply $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Update user
        $supplier->user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        // Update supplier profile
        $supplier->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully');
    }

    public function destroy(Supply $supplier)
    {
        $supplier->user()->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully');
    }
}
