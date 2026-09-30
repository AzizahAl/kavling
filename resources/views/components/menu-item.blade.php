@props(['href' => null, 'icon' => null, 'type' => 'button', 'danger' => false])
@php $kelas = 'flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm transition-colors duration-100 focus:outline-none ' . ($danger ? 'text-red-600 hover:bg-red-50 focus:bg-red-50' : 'text-slate-700 hover:bg-slate-100 focus:bg-slate-100'); @endphp
@if ($href)
    <a href="{{ $href }}" role="menuitem" {{ $attributes->merge(['class' => $kelas]) }}>@if ($icon)<x-icon :name="$icon" class="size-4 opacity-70"/>@endif{{ $slot }}</a>
@else
    <button type="{{ $type }}" role="menuitem" {{ $attributes->merge(['class' => $kelas]) }}>@if ($icon)<x-icon :name="$icon" class="size-4 opacity-70"/>@endif{{ $slot }}</button>
@endif
