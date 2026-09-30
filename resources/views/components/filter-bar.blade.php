@props(['action' => url()->current(), 'cari' => true, 'placeholder' => 'Cari…', 'abaikan' => []])
{{--
    Baris filter seragam (GET): pencarian + dropdown filter di slot + "Hapus filter".
    Filter terkirim otomatis saat dipilih; pencarian dengan Enter. Di HP filter dilipat di balik tombol "Filter".
    $abaikan: parameter yang tidak dihitung sebagai filter aktif (mis. tab status yang dipertahankan).
--}}
@php
    $aktif = collect(request()->except(array_merge(['page', 'cari'], $abaikan)))->filter(fn ($v) => filled($v))->count();
    $adaApapun = $aktif > 0 || filled(request('cari'));
    $reset = $action . (count($abaikan) ? '?' . http_build_query(request()->only($abaikan)) : '');
@endphp
<form method="GET" action="{{ $action }}" role="search"
      x-data="{ terbuka: false }"
      x-on:change="const t = $event.target; if (t.type !== 'search' && t.type !== 'text') $el.requestSubmit()"
      {{ $attributes->merge(['class' => 'flex flex-col gap-2.5 border-b border-slate-100 p-3 sm:flex-row sm:flex-wrap sm:items-center sm:gap-3 sm:p-4']) }}>
    <div class="flex gap-2 sm:contents">
        @if ($cari)
            <div class="relative min-w-0 flex-1 sm:max-w-xs">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"/>
                <input type="search" name="cari" value="{{ request('cari') }}" placeholder="{{ $placeholder }}" class="form-input pl-9" enterkeyhint="search" aria-label="Cari">
            </div>
        @endif
        @if (trim($slot))
            <button type="button" class="btn btn-secondary relative shrink-0 px-3 sm:hidden" x-on:click="terbuka = !terbuka" :aria-expanded="terbuka">
                <x-icon name="sliders" class="size-4"/> Filter
                @if ($aktif)<span class="flex size-5 items-center justify-center rounded-full bg-brand-600 text-[11px] font-semibold text-white">{{ $aktif }}</span>@endif
            </button>
        @endif
    </div>

    @if (trim($slot))
        <div class="grid grid-cols-1 gap-2.5 sm:flex sm:flex-wrap sm:items-center sm:gap-3 sm:[&>*]:w-48" :class="terbuka ? '' : 'max-sm:hidden'" x-cloak>
            {{ $slot }}
        </div>
    @endif

    <div class="flex items-center gap-2 sm:ml-auto">
        <button type="submit" class="sr-only">Cari</button>
        @if ($adaApapun)
            <a href="{{ $reset }}" class="btn btn-ghost btn-sm text-slate-500"><x-icon name="filter-x" class="size-4"/> Hapus filter</a>
        @endif
    </div>
</form>
