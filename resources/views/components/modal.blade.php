@props(['name', 'title' => null, 'maxWidth' => '2xl', 'show' => false])
@php
    $width = ['sm' => 'sm:max-w-sm', 'md' => 'sm:max-w-md', 'lg' => 'sm:max-w-lg', 'xl' => 'sm:max-w-xl', '2xl' => 'sm:max-w-2xl', '3xl' => 'sm:max-w-3xl', '4xl' => 'sm:max-w-4xl'][$maxWidth] ?? 'sm:max-w-2xl';
@endphp
{{--
    Buka : $dispatch('open-modal', 'nama')   Tutup: $dispatch('close-modal', 'nama')
    Di layar HP modal tampil sebagai lembar dari bawah (bottom sheet).
--}}
<div x-data="{ open: @js((bool) $show) }"
     x-on:open-modal.window="if ($event.detail === @js($name)) open = true"
     x-on:close-modal.window="if ($event.detail === @js($name)) open = false"
     x-on:keydown.escape.window="open = false"
     x-effect="document.body.classList.toggle('overflow-hidden', open)"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true">
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px]" x-on:click="open = false"></div>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
         {{ $attributes->merge(['class' => "relative flex max-h-[92vh] w-full flex-col rounded-t-2xl bg-white shadow-xl sm:rounded-2xl {$width}"]) }}>
        @if ($title)
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
                <button type="button" class="btn-icon -mr-2" x-on:click="open = false" aria-label="Tutup"><x-icon name="x"/></button>
            </div>
        @endif
        <div class="overflow-y-auto overscroll-contain">{{ $slot }}</div>
    </div>
</div>
