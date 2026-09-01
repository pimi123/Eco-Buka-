<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'country' => $this->input('country') ?: 'Kosove',
            'city' => $this->input('city') ?: $this->input('municipality'),
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'country' => ['required', 'string', 'max:120', 'in:Kosove,Shqiperi,Maqedoni e Veriut'],
            'municipality' => ['required', 'string', 'max:120'],
            'delivery_address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'delivery_details' => ['nullable', 'string', 'max:1000'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'policy_accepted' => ['accepted'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.selected_options' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => 'Ju lutemi shkruani emrin dhe mbiemrin.',
            'customer_phone.required' => 'Ju lutemi shkruani numrin e telefonit.',
            'customer_email.email' => 'Ju lutemi shkruani një email valid.',
            'country.required' => 'Ju lutemi zgjedhni shtetin.',
            'country.in' => 'Shteti i zgjedhur nuk është valid.',
            'municipality.required' => 'Ju lutemi zgjedhni qytetin ose komunën.',
            'delivery_address.required' => 'Ju lutemi shkruani adresën e dërgesës.',
            'policy_accepted.accepted' => 'Duhet të pranoni politikat para se të dërgoni porosinë.',
            'items.required' => 'Shporta nuk mund të jetë e zbrazët.',
            'items.min' => 'Shporta nuk mund të jetë e zbrazët.',
        ];
    }
}
