<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ContactRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileRequest extends FormRequest
{
    use ContactRules;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Role sengaja tidak ada di sini: form profil tidak dapat mengubah peran.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:254', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'phone' => $this->phoneRules(false),
            'address' => $this->addressRules(false),
            'service_area' => $this->serviceAreaRules(false),
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePhone('phone');
    }
}
