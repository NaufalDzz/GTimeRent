<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('kategori', ''));

        $units = Unit::query()
            ->with('categories')
            ->withHeldFlag()
            ->when($query !== '', fn (Builder $units) => $units->where(
                fn (Builder $match) => $match->where('name', 'like', '%'.$query.'%')->orWhere('code', 'like', '%'.$query.'%'),
            ))
            ->when($category !== '', fn (Builder $units) => $units->whereHas(
                'categories',
                fn (Builder $categories) => $categories->where('name', $category),
            ))
            ->orderBy('kind')
            ->orderBy('code')
            ->get();

        return view('catalog.index', [
            'units' => $units,
            'query' => $query,
            'category' => $category,
            'categories' => Category::query()->whereHas('units')->orderBy('name')->pluck('name'),
            'total' => Unit::query()->count(),
        ]);
    }

    public function show(Unit $unit): View
    {
        $unit->load('categories');

        $partners = $unit->isAvailable()
            ? Unit::query()->available()->where('kind', '!=', $unit->kind)->orderBy('package_price')->get()
            : collect();

        return view('catalog.show', [
            'unit' => $unit,
            'partners' => $partners,
            'shippingFee' => Setting::current()->flat_shipping_fee,
        ]);
    }
}
