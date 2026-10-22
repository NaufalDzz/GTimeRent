<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Error register dipisah dari error login karena keduanya ada di halaman yang sama.
     *
     * @var string
     */
    protected $errorBag = 'register';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Kolom role tidak divalidasi dan tidak pernah dibaca, sehingga register selalu membuat anggota.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('login', ['tab' => 'daftar']);
    }
}
