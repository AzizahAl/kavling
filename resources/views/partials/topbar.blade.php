@php
    $user = auth()->user();
    $nama = $user->name ?? 'Tamu';
    $inisial = collect(explode(' ', $nama))->filter()->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
    $peran = $user ? (\App\Models\User::PERAN[$user->role] ?? $user->role) : 'Belum masuk';
@endphp
<header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
    <div class="flex items-center gap-2">
        <button type="button" class="btn-icon -ml-2 lg:hidden" x-on:click="sidebar = true" aria-label="Buka menu">
            <x-icon name="menu" class="h-6 w-6"/>
        </button>
        <p class="hidden text-sm text-slate-500 sm:block">{{ tanggal(now(), 'l, j F Y') }}</p>
    </div>

    @if ($user)
        <div class="relative" x-data="{ buka: false }" x-on:click.outside="buka = false" x-on:keydown.escape.window="buka = false">
            <button type="button" class="flex items-center gap-3 rounded-lg p-1 hover:bg-slate-100" x-on:click="buka = !buka" :aria-expanded="buka">
                <span class="hidden text-right leading-tight sm:block">
                    <span class="block text-sm font-semibold text-slate-800">{{ $nama }}</span>
                    <span class="block text-xs text-slate-500">{{ $peran }}</span>
                </span>
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-forest-800 text-sm font-semibold text-gold-400">{{ $inisial ?: '?' }}</span>
                <x-icon name="chevron-down" class="h-4 w-4 text-slate-400"/>
            </button>
            <div x-show="buka" x-transition.origin.top.right x-cloak class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                <div class="border-b border-slate-100 px-4 py-3 sm:hidden">
                    <p class="text-sm font-semibold text-slate-800">{{ $nama }}</p>
                    <p class="text-xs text-slate-500">{{ $peran }}</p>
                </div>
                <a href="{{ route('profil') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="user" class="h-4 w-4"/> Profil & Kata Sandi</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50"><x-icon name="logout" class="h-4 w-4"/> Keluar</button>
                </form>
            </div>
        </div>
    @endif
</header>
