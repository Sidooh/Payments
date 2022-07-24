<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentMethod;
use App\Enums\PaymentSubtype;
use App\Enums\ProductType;
use App\Enums\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Voucher;
use App\Repositories\PaymentRepository;
use App\Repositories\WithdrawalRepository;
use App\Rules\SidoohAccountExists;
use DrH\Mpesa\Exceptions\MpesaException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use JetBrains\PhpStorm\ArrayShape;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function __invoke(Request $request): JsonResponse
    {
        $countryCode = config('services.sidooh.country_code');

        $request->validate([
            "transactions"  => ['required', 'array'], //TODO: Define what should be passed in transactions data: product_id, amount, reference, destination
            'payment_mode'  => ['required', new Enum(PaymentMethod::class)],
            'debit_account' => [
                'required',
                Rule::when(
                    $request->input("payment_mode") === PaymentMethod::MPESA->name,
                    "phone:$countryCode",
                    [new SidoohAccountExists]
                )
            ],
            'transactions.*.product_id'  => ['required', new Enum(ProductType::class)],
            'transactions.*.amount'      => ['required', 'integer'],
            'transactions.*.destination' => ['required', 'numeric'],
            'transactions.*.description' => ['required', 'string'],

        ]);

        Log::info('...[CTRL - PAYMENT]: Invoke...', $request->all());

        $transactions = collect($request->transactions);

        $repo = new PaymentRepository($transactions, PaymentMethod::from($request->payment_mode), $request->debit_account);

        try {
            $data = $repo->process();
            return $this->successResponse($data, "Payment Created.");

        } catch (MpesaException $e) {
            Log::critical($e);
            return $this->errorResponse("Failed to process payment request.");

        } catch (Exception $err) {
            if ($err->getCode() === 422) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }
            Log::error($err);
            return $this->errorResponse("Failed to process payment request.");
        }

    }

    public function index(): JsonResponse
    {
        $payments = Payment::latest()->get();

        return response()->json($payments);
    }

    public function show(Payment $payment): JsonResponse
    {
        if ($payment->subtype === PaymentSubtype::STK->name) $payment->load([
            "providable:id,status,reference,checkout_request_id,amount,phone,created_at",
            "providable.response:id,checkout_request_id,result_desc,created_at"
        ]);

        if ($payment->subtype === PaymentSubtype::VOUCHER->name) $payment->load([
            "providable:id,type,amount,description,created_at",
        ]);

        if ($payment->subtype === PaymentSubtype::B2C->name) $payment->load([
            "providable:id,amount,phone,remarks,conversation_id",
            'providable.response'
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

    public function withdraw(Request $request): JsonResponse
    {
        $repo = new WithdrawalRepository($request->all());

        $response = $repo->mpesa();

        return $this->successResponse($response);
    }
}
