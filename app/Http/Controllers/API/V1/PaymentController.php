<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentMethod;
use App\Enums\PaymentSubtype;
use App\Enums\ProductType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Payment;
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
use Throwable;

class PaymentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @throws Throwable
     * @return JsonResponse
     *
     */
    public function __invoke(Request $request): JsonResponse
    {
        $countryCode = config('services.sidooh.country_code');

        $request->validate([
            'payment_mode'               => ['required', new Enum(PaymentMethod::class)],
            'debit_account'              => [
                'required',
                Rule::when($request->input("payment_mode") === PaymentMethod::MPESA->name, "phone:$countryCode", [new SidoohAccountExists]),
            ],
            'transactions.*.product_id'  => ['required', new Enum(ProductType::class)],
            'transactions.*.reference'   => ['required', 'integer'],
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
        } catch (Exception $err) {
            if($err->getCode() === 422) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse("Failed to process payment request.");
    }

    public function index(Request $request): JsonResponse
    {
        $payments = Payment::latest()->limit(100);

        if($request->has('status') && $status = Status::tryFrom($request->status)) {
            $payments->whereStatus($status);
            if($status !== Status::PENDING) {
                $payments->limit(100); // Other statuses will have too many records
            }
        } else {
            $payments->limit(100);
        }

        $payments = $payments->latest()->get();

        return $this->successResponse($payments);
    }

    public function show(Payment $payment): JsonResponse
    {
        if($payment->subtype === PaymentSubtype::STK->name) {
            $payment->load([
                "provider:id,status,reference,description,checkout_request_id,amount,phone,created_at",
                "provider.response:id,checkout_request_id,mpesa_receipt_number,phone,result_desc,created_at",
            ]);
        }

        if($payment->subtype === PaymentSubtype::VOUCHER->name) {
            $payment->load([
                "provider:id,type,amount,description,created_at",
            ]);
        }

        return $this->successResponse($payment);
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
