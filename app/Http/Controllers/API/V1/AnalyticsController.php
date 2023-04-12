<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use DrH\TendePay\Models\TendePayRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    public function getPaymentsSLOs(): JsonResponse
    {
        $slo = Cache::remember('payments_slo', (3600 * 24 * 7), function() {
            return Payment::selectRaw('YEAR(created_at) as year, status, count(*) as count')
                          ->groupByRaw('year, status')
                          ->get()
                          ->filter(fn ($x) => in_array($x->status,
                              [Status::COMPLETED, Status::FAILED]));
        });

        return $this->successResponse($slo);
    }

    public function getVendorsSLOs(): JsonResponse
    {
        $SLOs = Cache::remember('vendors_slo', (3600 * 24 * 7), fn () => [
            'tende'   => TendePayRequest::selectRaw('ROUND(COUNT(status)/COUNT(*) * 100) slo')
                                        ->fromRaw("(SELECT CASE WHEN status = '1' THEN 1 END status FROM tende_pay_requests WHERE created_at > ?) tende_pay_requests",
                                            now()->subYear())
                                        ->value('slo'),
            'voucher' => Payment::selectRaw('ROUND(COUNT(status)/COUNT(*) * 100) slo')
                                ->fromRaw("(SELECT CASE WHEN status = 'COMPLETED' THEN 1 END status FROM payments WHERE subtype = ? OR destination_subtype = ?) payments",
                                    [PaymentSubtype::VOUCHER, PaymentSubtype::VOUCHER])
                                ->value('slo'),
        ]);

        return $this->successResponse($SLOs);
    }

    public function payments(): JsonResponse
    {
        $data = Cache::remember('transactions', (3600 * 24), function() {
            return Payment::selectRaw("status, DATE_FORMAT(created_at, '%Y%m%d%H') as date, COUNT(*) as count")
                              ->groupBy('date', 'status')
                              ->orderByDesc('date')
                              ->get();
        });

        return $this->successResponse($data);
    }

    public function revenue(): JsonResponse
    {
        $data = Cache::remember('revenue', (3600 * 24), function() {
            return Payment::selectRaw("status, DATE_FORMAT(created_at, '%Y%m%d%H') as date, SUM(amount) as amount")
                              ->groupBy('date', 'status')
                              ->orderByDesc('date')
                              ->get();
        });

        return $this->successResponse($data);
    }
}
