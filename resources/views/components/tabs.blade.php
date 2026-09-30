@props(['items' => []])
{{-- Tab tautan. $items: [[label, url, aktif(bool), jumlah|null], ...]. Bergulir menyamping di layar kecil. --}}
<nav {{ $attributes->merge(['class' => '-mx-4 mb-4 overflow-x-auto px-4 sm:mx-0 sm:px-0']) }} aria-label="Tab">
    <div class="inline-flex min-w-max gap-1 rounded-kontrol bg-slate-200/60 p-1">
        @foreach ($items as [$label, $url, $aktif, $jumlah])
            <a href="{{ $url }}" @if ($aktif) aria-current="page" @endif
               @class([
                   'inline-flex h-8 items-center gap-1.5 rounded-lg px-3 text-[13px] font-medium whitespace-nowrap transition-colors duration-150 sentuh:h-9',
                   'bg-white text-slate-900 shadow-kartu' => $aktif,
                   'text-slate-600 hover:bg-white/60 hover:text-slate-900' => ! $aktif,
               ])>
                {{ $label }}
                @if ($jumlah !== null)
                    <span @class(['rounded-full px-1.5 text-[11px] tabular-nums', 'bg-brand-50 text-brand-700' => $aktif, 'bg-slate-200 text-slate-500' => ! $aktif])>{{ $jumlah }}</span>
                @endif
            </a>
        @endforeach
    </div>
</nav>
