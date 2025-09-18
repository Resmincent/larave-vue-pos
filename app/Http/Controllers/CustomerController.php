<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->string('query');
        $customers = Customer::when($query, fn($w) => $w->where('name', 'like', "%$query%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('customers/Index', [
            'customers' => $customers,
            'filters' => ['query' => $query],
        ]);
    }

    public function create()
    {
        return Inertia::render('customers/Create',);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:15|unique:customers,phone',
        ]);

        Customer::create($data);
        return redirect()->route('customers.index')->with('success', 'Customer created successfully');
    }

    public function edit(Customer $customer)
    {
        return Inertia::render('customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:15|unique:customers,phone',
        ]);

        $customer->update($data);
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->back()->with('success', 'Customer deleted successfully');
    }
}
