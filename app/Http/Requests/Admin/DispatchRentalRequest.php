<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Hanya format waktu yang divalidasi di sini; urutan waktu dan larangan masa depan dijaga domain.
 */
class DispatchRentalRequest extends FormRequest
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
            'dispatched_at' => ['required', 'date_format:Y-m-d\TH:i,Y-m-d\TH:i:s'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'dispatched_at' => 'waktu keberangkatan kurir',
        ];
    }
}
