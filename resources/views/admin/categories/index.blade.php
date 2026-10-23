@extends('layouts.admin')

@section('title', 'Kategori')

@php
    $updateBag = \App\Http\Requests\Admin\CategoryRequest::UPDATE_BAG;
@endphp

@section('content')
<div class="eyebrow">Master data · {{ $activeCount }} aktif · {{ $archivedCount }} arsip</div>
<h1 class="h1-admin">Kategori</h1>
<p class="text-subtle" style="margin: 10px 0 0; max-width: 640px; font-size: 14px">Satu unit boleh masuk beberapa kategori. Kategori yang diarsipkan tidak bisa dipilih untuk unit baru, tapi tetap terbaca di riwayat.</p>

<form method="POST" action="{{ route('admin.categories.store') }}" class="hstack" style="margin-top: 28px; align-items: flex-end; max-width: 640px" novalidate>
@csrf
<div class="filter-field" style="flex: 1 1 260px">
<label for="k-new">Kategori baru</label>
<input id="k-new" name="name" type="text" class="input" value="{{ $errors->default->any() ? old('name') : '' }}" placeholder="mis. Streaming" maxlength="80" required @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
</div>
<button type="submit" class="btn btn-primary btn-sm">Tambah kategori</button>
<div style="flex-basis: 100%"><x-field-error name="name" /></div>
</form>

@if ($categories->isEmpty())
<div class="empty" style="margin-top: 24px; max-width: 760px">Belum ada kategori.</div>
@else
<ul class="card card-flush" style="margin: 24px 0 0; padding: 0; list-style: none; max-width: 760px">
@foreach ($categories as $category)
@php($isEditing = $editingId === $category->id && ! $category->trashed())
<li class="between" style="padding: 14px 20px; border-bottom: 1px solid var(--rule); @if ($isEditing) background: #170c12; @endif">
<div style="flex: 1 1 240px; min-width: 0">
@if ($isEditing)
<form method="POST" action="{{ route('admin.categories.update', $category) }}" id="edit-{{ $category->id }}" class="stack-sm" novalidate>
@csrf
@method('PUT')
<label for="k-edit-{{ $category->id }}">Nama kategori</label>
<input id="k-edit-{{ $category->id }}" name="name" type="text" class="input" value="{{ $errors->getBag($updateBag)->any() ? old('name') : $category->name }}" maxlength="80" required @error('name', $updateBag) aria-invalid="true" aria-describedby="k-edit-error" @enderror>
<x-field-error name="name" :bag="$updateBag" id="k-edit-error" />
</form>
@else
<div style="font-weight: 500; @if ($category->trashed()) color: var(--dim) @endif">{{ $category->name }}</div>
@endif
<div class="tiny text-dim" style="margin-top: 2px">{{ $category->units_count }} unit aktif @if ($category->trashed()) · tidak tersedia untuk penugasan baru @endif</div>
</div>
<span class="badge {{ $category->trashed() ? 'tone-muted' : 'tone-success' }}">{{ $category->trashed() ? 'Diarsipkan' : 'Aktif' }}</span>
<div class="hstack" style="gap: 8px">
@if ($isEditing)
<button type="submit" form="edit-{{ $category->id }}" class="btn btn-primary btn-sm">Simpan</button>
<a href="{{ route('admin.categories.index') }}" class="btn btn-sm">Batal</a>
@elseif (! $category->trashed())
<a href="{{ route('admin.categories.index', ['ubah' => $category->id]) }}" class="btn btn-sm">Ubah</a>
@endif
@if ($category->trashed())
<form method="POST" action="{{ route('admin.categories.restore', $category) }}">
@csrf
<button type="submit" class="btn btn-sm">Pulihkan</button>
</form>
@elseif (! $isEditing)
<form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Arsipkan kategori ini?')">
@csrf
@method('DELETE')
<button type="submit" class="btn btn-sm btn-danger">Arsipkan</button>
</form>
@endif
</div>
</li>
@endforeach
</ul>
@endif
@endsection
