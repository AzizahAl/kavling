{{-- Navigasi halaman berbahasa Indonesia. Dipakai sebagai tampilan bawaan paginator (AppServiceProvider). --}}
@if ($paginator->hasPages())
    <nav class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-4 py-3 sm:flex-row" aria-label="Navigasi halaman">
        <p class="text-xs text-slate-500">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>
        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="btn-icon cursor-not-allowed opacity-40" aria-disabled="true"><x-icon name="chevron-left" class="size-4"/></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-icon" x-data x-tip="'Sebelumnya'"><x-icon name="chevron-left" class="size-4"/></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-1 text-sm text-slate-400">…</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="flex size-9 items-center justify-center rounded-kontrol bg-brand-600 text-sm font-medium text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="hidden size-9 items-center justify-center rounded-kontrol text-sm text-slate-600 hover:bg-slate-100 sm:flex">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-icon" x-data x-tip="'Berikutnya'"><x-icon name="chevron-right" class="size-4"/></a>
            @else
                <span class="btn-icon cursor-not-allowed opacity-40" aria-disabled="true"><x-icon name="chevron-right" class="size-4"/></span>
            @endif
        </div>
    </nav>
@endif
