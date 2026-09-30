@props(['icon', 'label', 'href' => null, 'variant' => null, 'type' => 'button'])
{{-- Tombol yang hanya berupa ikon, selalu dengan tooltip & aria-label. variant: danger --}}
@php $kelas = 'btn-icon' . ($variant === 'danger' ? ' btn-icon-danger' : ''); @endphp
@if ($href)
    <a href="{{ $href }}" x-data x-tip="@js($label)" aria-label="{{ $label }}" {{ $attributes->merge(['class' => $kelas]) }}><x-icon :name="$icon" class="size-[18px]"/></a>
@else
    <button type="{{ $type }}" x-data x-tip="@js($label)" aria-label="{{ $label }}" {{ $attributes->merge(['class' => $kelas]) }}><x-icon :name="$icon" class="size-[18px]"/></button>
@endif
