<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\Frequency;
use App\Enums\PaymentSubtype;
use App\Enums\Period;
use App\Enums\Status;
use App\Facades\LocalCarbon;
use App\Helpers\ChartAid;
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

    public function revenueChart(Request $request): JsonResponse
    {
        $frequency = Frequency::tryFrom((string)$request->input("frequency")) ?? Frequency::HOURLY;

        $chartAid = new ChartAid(Period::TODAY, $frequency, "sum", "amount");
        $chartAid->setShowFuture(true);

        $fetch = function (array $whereBetween, int $freqCount = null) use ($chartAid) {
            $cacheKey = 'payments_' . implode('_', $whereBetween);
            $transactions = Cache::remember($cacheKey, 60 * 60, function () use ($whereBetween) {
                return Payment::select(["status", "created_at", "amount"])
                    ->whereBetween('created_at', $whereBetween)->get();
            });

            $transform = function ($transactions, $key) use ($freqCount, $chartAid) {
                $models = $transactions->groupBy(fn($item) => $chartAid->chartDateFormat($item->created_at));

                return [$key => $chartAid->chartDataSet($models, $freqCount)];
            };

            return $transactions->groupBy("status")->toBase()->mapWithKeys($transform)
                ->merge($transform($transactions, "ALL"));
        };

        $todayHrs = LocalCarbon::now()->diffInHours(LocalCarbon::now()->startOfDay());

        return $this->successResponse([
            "today" => $fetch([
                LocalCarbon::today()->startOfDay()->utc(),
                LocalCarbon::today()->endOfDay()->utc()
            ], $todayHrs + 1),
            "yesterday" => $fetch([
                LocalCarbon::yesterday()->startOfDay()->utc(),
                LocalCarbon::yesterday()->endOfDay()->utc()
            ]),
        ]);
    }
}
