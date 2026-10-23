<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Error ubah dipisah agar tidak tampil di form tambah pada halaman yang sama.
     */
    public const UPDATE_BAG = 'categoryUpdate';

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Nama unik termasuk terhadap kategori arsip, agar riwayat tidak ambigu.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:80',
                Rule::unique('categories', 'name')->ignore($category instanceof Category ? $category->id : null),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama kategori',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Nama kategori sudah dipakai, termasuk kategori yang diarsipkan.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('category') instanceof Category) {
            $this->errorBag = self::UPDATE_BAG;
        }
    }
}
