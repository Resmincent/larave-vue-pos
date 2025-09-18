<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentMethodController extends Controller
{

    public function index()
    {
        return Inertia::render('PaymentMethods/Index', [
            'paymentMethods' => PaymentMethod::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('PaymentMethods/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:payment_methods,code',
            'is_active' => 'required|boolean',
        ]);

        PaymentMethod::create($data);
        return redirect()->route('payment-methods.index')->with('success', 'Payment method created successfully');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return Inertia::render('PaymentMethods/Edit', [
            'paymentMethod' => $paymentMethod,
        ]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:payment_methods,code,' . $paymentMethod->id,
            'is_active' => 'required|boolean',
        ]);

        $paymentMethod->update($data);
        return redirect()->route('payment-methods.index')->with('success', 'Payment method updated successfully');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return redirect()->back()->with('success', 'Payment method deleted successfully');
    }
}
