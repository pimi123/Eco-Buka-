<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiateNestPayPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shopurl' => ['nullable', 'url', 'max:2048'],
            'installment_count' => ['nullable', 'integer', 'min:2', 'max:60'],
        ];
    }
}
