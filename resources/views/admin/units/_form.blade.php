@php
    $isNew = ! $unit->exists;
    $selectedCategoryIds = collect(old('categories', $unit->exists ? $unit->categories->reject->trashed()->modelKeys() : []))->map(fn ($id) => (int) $id)->all();
    $archivedCategories = $unit->exists ? $unit->categories->filter->trashed() : collect();
    $kindValue = old('kind', $unit->kind?->value);
    $conditionValue = old('condition_status', $unit->condition_status?->value ?? 'ready');
@endphp

<form method="POST" action="{{ $isNew ? route('admin.units.store') : route('admin.units.update', $unit) }}" aria-label="Form unit" class="aside-card card-flush" style="padding: 0" novalidate>
@csrf
@unless ($isNew)
@method('PUT')
@endunless

<div class="between" style="padding: 20px 24px; border-bottom: 1px solid var(--card-line)">
<div>
<div class="label-mono">{{ $isNew ? 'Unit baru' : 'Ubah unit' }}</div>
<div class="h2" style="margin-top: 4px">{{ $isNew ? 'Tambah paket fisik' : $unit->code }}</div>
</div>
<div class="hstack" style="gap: 6px">
@if ($unit->trashed())
<span class="badge tone-muted">Diarsipkan</span>
@endif
@if ($isHeld)
<span class="badge tone-warning">Sedang ditahan</span>
@endif
</div>
</div>

<div class="stack" style="padding: 20px 24px">
<div class="field-row">
<div class="field">
<label for="u-code">Kode unit</label>
@if ($isNew)
<input id="u-code" name="code" type="text" class="input mono" value="{{ old('code') }}" placeholder="mis. PC-MID-003" maxlength="30" required @error('code') aria-invalid="true" aria-describedby="code-error" @enderror>
@else
<input id="u-code" type="text" class="input mono" value="{{ $unit->code }}" readonly aria-describedby="u-code-hint">
<p class="field-hint" id="u-code-hint">Kode mewakili satu paket fisik dan tidak dapat diganti.</p>
@endif
<x-field-error name="code" />
</div>
<div class="field">
<label for="u-kind">Jenis</label>
@if ($isHeld)
<input type="hidden" name="kind" value="{{ $unit->kind->value }}">
@endif
<select id="u-kind" @unless ($isHeld) name="kind" @endunless class="input" @disabled($isHeld) @error('kind') aria-invalid="true" aria-describedby="kind-error" @enderror>
@foreach (\App\Domain\Catalog\UnitKind::cases() as $kind)
<option value="{{ $kind->value }}" @selected($kindValue === $kind->value)>{{ $kind->label() }}</option>
@endforeach
</select>
<x-field-error name="kind" />
</div>
</div>

<div class="field">
<label for="u-name">Nama paket</label>
<input id="u-name" name="name" type="text" class="input" value="{{ old('name', $unit->name) }}" placeholder="2–120 karakter" maxlength="120" required @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
<p class="field-hint">Nama boleh sama dengan unit lain; kode yang membedakan set fisik.</p>
<x-field-error name="name" />
</div>

<div class="field">
<label for="u-spec">Spesifikasi</label>
<textarea id="u-spec" name="specs_text" rows="3" class="input small" placeholder="CPU, GPU, RAM, monitor… pisahkan dengan ; atau baris baru" required @error('specs_text') aria-invalid="true" aria-describedby="specs_text-error" @enderror>{{ old('specs_text', $unit->specs_text) }}</textarea>
<x-field-error name="specs_text" />
</div>

<div class="field">
<label for="u-contents">Kelengkapan tetap <span class="text-dim">(satu baris per item)</span></label>
<textarea id="u-contents" name="contents_text" rows="4" class="input mono tiny" style="line-height: 1.6" required @readonly($isHeld) @error('contents_text') aria-invalid="true" aria-describedby="contents_text-error" @enderror>{{ old('contents_text', $unit->contents_text) }}</textarea>
@if ($isHeld)
<p class="field-hint" style="color: #f6cf8e">Jenis dan kelengkapan dikunci selama unit ditahan pesanan.</p>
@endif
<x-field-error name="contents_text" />
</div>

<fieldset style="margin: 0; padding: 0; border: 0">
<legend class="field-label" style="padding: 0">Kategori <span class="text-dim">(min. 1 kategori aktif)</span></legend>
<div class="chips" style="margin-top: 10px; gap: 8px 14px">
@forelse ($categories as $category)
<label class="check" style="align-items: center"><input type="checkbox" name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id, $selectedCategoryIds, true))>{{ $category->name }}</label>
@empty
<p class="field-hint">Belum ada kategori aktif. <a href="{{ route('admin.categories.index') }}">Tambah kategori</a> dulu.</p>
@endforelse
</div>
@if ($archivedCategories->isNotEmpty())
<p class="field-hint" style="margin-top: 8px">Tetap tertaut (kategori diarsipkan):
@foreach ($archivedCategories as $category)
<span class="chip" style="opacity: 0.6">{{ $category->name }}</span>
@endforeach
</p>
@endif
<x-field-error name="categories" />
@foreach ($errors->get('categories.*') as $messages)
<p class="field-error" role="alert">{{ $messages[0] }}</p>
@endforeach
</fieldset>

<div class="field-row">
<div class="field" style="flex-basis: 140px">
<label for="u-price">Harga paket ≤5 hari (Rp)</label>
<input id="u-price" name="package_price" type="number" min="0" max="1000000000" step="1" inputmode="numeric" class="input" value="{{ old('package_price', $unit->package_price) }}" required @error('package_price') aria-invalid="true" aria-describedby="package_price-error" @enderror>
<x-field-error name="package_price" />
</div>
<div class="field" style="flex-basis: 140px">
<label for="u-fine">Denda / hari (Rp)</label>
<input id="u-fine" name="daily_fine" type="number" min="0" max="1000000000" step="1" inputmode="numeric" class="input" value="{{ old('daily_fine', $unit->daily_fine) }}" required @error('daily_fine') aria-invalid="true" aria-describedby="daily_fine-error" @enderror>
<x-field-error name="daily_fine" />
</div>
</div>
<p class="field-hint" style="margin-top: -6px">Bilangan bulat. Perubahan hanya berlaku untuk checkout berikutnya.</p>

<fieldset style="margin: 0; padding: 0; border: 0">
<legend class="field-label" style="padding: 0">Kondisi</legend>
<div class="field-row" style="margin-top: 8px">
@foreach (\App\Domain\Catalog\UnitCondition::cases() as $condition)
<label class="radio-card" style="flex: 1 1 140px"><input type="radio" name="condition_status" value="{{ $condition->value }}" @checked($conditionValue === $condition->value) @disabled($isHeld && $condition === \App\Domain\Catalog\UnitCondition::Maintenance)><span>{{ $condition === \App\Domain\Catalog\UnitCondition::Ready ? 'Ready' : 'Maintenance' }}</span><span class="dot" aria-hidden="true"></span></label>
@endforeach
</div>
@if ($isHeld)
<p class="field-hint" style="margin-top: 8px">Maintenance tersedia setelah pesanan yang menahan unit ini selesai.</p>
@endif
<x-field-error name="condition_status" />
</fieldset>

<div class="hstack" style="gap: 10px; margin-top: 4px">
<button type="submit" class="btn btn-primary" style="flex: 1 1 160px">{{ $isNew ? 'Simpan unit' : 'Simpan perubahan' }}</button>
<a href="{{ route('admin.units.index') }}" class="btn">Kembali</a>
</div>
</div>
</form>
