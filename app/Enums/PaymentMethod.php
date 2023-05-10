<?php

namespace App\Enums;

use Exception;

enum PaymentMethod: string
{
    case MPESA = 'MPESA';
    case VOUCHER = 'VOUCHER';
    case FLOAT = 'FLOAT';

    public function getTypeAndSubtype(): array
    {
        $mpesaType = PaymentType::tryFrom(config('services.sidooh.providers.mpesa.stk')) ?? PaymentType::MPESA;
        return match ($this) {
            self::MPESA   => [$mpesaType, PaymentSubtype::STK],
            self::VOUCHER => [PaymentType::SIDOOH, PaymentSubtype::VOUCHER],
            self::FLOAT   => [PaymentType::SIDOOH, PaymentSubtype::FLOAT],
        };
    }

    /**
     * @throws Exception
     */
    public function getWithdrawalTypeAndSubtype(): array
    {
        return match ($this) {
            self::MPESA   => [PaymentType::MPESA, PaymentSubtype::B2C],
            self::VOUCHER => [PaymentType::SIDOOH, PaymentSubtype::VOUCHER],
            self::FLOAT   => throw new Exception('Unsupported payment method')
        };
    }
}
