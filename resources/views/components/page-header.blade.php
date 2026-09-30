@props(['title', 'subtitle' => null, 'breadcrumbs' => [], 'back' => null])
{{-- Judul halaman seragam: remah roti, judul, keterangan singkat; aksi di kanan (di HP turun ke bawah). --}}
<div {{ $attributes->merge(['class' => 'mb-5 flex flex-col gap-3 sm:mb-6 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if ($breadcrumbs)
            <nav class="mb-1 flex flex-wrap items-center gap-1 text-xs text-slate-500" aria-label="Breadcrumb">
                @foreach ($breadcrumbs as $label => $url)
                    @if (! $loop->first)<x-icon name="chevron-right" class="size-3 text-slate-400"/>@endif
                    @if ($url)<a href="{{ $url }}" class="hover:text-brand-700">{{ $label }}</a>@else<span>{{ $label }}</span>@endif
                @endforeach
            </nav>
        @endif
        <div class="flex items-center gap-1.5">
            @if ($back)
                <x-icon-button :href="$back" icon="arrow-left" label="Kembali" class="-ml-2"/>
            @endif
            <h1 class="truncate text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl">{{ $title }}</h1>
        </div>
        @if ($subtitle)
            <p class="mt-0.5 text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2 [&>*]:grow sm:[&>*]:grow-0">{{ $actions }}</div>
    @endisset
</div>
