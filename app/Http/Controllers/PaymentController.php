<?php

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{

    public function index(Request $request)
    {
        $q = $request->string('q');
        $payments = Payment::with(['method:id,name,code', 'sale:id,code', 'purchase:id,code'])
            ->when($q, fn($w) => $w->whereHas('sale', fn($s) => $s->where('code', 'like', "%$q%"))
                ->orWhereHas('purchase', fn($p) => $p->where('code', 'like', "%$q%")))
            ->orderByDesc('paid_at')->paginate(10)->withQueryString();

        return Inertia::render('Payments/Index', [
            'filters' => ['q' => $q],
            'payments' => $payments
        ]);
    }
}
