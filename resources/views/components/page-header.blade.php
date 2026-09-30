@props(['title', 'subtitle' => null, 'breadcrumbs' => [], 'back' => null])
{{-- Judul halaman. $breadcrumbs = ['Label' => url|null, ...]; slot "actions" untuk tombol di kanan. --}}
<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if ($breadcrumbs)
            <nav class="mb-1.5 flex flex-wrap items-center gap-1 text-xs text-slate-500" aria-label="Breadcrumb">
                @foreach ($breadcrumbs as $label => $url)
                    @if (! $loop->first)<x-icon name="chevron-right" class="h-3 w-3 text-slate-400"/>@endif
                    @if ($url)<a href="{{ $url }}" class="hover:text-forest-600">{{ $label }}</a>@else<span class="text-slate-700">{{ $label }}</span>@endif
                @endforeach
            </nav>
        @endif
        <div class="flex items-center gap-2">
            @if ($back)
                <a href="{{ $back }}" class="btn-icon -ml-2" title="Kembali"><x-icon name="arrow-left"/></a>
            @endif
            <h1 class="truncate text-2xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
        </div>
        @if ($subtitle)
            <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
