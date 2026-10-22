@props(['name', 'bag' => 'default'])

@error($name, $bag)
<p class="field-error" id="{{ $attributes->get('id', $name.'-error') }}" role="alert">
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" style="vertical-align: -2px"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v5M12 16h.01"></path></svg>
{{ $message }}</p>
@enderror
