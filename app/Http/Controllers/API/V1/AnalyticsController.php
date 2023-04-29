<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentSubtype;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use DrH\Mpesa\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Entities\MpesaStkRequest;
use DrH\TendePay\Models\TendePayRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    public function getPaymentsSLO(): JsonResponse
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

    public function getVendorsSLO(Request $request): JsonResponse
    {
        $SLOs = Cache::remember('vendors_slo', (3600 * 24 * 7), fn () => [
            'tende'     => TendePayRequest::selectRaw('ROUND(COUNT(status)/COUNT(*) * 100) slo')
                                          ->fromRaw("(SELECT CASE WHEN status = '1' THEN 1 END status FROM tende_pay_requests WHERE created_at > ?) tende_pay_requests",
                                              now()->subYear())
                                          ->value('slo'),
            'mpesa_stk' => MpesaStkRequest::selectRaw('ROUND(COUNT(status)/COUNT(*) * 100) slo')
                                          ->fromRaw("(SELECT CASE WHEN status = 'Paid' THEN 1 END status FROM mpesa_stk_requests WHERE created_at > ?) mpesa_stk_requests",
                                              now()->subYear())
                                          ->value('slo'),
            'mpesa_b2c' => MpesaBulkPaymentResponse::selectRaw('ROUND(COUNT(result_code)/COUNT(*) * 100) slo')
                                                   ->fromRaw("(SELECT CASE WHEN result_code = '0' THEN 1 END result_code FROM mpesa_bulk_payment_responses WHERE created_at > ?) mpesa_bulk_payment_responses",
                                                       now()->subYear())
                                                   ->value('slo'),
            'voucher'   => Payment::selectRaw('ROUND(COUNT(status)/COUNT(*) * 100) slo')
                                  ->fromRaw("(SELECT CASE WHEN status = 'COMPLETED' THEN 1 END status FROM payments WHERE subtype = ? OR destination_subtype = ?) payments",
                                      [PaymentSubtype::VOUCHER, PaymentSubtype::VOUCHER])
                                  ->value('slo'),
        ]);

        return $this->successResponse($SLOs);
    }

    public function payments(): JsonResponse
    {
        $data = Cache::remember('transactions', (3600 * 24), function() {
            return Payment::selectRaw("status, DATE_FORMAT(created_at, '%Y%m%d%H') as date, COUNT(*) as count, SUM(amount) as amount")
                          ->groupBy('date', 'status')
                          ->orderByDesc('date')
                          ->get();
        });

        return $this->successResponse($data);
    }
}
