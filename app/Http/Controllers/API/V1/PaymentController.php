<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentMethod;
use App\Enums\PaymentSubtype;
use App\Enums\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Voucher;
use App\Repositories\PaymentRepository;
use App\Repositories\WithdrawalRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @throws Throwable
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            "transactions" => ['required', 'array'],
            "data"         => ["required", "array"],
            "total_amount" => "required|numeric"
        ]);

        $data = $request->all();

        $repo = new PaymentRepository($data['transactions'], $data['total_amount'], $data['data']);

        Log::info('...[CONTROLLER - PAYMENT]: Invoke...', $data);

        try {
            $data = match ($request->input('method')) {
                PaymentMethod::MPESA->name => $repo->mpesa(),
                PaymentMethod::VOUCHER->name => $repo->voucher(),
                PaymentMethod::FLOAT->name => $repo->float(),
                default => throw new Exception("Unsupported payment method!")
            };

            return $this->successResponse($data, "Payment Created!");
        } catch (Exception $err) {
            return $this->errorResponse($err->getMessage(), $err->getCode());
        }
    }

    public function index(): JsonResponse
    {
        $payments = Payment::latest()->get();

        return response()->json($payments);
    }

    public function show(Payment $payment): JsonResponse
    {
        if($payment->subtype === PaymentSubtype::STK->name) $payment->load([
            "providable:id,status,reference,checkout_request_id,amount,phone,created_at",
            "providable.response:id,checkout_request_id,result_desc,created_at"
        ]);

        if($payment->subtype === PaymentSubtype::VOUCHER->name) $payment->load([
            "providable:id,type,amount,description,created_at",
        ]);

        return response()->json($payment);
    }

    public function getByTransactionId(int $transactionId): JsonResponse
    {
        $payment = Payment::select(["id", "provider_id", "provider_type", "amount", "status", "type", "subtype"])
            ->whereProvidableId($transactionId)->first();

        if($payment->subtype === PaymentSubtype::STK->name) $payment->load([
            "provider:id,status,reference,checkout_request_id,amount,phone,created_at",
            "provider.response:id,checkout_request_id,result_desc,created_at"
        ]);

        return response()->json($payment);
    }

    #[ArrayShape([
        "payment" => "array",
        "voucher" => "array"
    ])] public function findDetails(int $paymentId, int $accountId): array
    {
        return [
            "payment" => Payment::find($paymentId),
            "voucher" => Voucher::firstOrCreate(['account_id' => $accountId], [
                'type' => VoucherType::SIDOOH
            ])->toArray()
        ];
    }

    public function queryMpesaStatus(): JsonResponse
    {
        $exitCode = Artisan::call('mpesa:query_stk_status');

        return $this->successResponse(['Status' => $exitCode]);
    }

    public function disburse(Request $request): JsonResponse
    {
        $repo = new WithdrawalRepository($request->all());

        $response = $repo->mpesa();

        return $this->successResponse($response);
    }
}
