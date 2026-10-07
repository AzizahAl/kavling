{{-- Navigasi bagian menu Agen & Marketing (admin): data agen, lead harian, rekap lead. --}}
@if (auth()->user()?->isAdmin())
    <nav class="-mx-4 mb-4 overflow-x-auto border-b border-slate-200 px-4 sm:mx-0 sm:px-0" aria-label="Bagian Agen & Marketing">
        <div class="flex min-w-max gap-5">
            @foreach ([['Data Agen', 'agen.index', 'users', 'agen.index'], ['Lead Harian', 'lead.index', 'funnel', 'lead.index'], ['Rekap Lead', 'lead.rekap', 'chart', 'lead.rekap']] as [$label, $route, $ikon, $pola])
                @php $aktif = request()->routeIs($pola); @endphp
                <a href="{{ route($route) }}" @if ($aktif) aria-current="page" @endif
                   @class(['-mb-px inline-flex items-center gap-1.5 border-b-2 px-0.5 pb-2.5 text-sm font-medium whitespace-nowrap transition-colors duration-150',
                           'border-brand-600 text-brand-700' => $aktif, 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' => ! $aktif])>
                    <x-icon :name="$ikon" class="size-4"/> {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>
@endif
