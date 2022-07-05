<?php

namespace App\Http\Controllers\API;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $payments = Payment::select(["id", "amount", "type", "subtype", "status", "created_at"])->latest()->get();
        $paymentsToday = Payment::select(["amount", "created_at"])->whereDate("created_at", Carbon::today());

        return response()->json([
            "total_revenue"       => $payments->sum("amount"),
            "total_revenue_today" => Payment::whereDate("created_at", Carbon::today())->sum("amount"),

            "total_payments"       => $payments->count(),
            "total_payments_today" => $paymentsToday->count(),

            "recent_payments"  => $payments->take(70),
            "pending_payments" => $payments->filter(fn(Payment $payment) => $payment->status === Status::PENDING->name)
                ->values(),
        ]);
    }
}
