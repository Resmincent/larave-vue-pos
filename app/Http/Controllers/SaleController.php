<?php

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
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

            $purchase = Sale::create([]);
        });
    }
}
