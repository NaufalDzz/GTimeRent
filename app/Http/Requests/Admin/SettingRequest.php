<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
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
            'flat_shipping_fee' => ['required', 'integer', 'min:0', 'max:'.UnitRequest::MAX_MONEY],
            'service_area_text' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'flat_shipping_fee.integer' => 'Isi bilangan bulat rupiah tanpa desimal atau tanda minus.',
            'flat_shipping_fee.min' => 'Isi bilangan bulat rupiah tanpa desimal atau tanda minus.',
            'flat_shipping_fee.max' => 'Maksimal Rp1.000.000.000.',
        ];
    }
}
