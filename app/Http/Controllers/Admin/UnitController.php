<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\UnitCondition;
use App\Domain\Catalog\UnitKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UnitRequest;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UnitController extends Controller
{
    /**
     * Filter status yang tersedia di daftar unit admin.
     *
     * @var array<string, string>
     */
    public const FILTERS = [
        '' => 'Semua',
        'tersedia' => 'Tersedia',
        'ditahan' => 'Sedang ditahan',
        'maintenance' => 'Maintenance',
        'arsip' => 'Diarsipkan',
    ];

    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $filter = (string) $request->query('status', '');
        if (! array_key_exists($filter, self::FILTERS)) {
            $filter = '';
        }

        $units = Unit::withTrashed()
            ->with([
                'categories',
                'rentalItems' => fn (HasMany $items) => $items
                    ->whereHas('rental', fn (Builder $rentals) => $rentals->holding())
                    ->with('rental'),
            ])
            ->withHeldFlag()
            ->when($query !== '', fn (Builder $units) => $units->where(
                fn (Builder $match) => $match->where('name', 'like', '%'.$query.'%')->orWhere('code', 'like', '%'.$query.'%'),
            ))
            ->when($filter === 'tersedia', fn (Builder $units) => $units->whereNull('deleted_at')->available())
            ->when($filter === 'ditahan', fn (Builder $units) => $units->held())
            ->when($filter === 'maintenance', fn (Builder $units) => $units->whereNull('deleted_at')->where('condition_status', UnitCondition::Maintenance))
            ->when($filter === 'arsip', fn (Builder $units) => $units->whereNotNull('deleted_at'))
            ->orderByRaw('deleted_at is not null')
            ->orderBy('kind')
            ->orderBy('code')
            ->get();

        $editing = $request->boolean('baru') ? null : ($request->filled('ubah')
            ? Unit::withTrashed()->with('categories')->where('code', $request->query('ubah'))->firstOrFail()
            : $units->first());

        return view('admin.units.index', [
            'units' => $units,
            'query' => $query,
            'filter' => $filter,
            'filters' => self::FILTERS,
            'activeCount' => Unit::query()->count(),
            'editing' => $editing,
            'categories' => $this->activeCategories(),
        ]);
    }

    public function create(): View
    {
        return view('admin.units.create', [
            'unit' => new Unit(['kind' => UnitKind::PcMonitor, 'condition_status' => UnitCondition::Ready]),
            'categories' => $this->activeCategories(),
            'isHeld' => false,
        ]);
    }

    public function store(UnitRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $unit = DB::transaction(function () use ($data): Unit {
            $unit = Unit::query()->create(collect($data)->except('categories')->all());
            $unit->categories()->sync($data['categories']);

            return $unit;
        });

        return redirect()->route(
            $request->boolean('return_to_list') ? 'admin.units.index' : 'admin.units.edit',
            $request->boolean('return_to_list') ? ['ubah' => $unit->code] : $unit,
        )
            ->with('status', "Unit {$unit->code} tersimpan dan tampil di katalog.");
    }

    public function edit(Unit $unit): View
    {
        $unit->load('categories');

        return view('admin.units.edit', [
            'unit' => $unit,
            'categories' => $this->activeCategories(),
            'isHeld' => $unit->isHeld(),
        ]);
    }

    /**
     * Pemeriksaan unit ditahan diulang di bawah kunci baris yang sama dengan checkout,
     * agar perubahan tidak menyusup di antara validasi dan penyimpanan.
     */
    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $unit, $data): void {
            $locked = Unit::withTrashed()->lockForUpdate()->findOrFail($unit->id);

            $violations = $request->heldViolations($locked);
            if ($violations !== []) {
                throw ValidationException::withMessages($violations);
            }

            $archivedCategoryIds = $locked->categories->filter->trashed()->modelKeys();

            $locked->update(collect($data)->except('categories')->all());
            $locked->categories()->sync(array_merge($data['categories'], $archivedCategoryIds));
        });

        return redirect()->route(
            $request->boolean('return_to_list') ? 'admin.units.index' : 'admin.units.edit',
            $request->boolean('return_to_list') ? ['ubah' => $unit->code] : $unit,
        )
            ->with('status', 'Unit tersimpan. Transaksi lama tetap memakai snapshot harga.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $archived = DB::transaction(function () use ($unit): bool {
            $locked = Unit::query()->lockForUpdate()->findOrFail($unit->id);
            if ($locked->isHeld()) {
                return false;
            }

            return (bool) $locked->delete();
        });

        if (! $archived) {
            return back()->with('error', "Unit {$unit->code} masih ditahan pesanan. Arsip tersedia setelah pesanannya selesai atau dibatalkan.");
        }

        return redirect()->route('admin.units.index')
            ->with('status', "Unit {$unit->code} diarsipkan. Riwayat transaksinya tetap terbaca.");
    }

    public function restore(Unit $unit): RedirectResponse
    {
        $unit->restore();

        return back()->with('status', "Unit {$unit->code} dipulihkan.");
    }

    /**
     * @return Collection<int, Category>
     */
    private function activeCategories(): Collection
    {
        return Category::query()->orderBy('name')->get();
    }
}
