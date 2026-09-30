@props(['label' => null, 'name' => null, 'hint' => null, 'required' => false, 'for' => null])
{{-- Pembungkus label + input + pesan error. Input diletakkan di slot. --}}
<div {{ $attributes }}>
    @if ($label)
        <label for="{{ $for ?? $name }}" class="form-label">{{ $label }}@if ($required)<span class="text-red-500"> *</span>@endif</label>
    @endif
    {{ $slot }}
    @if ($name && $errors->has($name))
        <p class="form-error">{{ $errors->first($name) }}</p>
    @elseif ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
</div>
