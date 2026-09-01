<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'purpose' => $this->input('purpose') ?: ContactMessage::PURPOSE_OFFER,
        ]);
    }

    public function rules(): array
    {
        return [
            'purpose' => ['required', Rule::in(ContactMessage::PURPOSES)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
            'source_path' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'purpose.required' => 'Ju lutemi zgjedhni arsyen e kontaktit.',
            'purpose.in' => 'Arsyeja e kontaktit nuk eshte valide.',
            'name.required' => 'Ju lutemi shkruani emrin dhe mbiemrin.',
            'phone.required' => 'Ju lutemi shkruani numrin e telefonit.',
            'email.email' => 'Ju lutemi shkruani nje email valid.',
            'message.required' => 'Ju lutemi shkruani mesazhin.',
            'message.max' => 'Mesazhi eshte shume i gjate.',
        ];
    }
}
