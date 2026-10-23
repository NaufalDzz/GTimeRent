<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::withTrashed()
            ->withCount('units')
            ->orderByRaw('deleted_at is not null')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', [
            'categories' => $categories,
            'editingId' => $request->integer('ubah') ?: null,
            'activeCount' => $categories->reject->trashed()->count(),
            'archivedCount' => $categories->filter->trashed()->count(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::query()->create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('status', "Kategori {$category->name} ditambahkan.");
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('status', "Kategori {$category->name} tersimpan.");
    }

    /**
     * Arsip mempertahankan tautan unit yang sudah ada; kategori hanya hilang dari pilihan baru.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('status', "Kategori {$category->name} diarsipkan. Unit yang sudah tertaut tetap menampilkannya.");
    }

    public function restore(Category $category): RedirectResponse
    {
        $category->restore();

        return redirect()->route('admin.categories.index')
            ->with('status', "Kategori {$category->name} dipulihkan.");
    }
}
