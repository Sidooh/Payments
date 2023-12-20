<?php

namespace App\Repositories\EventRepositories;

use App\DTOs\PaymentDTO;
use App\Enums\Description;
use App\Enums\EventType;
use App\Enums\PaymentCodes;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\Status;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Repositories\PaymentRepositories\PaymentRepository;
use App\Repositories\SidoohRepositories\FloatAccountRepository;
use App\Services\SidoohNotify;
use App\Services\SidoohService;
use DrH\Mpesa\Entities\MpesaB2bCallback;
use DrH\Mpesa\Entities\MpesaB2cResultParameter;
use DrH\Mpesa\Entities\MpesaBulkPaymentResponse;
use DrH\Mpesa\Entities\MpesaStkCallback;
use Error;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class MpesaEventRepository
{
    public static function stkPaymentFailed(MpesaStkCallback $stkCallback): void
    {
        $payment = Payment::whereProvider(PaymentType::MPESA, PaymentSubtype::STK, $stkCallback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING) {
            Log::critical('Payment is not pending...', [$payment, $stkCallback->request]);

            return;
        }

        $payment->update(['status' => Status::FAILED]);

        $x = (object) $payment->toArray();

        [$x->error_code, $x->error_message] = match ($stkCallback->result_code) {
            1, '1' => [PaymentCodes::MPESA_INSUFFICIENT_BALANCE, 'Mpesa - Insufficient balance'],
            1031, 1032, '1031', '1032' => [PaymentCodes::MPESA_CANCELLED, 'Mpesa - Cancelled'],
            1037, '1037' => [PaymentCodes::MPESA_TIMEOUT, 'Mpesa - Timed out'],
            default => [PaymentCodes::MPESA_FAILED, 'Mpesa - Failed']
        };

        SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($x));
    }

    /**
     * @throws Throwable
     */
    public static function stkPaymentReceived(MpesaStkCallback $stkCallback): void
    {
        $payment = Payment::whereProvider(PaymentType::MPESA, PaymentSubtype::STK, $stkCallback->request->id)->firstOrFail();

        if ($payment->status !== Status::PENDING) {
            Log::critical('Payment is not pending...', [$payment, $stkCallback->request]);

            return;
        }

        // Complete payment
        if (!$payment->destination_type) {
            $payment->update(['status' => Status::COMPLETED]);

            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));

            return;
        }

        // Handle destination payment
        $repo = new PaymentRepository(PaymentDTO::fromPayment($payment), $payment->ipn);

        $repo->processPayment();
    }

    public static function b2cPaymentSent(MpesaBulkPaymentResponse $paymentResponse): void
    {
        try {
            $payment = Payment::whereDestinationProvider(PaymentType::MPESA, PaymentSubtype::B2C, $paymentResponse->request->id)
                ->firstOrFail();
            if ($payment->status !== Status::PENDING) {
                throw new Error("Payment is not pending... - $payment->id");
            }

            $payment->update(['status' => Status::COMPLETED]);

            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));

            dispatch(function() {
                $balance = MpesaB2cResultParameter::latest('id')->value('b2c_utility_account_available_funds');
                $threshold = config('services.sidooh.providers.mpesa.b2c_balance_threshold', 5000);

                if ($balance <= $threshold) {
                    SidoohNotify::notify(admin_contacts(), "B2C Alert!\n\n$balance", EventType::ERROR_ALERT);
                }

            })->delay(now()->addSeconds(5));
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    /**
     * @throws \Throwable
     */
    public static function b2cPaymentFailed(MpesaBulkPaymentResponse $paymentResponse): void
    {
        try {
            $payment = Payment::whereDestinationProvider(PaymentType::MPESA, PaymentSubtype::B2C, $paymentResponse->request->id)
                ->firstOrFail();

            if ($payment->status !== Status::PENDING) {
                throw new Error("Payment is not pending... - $payment->id");
            }

            $account = $payment->provider->floatAccount;

            FloatAccountRepository::credit(
                $account->id,
                $payment->amount,
                Description::ACCOUNT_WITHDRAWAL_REFUND->value,
                $payment->charge
            );

            $payment->update(['status' => Status::FAILED]);

            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    public static function b2bPaymentSent(MpesaB2bCallback $paymentCallback): void
    {
        try {
            $payment = Payment::whereDestinationProvider(PaymentType::MPESA, PaymentSubtype::B2B, $paymentCallback->request->id)
                ->with('destinationProvider.response')
                ->firstOrFail();
            if ($payment->status !== Status::PENDING) {
                throw new Error("Payment is not pending... - $payment->id");
            }

            $payment->update(['status' => Status::COMPLETED]);

            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));

            dispatch(function() use ($paymentCallback) {
                $threshold = config('services.sidooh.providers.mpesa.b2b_balance_threshold', 10000);

                $balance = (int)explode('|', $paymentCallback->debit_account_balance)[2];

                if ($balance <= $threshold) {
                    SidoohNotify::notify(admin_contacts(), "B2B Alert!\n\n$balance", EventType::ERROR_ALERT);
                }

            })->delay(now()->addSeconds(5));
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    /**
     * @throws \Throwable
     */
    public static function b2bPaymentFailed(MpesaB2bCallback $paymentCallback): void
    {
        try {
            $payment = Payment::whereDestinationProvider(PaymentType::MPESA, PaymentSubtype::B2B, $paymentCallback->request->id)
                ->firstOrFail();

            Log::error($payment);

            if ($payment->status !== Status::PENDING) {
                throw new Error("Payment is not pending... - $payment->id");
            }

            $account = $payment->provider->floatAccount;

            FloatAccountRepository::credit(
                $account->id,
                $payment->amount,
                Description::PAYMENT_REVERSAL->value,
                $payment->charge
            );

            $payment->update(['status' => Status::FAILED]);

            SidoohService::sendCallback($payment->ipn, 'POST', PaymentResource::make($payment));
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    /**
     * @throws \Exception
     */
    //    public static function c2bPaymentConfirmed(MpesaC2bCallback $callback): void
    //    {
    //        $account = SidoohAccounts::findByPhone($callback->msisdn);
    //        $voucher = VoucherRepository::getDefaultVoucherForAccount($account['id']);
    //        $reason = 'Till payment made to Sidooh.';
    //
    //        $repo = new PaymentRepository(
    //            new PaymentDTO(
    //                $account['id'],
    //                $callback->trans_amount,
    //                PaymentType::SIDOOH,
    //                PaymentSubtype::FLOAT,
    //                Description::VOUCHER_CREDIT->value,
    //                $reason,
    //                1,
    //                false,
    //                PaymentType::SIDOOH,
    //                PaymentSubtype::VOUCHER,
    //                ['voucher_id' => $voucher->id]
    //            )
    //        );
    //
    //        $payment = $repo->processPayment();
    //
    //        $voucher = $voucher->refresh();
    //
    //        $amount = 'Ksh'.number_format($payment->amount, 2);
    //        $balance = 'Ksh'.number_format($voucher->balance, 2);
    //        $date = $payment->updated_at->timezone('Africa/Nairobi')->format(config('settings.sms_date_time_format'));
    //
    //        $message = "You have received $amount voucher ";
    //        $message .= "from Sidooh on $date.\n";
    //        $message .= "\n\tReason - {$reason}\n\n";
    //        $message .= "New voucher balance is $balance.\n\n";
    //        $message .= "Dial *384*99# NOW for FREE on your Safaricom line to BUY AIRTIME or PAY BILLS & PAY USING the voucher received.\n\n";
    //        $message .= config('services.sidooh.tagline');
    //
    //        $account = SidoohAccounts::find($voucher->account_id);
    //        SidoohNotify::notify($account['phone'], $message, EventType::VOUCHER_CREDITED);
    //    }
}
