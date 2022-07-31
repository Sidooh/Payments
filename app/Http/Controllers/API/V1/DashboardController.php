<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $totalPayments = Cache::remember('total_payments', 60 * 60 * 24, function () {
            return Payment::count();
        });
        $totalPaymentsToday = Cache::remember('total_payments_today', 60 * 60, function () {
            return Payment::whereDate('created_at', Carbon::today())->count();
        });

        $totalRevenue = Cache::remember('total_revenue', 60 * 60 * 24, function () {
            return round(Payment::whereStatus(Status::COMPLETED->name)
                ->whereNotIn('subtype', [PaymentSubtype::B2C->name, PaymentSubtype::VOUCHER->name])
                ->sum("amount"));
        });
        $totalRevenueToday = Cache::remember('total_revenue_today', 60 * 60, function () {
            return round(Payment::whereStatus(Status::COMPLETED->name)
                ->whereNotIn('subtype', [PaymentSubtype::B2C->name, PaymentSubtype::VOUCHER->name])
                ->whereDate('created_at', Carbon::today())
                ->sum("amount"));
        });

        return $this->successResponse([
            "total_payments" => $totalPayments,
            "total_payments_today" => $totalPaymentsToday,

            "total_revenue" => $totalRevenue,
            "total_revenue_today" => $totalRevenueToday,
        ]);
    }
}
