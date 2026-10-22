<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ContactRules;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    use ContactRules;

    public function authorize(): bool
    {
        return $this->user()?->isMember() ?? false;
    }

    /**
     * Unit diambil dari keranjang di session dan total dihitung ulang di server,
     * jadi harga atau total kiriman browser tidak pernah dibaca.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'min:2', 'max:100'],
            'recipient_phone' => $this->phoneRules(true),
            'address' => $this->addressRules(true),
            'service_area' => $this->serviceAreaRules(true),
            'agree' => ['accepted'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePhone('recipient_phone');
    }
}
