<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

/**
 * Aturan kontak bersama untuk profil, checkout, dan form anggota admin (PRD bagian 6).
 */
trait ContactRules
{
    /**
     * @return array<int, mixed>
     */
    protected function phoneRules(bool $required): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'max:20', 'regex:/^\+?[0-9]{8,20}$/'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function addressRules(bool $required): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'min:10', 'max:1000'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function serviceAreaRules(bool $required): array
    {
        return [$required ? 'required' : 'nullable', 'string', Rule::in(config('gtrent.service_areas'))];
    }

    /**
     * Spasi dan tanda hubung di nomor telepon dibuang sebelum validasi.
     */
    protected function normalizePhone(string $field): void
    {
        $phone = $this->input($field);
        if (is_string($phone)) {
            $this->merge([$field => preg_replace('/[\s\-]/', '', $phone)]);
        }
    }
}
