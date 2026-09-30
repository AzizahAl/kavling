@props(['variant' => 'primary', 'size' => null, 'icon' => null, 'href' => null, 'type' => 'button'])
{{-- Tombol seragam. variant: primary | secondary | neutral | danger | ghost. size: sm --}}
@php $kelas = 'btn btn-' . $variant . ($size === 'sm' ? ' btn-sm' : ''); @endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $kelas]) }}>
        @if ($icon)<x-icon :name="$icon" class="size-4"/>@endif{{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $kelas]) }}>
        @if ($icon)<x-icon :name="$icon" class="size-4"/>@endif{{ $slot }}
    </button>
@endif
