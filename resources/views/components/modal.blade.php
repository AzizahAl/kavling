@props(['name', 'title' => null, 'judulJs' => null, 'subtitle' => null, 'maxWidth' => 'lg', 'show' => false])
{{--
    Popup seragam: latar meredup, judul, tombol tutup, isi bisa digulir, tutup dengan Esc / klik latar.
      Buka: $dispatch('open-modal', 'nama')   Tutup: $dispatch('close-modal', 'nama')
      Judul dinamis Alpine: judul-js="f.id ? 'Ubah' : 'Tambah'"
    Susunan isi:  <form class="contents"> <x-modal-body>…</x-modal-body> <x-modal-footer>…</x-modal-footer> </form>
    Di HP tampil sebagai lembar dari bawah.
--}}
@php
    $lebar = ['sm' => 'sm:max-w-sm', 'md' => 'sm:max-w-md', 'lg' => 'sm:max-w-lg', 'xl' => 'sm:max-w-xl', '2xl' => 'sm:max-w-2xl', '3xl' => 'sm:max-w-3xl'][$maxWidth] ?? 'sm:max-w-lg';
@endphp
<div x-data="{ open: @js((bool) $show) }"
     x-on:open-modal.window="if ($event.detail === @js($name)) open = true"
     x-on:close-modal.window="if ($event.detail === @js($name)) open = false"
     x-on:keydown.escape.window="if (open && ! $store.confirm.open && ! $store.toast.daftar.length) open = false"
     x-effect="document.body.classList.toggle('overflow-hidden', open)"
     x-init="$watch('open', v => v && $nextTick(() => $refs.panel.querySelector('input:not([type=hidden]):not([readonly]),select,textarea')?.focus({ preventScroll: true })))"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true">
    <div x-show="open" x-transition.opacity.duration.200ms class="absolute inset-0 bg-slate-900/45 backdrop-blur-[2px]" x-on:click="open = false"></div>
    <div x-ref="panel" x-show="open"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-2 sm:scale-[.98]" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-2"
         {{ $attributes->merge(['class' => "relative flex max-h-[92dvh] w-full flex-col overflow-hidden rounded-t-popup bg-white shadow-popup sm:max-h-[88vh] sm:rounded-popup {$lebar}"]) }}>
        @if ($title || $judulJs)
            <div class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div class="min-w-0">
                    <h3 class="text-base font-semibold text-slate-900" @if ($judulJs) x-text="{{ $judulJs }}" @endif>{{ $title }}</h3>
                    @if ($subtitle)<p class="mt-0.5 text-sm text-slate-500">{{ $subtitle }}</p>@endif
                </div>
                <button type="button" class="btn-icon -mt-1 -mr-2" x-on:click="open = false" aria-label="Tutup"><x-icon name="x" class="size-[18px]"/></button>
            </div>
        @endif
        <div class="flex min-h-0 flex-1 flex-col">{{ $slot }}</div>
    </div>
</div>
