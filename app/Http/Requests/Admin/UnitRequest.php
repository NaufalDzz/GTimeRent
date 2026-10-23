<?php

namespace App\Http\Requests\Admin;

use App\Domain\Catalog\UnitCondition;
use App\Domain\Catalog\UnitKind;
use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UnitRequest extends FormRequest
{
    public const MAX_MONEY = 1_000_000_000;

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Kode hanya diisi saat tambah unit; setelah itu kode mewakili paket fisik tetap
     * dan tidak dapat diganti atau dipakai ulang, termasuk terhadap unit arsip.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'kind' => ['required', Rule::enum(UnitKind::class)],
            'specs_text' => ['required', 'string', 'max:5000'],
            'contents_text' => ['required', 'string', 'max:5000'],
            'package_price' => ['required', 'integer', 'min:0', 'max:'.self::MAX_MONEY],
            'daily_fine' => ['required', 'integer', 'min:0', 'max:'.self::MAX_MONEY],
            'condition_status' => ['required', Rule::enum(UnitCondition::class)],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'distinct', Rule::exists('categories', 'id')->whereNull('deleted_at')],
        ];

        if ($this->editedUnit() === null) {
            $rules['code'] = ['required', 'string', 'min:3', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('units', 'code')];
        }

        return $rules;
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $unit = $this->editedUnit();
                if ($unit === null || $validator->errors()->isNotEmpty()) {
                    return;
                }

                foreach ($this->heldViolations($unit) as $field => $message) {
                    $validator->errors()->add($field, $message);
                }
            },
        ];
    }

    /**
     * Perubahan yang dilarang selama unit ditahan pesanan (PRD FR-07).
     *
     * @return array<string, string>
     */
    public function heldViolations(Unit $unit): array
    {
        if (! $unit->isHeld()) {
            return [];
        }

        $violations = [];
        if ($this->input('kind') !== $unit->kind->value) {
            $violations['kind'] = 'Jenis unit tidak dapat diubah selama unit ditahan pesanan.';
        }
        if (self::normalizeLines((string) $this->input('contents_text')) !== self::normalizeLines((string) $unit->contents_text)) {
            $violations['contents_text'] = 'Kelengkapan tidak dapat diubah selama unit ditahan pesanan.';
        }
        if ($this->input('condition_status') === UnitCondition::Maintenance->value) {
            $violations['condition_status'] = 'Unit yang sedang ditahan pesanan tidak dapat diubah ke maintenance.';
        }

        return $violations;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'categories.*' => 'kategori',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Kode sudah dipakai, termasuk unit yang diarsipkan.',
            'code.regex' => 'Kode hanya boleh huruf, angka, tanda - atau _.',
            'categories.required' => 'Pilih minimal satu kategori aktif.',
            'categories.min' => 'Pilih minimal satu kategori aktif.',
            'categories.*.exists' => 'Kategori yang dipilih sudah diarsipkan atau tidak ditemukan.',
            'kind.enum' => 'Jenis unit tidak valid.',
            'condition_status.enum' => 'Kondisi tidak valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_map(
            fn (mixed $value): mixed => is_string($value) ? self::normalizeLines($value) : $value,
            $this->only('specs_text', 'contents_text'),
        ));
    }

    private function editedUnit(): ?Unit
    {
        $unit = $this->route('unit');

        return $unit instanceof Unit ? $unit : null;
    }

    private static function normalizeLines(string $text): string
    {
        return trim(str_replace("\r\n", "\n", $text));
    }
}
