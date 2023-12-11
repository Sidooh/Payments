<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Carbon\Carbon;
use DrH\Mpesa\Entities\MpesaB2bCallback;
use DrH\Mpesa\Entities\MpesaB2cResultParameter;
use DrH\Mpesa\Entities\MpesaC2bCallback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $totalPayments = Cache::remember('total_payments', 60 * 60 * 24, fn () => Payment::count());
        $totalPaymentsToday = Cache::remember(
            'total_payments_today', 60 * 60, fn () => Payment::whereDate('created_at', Carbon::today())->count()
        );

        $totalRevenue = Cache::remember('total_revenue', 60 * 60 * 24, function() {
            return round(
                Payment::whereStatus(Status::COMPLETED->name)->whereNotIn(
                    'subtype', [
                        PaymentSubtype::B2C->name,
                        PaymentSubtype::VOUCHER->name,
                    ]
                )->sum('charge')
            );
        });
        $totalRevenueToday = Cache::remember('total_revenue_today', 60 * 60, function() {
            return round(
                Payment::whereStatus(Status::COMPLETED->name)->whereNotIn(
                    'subtype', [
                        PaymentSubtype::B2C->name,
                        PaymentSubtype::VOUCHER->name,
                    ]
                )->whereDate('created_at', Carbon::today())->sum('charge')
            );
        });

        return $this->successResponse([
            'total_payments'       => $totalPayments,
            'total_payments_today' => $totalPaymentsToday,

            'total_revenue'        => $totalRevenue,
            'total_revenue_today'  => $totalRevenueToday,
        ]);
    }

    public function chart(Request $request): JsonResponse
    {
        $chart = Payment::selectRaw("status, DATE_FORMAT(created_at, '%Y%m%d%H') as date, SUM(amount) as amount, COUNT(*) as count")
                                   ->whereDate('created_at', Carbon::today())
                                   ->groupBy('date', 'status')
                                   ->orderByDesc('date')
                                   ->get();

        return $this->successResponse($chart);
    }

    public function getProviderBalances(): JsonResponse
    {
        $orgBalance = Cache::remember('org_balance', 60 * 60 * 12, fn () => MpesaC2bCallback::latest('id')->value('org_account_balance'));
        $b2cBalance = Cache::remember('b2c_balance', 60 * 60 * 12, fn () => MpesaB2cResultParameter::latest('id')->value('b2c_utility_account_available_funds'));
        $b2bBalance = Cache::remember('b2b_balance',
            60 * 10,
            fn () => explode('|', MpesaB2bCallback::latest('id')->value('debit_account_balance'))[2]);

        return $this->successResponse([
            'org_balance' => $orgBalance,
            'b2b_balance' => $b2bBalance,
            'b2c_balance' => $b2cBalance,
        ]);
    }
}
