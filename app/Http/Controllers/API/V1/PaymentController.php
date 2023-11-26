<?php

namespace App\Http\Controllers\API\V1;

use App\DTOs\PaymentDTO;
use App\Enums\Description;
use App\Enums\MerchantType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantPaymentRequest;
use App\Http\Requests\MpesaWithdrawalRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Requests\WithdrawalRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Repositories\PaymentRepositories\PaymentRepository;
use App\Repositories\SidoohRepositories\VoucherRepository;
use App\Services\SidoohAccounts;
use App\Services\SidoohService;
use Error;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class PaymentController extends Controller
{
    public function __invoke(PaymentRequest $request): JsonResponse
    {
        Log::info('...[CTRL - PAYMENT]: Invoke...', $request->all());

        try {
            [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();
            [$destinationType, $destinationSubtype] = PaymentMethod::from($request->destination)->getTypeAndSubtype();

            $destinationData = match ($destinationSubtype) {
                PaymentSubtype::FLOAT   => 'float_account_id',
                PaymentSubtype::VOUCHER => 'voucher_id',
                default                 => throw new HttpException(
                    422, 'Only float account and voucher are supported for destination.'
                )
            };

            $repo = new PaymentRepository(
                new PaymentDTO(
                    $request->account_id,
                    $request->amount,
                    $type,
                    $subtype,
                    $request->description,
                    $request->reference,
                    $request->source_account,
                    false,
                    $destinationType,
                    $destinationSubtype,
                    [$destinationData => $request->destination_account]
                ), $request->ipn
            );

            $payment = $repo->processPayment();

            return $this->successResponse(PaymentResource::make($payment), 'Payment Requested.');
        } catch (HttpException $err) {
            Log::error($err);

            return $this->errorResponse($err->getMessage(), $err->getStatusCode());
        } catch (Exception|Throwable|Error $err) {
            if ($err->getCode() === 422 || $err->getCode() === 400) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process payment request.');
    }

    /**
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function index(Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        $payments = Payment::latest();

        if ($request->has('status') && $status = Status::tryFrom($request->status)) {
            $payments->whereStatus($status);
        }

        $payments = $payments->limit(150)->get();

        if (in_array('account', $relations)) {
            $payments = withRelation('account', $payments, 'account_id', 'id');
        }

        return $this->successResponse($payments);
    }

    /**
     * @throws \Exception
     */
    public function show(Payment $payment): JsonResponse
    {
        // TODO: Add auth check functionality for this
        if ($payment->subtype === PaymentSubtype::STK) {
            $payment->type === PaymentType::BUNI ?
                $payment->load([
                    'provider:id,status,invoice_number,description,merchant_request_id,amount,phone_number,created_at',
                    'provider.callback:id,merchant_request_id,mpesa_receipt_number,phone_number,result_desc,created_at',
                ])
                :
                $payment->load([
                    'provider:id,status,reference,description,checkout_request_id,amount,phone,created_at',
                    'provider.response:id,checkout_request_id,mpesa_receipt_number,phone,result_desc,created_at',
                ]);
        }

        if (in_array($payment->subtype, [PaymentSubtype::VOUCHER, PaymentSubtype::FLOAT])) {
            $payment->load('provider:id,type,amount,description,created_at');
        }

        if ($payment->subtype === PaymentSubtype::C2B) {
            $payment->load('provider');
        }

        if ($payment->destination_subtype === PaymentSubtype::FLOAT) {
            $payment->load('destinationProvider:id,type,amount,description,created_at');
        }

        if ($payment->destination_subtype === PaymentSubtype::VOUCHER) {
            $payment->load('destinationProvider:id,type,amount,description,created_at');
        }

        if ($payment->destination_subtype === PaymentSubtype::B2C) {
            $payment->load('destinationProvider.response');
        }

        if ($payment->destination_subtype === PaymentSubtype::B2B) {
            $payment->load('destinationProvider.response');
        }

        if ($payment->account_id) {
            $payment->account = SidoohAccounts::find($payment->account_id);
        }

        return $this->successResponse($payment);
    }

    public function reverse(Payment $payment): JsonResponse
    {
        Log::info('...[CTRL - PAYMENT]: Reverse...');

        if ($payment->destination_subtype === PaymentSubtype::FLOAT) {
            $sourceAccount = $payment->destination_data['float_account_id'];
            $destinationIdField = 'voucher_id';
            $destinationAccount = VoucherRepository::getDefaultVoucherForAccount($payment->account_id)['id'];
        } elseif ($payment->destination_subtype === PaymentSubtype::VOUCHER) {
            $sourceAccount = $payment->destination_data['voucher_id'];
            $destinationIdField = 'float_account_id';
            $destinationAccount = $payment->provider->float_account_id;
        } else {
            throw new HttpException(422, 'Irreversible payment.');
        }

        if ($payment->type !== PaymentType::SIDOOH) {
            [$payment->type, $payment->subtype] = PaymentMethod::VOUCHER->getTypeAndSubtype();
        }

        try {
            $repo = new PaymentRepository(
                new PaymentDTO(
                    $payment->account_id,
                    $payment->amount,
                    $payment->destination_type,
                    $payment->destination_subtype,
                    Description::PAYMENT_REVERSAL->value,
                    $payment->reference,
                    $sourceAccount,
                    false,
                    $payment->type,
                    $payment->subtype,
                    [$destinationIdField => $destinationAccount, 'payment_id' => $payment->id]
                )
            );

            $payment = $repo->processPayment();

            return $this->successResponse(PaymentResource::make($payment), 'Payment Reversal Requested.');
        } catch (HttpException $err) {
            Log::error($err);

            return $this->errorResponse($err->getMessage(), $err->getStatusCode());
        } catch (Exception|Throwable|Error $err) {
            if ($err->getCode() === 422) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process payment request.');
    }

    public function retryPurchase(Payment $payment): JsonResponse
    {
        if ($payment->status !== Status::COMPLETED) {
            return $this->errorResponse('There is a problem with this transaction - Status. Contact Support.');
        }

        SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));

        return $this->successResponse($payment->refresh());
    }

    public function merchant(MerchantPaymentRequest $request): JsonResponse
    {
        Log::info('...[CTRL - PAYMENT]: Merchant...', [$request->all()]);

        try {
            [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();
            $merchantType = MerchantType::from($request->merchant_type);
            [$type2, $subtype2] = $merchantType->getTypeAndSubtype();

            if ($merchantType === MerchantType::MPESA_PAY_BILL) {
                $charge = pay_bill_charge($request->integer('amount'));
                $destination = $request->only('merchant_type', 'paybill_number', 'account_number');

                if (is_blacklisted_merchant($request->paybill_number)) {
                    throw new Exception('invalid merchant', 422);
                }
            } else {
                $charge = buy_goods_charge($request->integer('amount'));
                $destination = $request->only('merchant_type', 'buy_goods_number', 'account_number');

                if (is_blacklisted_merchant($request->buy_goods_number)) {
                    throw new Exception('invalid merchant', 422);
                }
            }

            $repo = new PaymentRepository(
                new PaymentDTO(
                    $request->account_id,
                    $request->amount,
                    $type,
                    $subtype,
                    $request->description,
                    $request->reference,
                    $request->source_account,
                    false,
                    $type2,
                    $subtype2,
                    $destination,
                    $charge
                ), $request->ipn
            );

            $payment = $repo->processPayment();

            return $this->successResponse(PaymentResource::make($payment), 'Payment Requested.');
        } catch (Exception $err) {
            if ($err->getCode() === 422) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process payment request.');
    }

    public function mpesaFloat(MerchantPaymentRequest $request): JsonResponse
    {
        Log::info('...[CTRL - PAYMENT]: Mpesa Float...', [$request->all()]);

        try {
            [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();
            $merchantType = MerchantType::from($request->merchant_type);
            [$type2, $subtype2] = $merchantType->getTypeAndSubtype();

            if ($merchantType === MerchantType::MPESA_STORE) {
                $charge = mpesa_float_charge($request->integer('amount'));
                $destination = $request->only('merchant_type', 'store', 'agent');

                if (is_blacklisted_merchant($request->store)) {
                    throw new Exception('invalid store', 422);
                }
            }

            $repo = new PaymentRepository(
                new PaymentDTO(
                    $request->account_id,
                    $request->amount,
                    $type,
                    $subtype,
                    $request->description,
                    $request->reference,
                    $request->source_account,
                    false,
                    $type2,
                    $subtype2,
                    $destination,
                    $charge
                ), $request->ipn
            );

            $payment = $repo->processPayment();

            return $this->successResponse(PaymentResource::make($payment), 'Payment Requested.');
        } catch (Exception $err) {
            if ($err->getCode() === 422) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process payment request.');
    }

    public function mpesaWithdraw(MpesaWithdrawalRequest $request): JsonResponse
    {
        Log::info('...[CTRL - PAYMENT]: Mpesa Withdraw...', $request->all());

        try {
            [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();
            [$destinationType, $destinationSubtype] = PaymentMethod::from($request->destination)->getTypeAndSubtype();

            $destinationData = match ($destinationSubtype) {
                PaymentSubtype::FLOAT   => 'float_account_id',
                default                 => throw new HttpException(
                    422, 'Only float account is supported for destination.'
                )
            };

            $repo = new PaymentRepository(
                new PaymentDTO(
                    $request->account_id,
                    $request->amount,
                    $type,
                    $subtype,
                    $request->description,
                    "Withdrawal",
                    $request->source_account,
                    false,
                    $destinationType,
                    $destinationSubtype,
                    [$destinationData => $request->destination_account],
                    mpesa_collection_charge($request->integer('amount')),
                ), $request->ipn
            );

            $payment = $repo->processPayment();

            return $this->successResponse(PaymentResource::make($payment), 'Payment Requested.');
        } catch (HttpException $err) {
            Log::error($err);

            return $this->errorResponse($err->getMessage(), $err->getStatusCode());
        } catch (Exception|Throwable|Error $err) {
            if ($err->getCode() === 422 || $err->getCode() === 400) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process payment request.');
    }


    public function merchantFloatTopUp(MpesaWithdrawalRequest $request): JsonResponse
    {
        Log::info('...[CTRL - PAYMENT]: Merchant Float...', $request->all());

        try {
            [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();
            [$destinationType, $destinationSubtype] = PaymentMethod::from($request->destination)->getTypeAndSubtype();

            $destinationData = match ($destinationSubtype) {
                PaymentSubtype::FLOAT   => 'float_account_id',
                default                 => throw new HttpException(
                    422, 'Only float account is supported for destination.'
                )
            };

            $repo = new PaymentRepository(
                new PaymentDTO(
                    $request->account_id,
                    $request->amount,
                    $type,
                    $subtype,
                    $request->description,
                    "Withdrawal",
                    $request->source_account,
                    false,
                    $destinationType,
                    $destinationSubtype,
                    [$destinationData => $request->destination_account],
                    mpesa_collection_charge($request->integer('amount')),
                ), $request->ipn
            );

            $payment = $repo->processPayment();

            return $this->successResponse(PaymentResource::make($payment), 'Payment Requested.');
        } catch (HttpException $err) {
            Log::error($err);

            return $this->errorResponse($err->getMessage(), $err->getStatusCode());
        } catch (Exception|Throwable|Error $err) {
            if ($err->getCode() === 422 || $err->getCode() === 400) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process payment request.');
    }

    public function withdraw(WithdrawalRequest $request): JsonResponse
    {
        Log::info('...[CTRL - PAYMENT]: Withdraw...', $request->all());

        try {
            [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();
            [$type2, $subtype2] = PaymentMethod::from($request->destination)->getWithdrawalTypeAndSubtype();
            $subtype2 = $type2 === PaymentType::MPESA ? PaymentSubtype::B2C : $subtype2;

            $destination = match ($subtype2) {
                PaymentSubtype::VOUCHER => 'voucher_id',
                PaymentSubtype::B2C     => 'phone',
                PaymentSubtype::FLOAT => 'float_account_id',
                default                 => throw new Exception('Unexpected payment subtype'),
            };

            $repo = new PaymentRepository(
                new PaymentDTO(
                    $request->account_id,
                    $request->amount,
                    $type,
                    $subtype,
                    $request->description,
                    $request->reference,
                    $request->source_account,
                    false,
                    $type2,
                    $subtype2,
                    [$destination => $request->destination_account],
                    $type2 === PaymentType::SIDOOH ? 0 : withdrawal_charge($request->amount)
                ), $request->ipn
            );

            $payment = $repo->processPayment();

            return $this->successResponse(PaymentResource::make($payment), 'Withdrawal Requested.');
        } catch (Exception $err) {
            if ($err->getCode() === 422) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process payment request.');
    }

    public function complete(Payment $payment): JsonResponse
    {
        // Check payment
        if ($payment->status !== Status::PENDING) {
            return $this->errorResponse('There is a problem with this transaction - Payment. Contact Support.');
        }

        $payment->update(['status' => Status::COMPLETED]);

        if ($payment->ipn) {
            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));
        }

        return $this->successResponse($payment->refresh());
    }

    public function fail(Payment $payment): JsonResponse
    {
        // Check payment
        if ($payment->status !== Status::PENDING) {
            return $this->errorResponse('There is a problem with this transaction - Status. Contact Support.');
        }

        $payment->update(['status' => Status::FAILED]);

        if ($payment->ipn) {
            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));
        }

        return $this->successResponse($payment->refresh());
    }

    public function typeAndSubtype(string $type, string $subType): JsonResponse
    {
        return match (PaymentType::tryFrom(strtoupper($type))) {
            PaymentType::SIDOOH => match (PaymentSubtype::tryFrom(strtoupper($subType))) {
                PaymentSubtype::B2B => $this->getB2BPayments(),
                default             => throw new HttpException(422, "Unexpected sub-type $subType for type $type")
            },
            PaymentType::MPESA  => match (PaymentSubtype::tryFrom(strtoupper($subType))) {
                PaymentSubtype::B2C => $this->getB2CPayments(),
                PaymentSubtype::STK => $this->getSTKPayments(),
                PaymentSubtype::C2B => $this->getC2BPayments(),
                default             => throw new HttpException(422, "Unexpected sub-type $subType for type $type"),
            },
            default             => throw new HttpException(422, "Unexpected payment type $type")
        };
    }

    public function getSTKPayments(): JsonResponse
    {
        $payments = Payment::whereSubtype(PaymentSubtype::STK->name)->with([
            'provider:id,status,reference,checkout_request_id,amount,phone,created_at',
            'provider.response:id,checkout_request_id,result_desc,created_at',
        ])->latest()->limit(100)->get();

        return $this->successResponse($payments);
    }

    public function getC2BPayments(): JsonResponse
    {
        $payments = Payment::whereSubtype(PaymentSubtype::C2B)->with([
            'provider:id,transaction_type,trans_id,trans_amount,first_name,middle_name,last_name,msisdn,created_at',
        ])->latest()->limit(100)->get();

        return $this->successResponse($payments);
    }

    public function getB2CPayments(): JsonResponse
    {
        $payments = Payment::whereDestinationSubtype(PaymentSubtype::B2C)->latest()->limit(100)->get();

        return $this->successResponse($payments);
    }

    public function getB2BPayments(): JsonResponse
    {
        $payments = Payment::whereDestinationType(PaymentType::TENDE)
                           ->whereDestinationSubtype(PaymentSubtype::B2B)
                           ->latest()
                           ->limit(100)
                           ->get();

        return $this->successResponse($payments);
    }

    public function queryMpesaStatus(): JsonResponse
    {
        $exitCode = Artisan::call('mpesa:query_stk_status');

        return $this->successResponse(['status' => $exitCode]);
    }
}
