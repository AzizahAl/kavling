@props(['label' => null, 'name' => null, 'hint' => null, 'required' => false, 'for' => null])
{{-- Label + isian + pesan error tepat di bawah isian. Tanda wajib (*) seragam. --}}
@php
    $kunci = $name ? str_replace(['[', ']'], ['.', ''], $name) : null;
    $salah = $kunci && $errors->has($kunci);
@endphp
<div {{ $attributes->class(['min-w-0', 'is-invalid' => $salah]) }}>
    @if ($label)
        <label for="{{ $for ?? $name }}" @class(['form-label', 'wajib' => $required])>{{ $label }}</label>
    @endif
    {{ $slot }}
    @if ($salah)
        <p class="form-error" role="alert"><x-icon name="alert" class="mt-px size-3.5"/>{{ $errors->first($kunci) }}</p>
    @elseif ($hint)
        <p class="form-hint">{{ $hint }}</p>
    @endif
</div>
