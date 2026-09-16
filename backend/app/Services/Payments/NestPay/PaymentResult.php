<?php

namespace App\Services\Payments\NestPay;

use App\Models\Payment;

class PaymentResult
{
    public function __construct(
        public readonly bool $approved,
        public readonly string $code,
        public readonly ?Payment $payment,
        public readonly string $message,
    ) {
    }
}
