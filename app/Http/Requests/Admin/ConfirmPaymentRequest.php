<?php

namespace App\Http\Requests\Admin;

use App\Domain\Rental\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'payment_method' => 'metode pembayaran',
        ];
    }

    public function paymentMethod(): PaymentMethod
    {
        return PaymentMethod::from($this->validated('payment_method'));
    }
}
