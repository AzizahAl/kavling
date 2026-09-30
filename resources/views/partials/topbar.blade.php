@php
    $user = auth()->user();
    $nama = $user->name ?? 'Tamu';
    $inisial = collect(explode(' ', $nama))->filter()->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
    $peran = $user ? (($user->role ?? null) === 'agen' ? 'Agen Marketing' : 'Administrator') : 'Belum masuk';
@endphp
<header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
    <div class="flex items-center gap-2">
        <button type="button" class="btn-icon -ml-2 lg:hidden" x-on:click="sidebar = true" aria-label="Buka menu">
            <x-icon name="menu" class="h-6 w-6"/>
        </button>
        <p class="hidden text-sm text-slate-500 sm:block">{{ tanggal(now(), 'l, j F Y') }}</p>
    </div>

    <div class="flex items-center gap-3">
        <div class="text-right leading-tight">
            <p class="text-sm font-semibold text-slate-800">{{ $nama }}</p>
            <p class="text-xs text-slate-500">{{ $peran }}</p>
        </div>
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-forest-800 text-sm font-semibold text-gold-400">{{ $inisial ?: '?' }}</div>
    </div>
</header>
