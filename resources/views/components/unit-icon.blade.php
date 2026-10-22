@props(['kind', 'size' => 24, 'stroke' => 1.6])

@if ($kind === \App\Domain\Catalog\UnitKind::PcMonitor)
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}><rect x="2" y="4" width="20" height="13" rx="2"></rect><path d="M8 21h8M12 17v4"></path></svg>
@else
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}><rect x="2" y="7" width="20" height="11" rx="2"></rect><path d="M6 11h.01M9 11h.01M12 11h.01M15 11h.01M18 11h.01M7 14.5h10"></path></svg>
@endif
