<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashSessionController extends Controller
{

    public function index(Request $request)
    {
        $sessions = CashSession::with('user')
            ->orderByDesc('opened_at')
            ->paginate(10);

        return Inertia::render(
            'CashSessions/Index',
            [
                'sessions' => $sessions
            ]
        );
    }

    public function openFrom()
    {
        return Inertia::render(
            'CashSessions/Open',
            [
                'last_open' => CashSession::where('status', CashSession::STATUS_OPEN)
                    ->latest('opened_at')
                    ->first()
            ]
        );
    }

    public function open(Request $request)
    {
        $data = $request->validate([
            'opening_balance' => 'required|numeric|min:0',
        ]);

        CashSession::where(
            'user_id',
            Auth::id()
        )
            ->where('status', CashSession::STATUS_OPEN)
            ->update(
                [
                    'status' => CashSession::STATUS_CLOSED,
                    'closed_at' => now(),
                    'closing_balance' => DB::raw('closing_balance')
                ]
            );

        CashSession::create([
            'user_id' => Auth::id(),
            'opened_at' => now(),
            'opening_balance' => $data['opening_balance'],
            'status' => CashSession::STATUS_OPEN
        ]);

        return redirect()->route('cash-sessions.index')->with('success', 'Cash session opened successfully.');
    }

    public function closeFrom(CashSession $cashSession)
    {
        abort_unless(
            $cashSession->user_id === Auth::id(),
            403
        );

        return Inertia::render(
            'CashSessions/Close',
            [
                'session' => $cashSession
            ]
        );
    }

    public function close(Request $request, CashSession $cashSession)
    {
        abort_unless(
            $cashSession->user_id === Auth::id(),
            403
        );

        $totalPayments = Payment::where('cash_session_id', $cashSession->id)->sum('amount');


        $data = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
        ]);

        $cashSession->update([
            'closed_at' => now(),
            'closing_balance' => $data['closing_balance'],
            'status' => CashSession::STATUS_CLOSED,
        ]);

        return redirect()->route('cash-sessions.index')->with('success', 'Cash session closed successfully. Total payments:' . $totalPayments);
    }
}
