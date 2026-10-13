<?php

namespace App\Http\Requests;

class InitiateNestPayCheckoutRequest extends StoreOrderRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'shopurl' => ['nullable', 'url', 'max:2048'],
            'installment_count' => ['nullable', 'integer', 'min:2', 'max:60'],
        ]);
    }
}
