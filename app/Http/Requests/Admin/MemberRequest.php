<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ContactRules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class MemberRequest extends FormRequest
{
    use ContactRules;

    /**
     * Akun admin hanya tampil baca di halaman anggota; form ini tidak mengubahnya.
     */
    public function authorize(): bool
    {
        if (! ($this->user()?->isAdmin() ?? false)) {
            return false;
        }

        $member = $this->editedMember();

        return $member === null || $member->isMember();
    }

    /**
     * Role sengaja tidak ada di sini: akun dari form admin selalu anggota.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $member = $this->editedMember();

        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:254', Rule::unique('users', 'email')->ignore($member?->id)],
            'password' => [$member === null ? 'required' : 'nullable', 'string', 'confirmed', Password::min(8)],
            'phone' => $this->phoneRules(false),
            'address' => $this->addressRules(false),
            'service_area' => $this->serviceAreaRules(false),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah dipakai, termasuk akun yang diarsipkan.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePhone('phone');
    }

    private function editedMember(): ?User
    {
        $user = $this->route('user');

        return $user instanceof User ? $user : null;
    }
}
