<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PayableType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentSubtype;
use App\Enums\VoucherType;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\Voucher;
use App\Repositories\PaymentRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;
use Throwable;

class PaymentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $payments = Payment::latest()->get();

        return PaymentResource::collection($payments);
    }

    public function getByTransactionId(Request $request, int $transactionId): JsonResponse
    {
        $payment = Payment::select(["id", "provider_id", "provider_type", "amount", "status", "type", "subtype"])
            ->wherePayableType(PayableType::TRANSACTION->name)->wherePayableId($transactionId)->first();

        if($payment->subtype === PaymentSubtype::STK->name) $payment->load([
            "provider:id,status,reference,checkout_request_id",
            "provider.response:id,checkout_request_id,result_desc,amount,transaction_date"
        ]);

        return response()->json($payment);
    }

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

        $data = match ($request->input('method')) {
            PaymentMethod::MPESA->name => $repo->mpesa(),
            PaymentMethod::VOUCHER->name => $repo->voucher(),
            PaymentMethod::FLOAT->name => $repo->float(),
            default => throw new Exception("Unsupported payment method!")
        };

        return $this->successResponse($data, "Payment Created!");
    }

    #[ArrayShape([
        "payment" => "array",
        "voucher" => "array"
    ])] public function findDetails(int $transactionId, int $accountId): array
    {
        return [
            "payment" => Payment::wherePayableId($transactionId)->first()->toArray(),
            "voucher" => Voucher::firstOrCreate(['account_id' => $accountId], [
                'type' => VoucherType::SIDOOH
            ])->toArray()
        ];
    }

    public function queryMpesaStatus(): JsonResponse
    {
        $exitCode = Artisan::call('mpesa:query_stk_status');

        return response()->json(['Status' => $exitCode]);
    }
}
