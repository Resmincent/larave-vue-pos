<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->string('query');

        $customers = Customer::with('user.roles')
            ->whereHas('user.roles', fn($q) => $q->where('name', 'Customer'))
            ->when(
                $query,
                fn($q) =>
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%$query%"))
            )
            ->join('users', 'users.id', '=', 'customers.user_id') // ✅ fix sort
            ->select('customers.*', 'users.name as user_name')
            ->orderBy('users.name')
            ->paginate(10);


        return Inertia::render('customers/Index', [
            'customers' => $customers,
            'filters'   => ['query' => $query],
        ]);
    }


    public function create()
    {
        return Inertia::render('customers/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:15|unique:customers,phone',
        ]);

        // 1. Buat user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // 2. Assign role Customer
        $user->assignRole('Customer');

        // 3. Buat profile customer
        Customer::create([
            'user_id' => $user->id,
            'address' => $data['address'],
            'phone' => $data['phone'],
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully');
    }

    public function edit(Customer $customer)
    {
        $customer->load('user');
        return Inertia::render('customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $customer->user_id,
            'address' => 'required|string',
            'phone' => 'nullable|string|max:15|unique:customers,phone,' . $customer->id,
        ]);

        // Update user
        $customer->user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        // Update customer profile
        $customer->update([
            'address' => $data['address'],
            'phone' => $data['phone'],
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully');
    }

    public function destroy(Customer $customer)
    {
        $customer->user()->delete();
        return redirect()->back()->with('success', 'Customer deleted successfully');
    }
}
