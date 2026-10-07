@php
    $user = auth()->user();
    $nama = $user->name ?? 'Tamu';
    $inisial = collect(explode(' ', $nama))->filter()->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
    $peran = $user ? (\App\Models\User::PERAN[$user->role] ?? $user->role) : '';
@endphp
<header class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between gap-3 border-b border-slate-200 bg-white/85 px-4 backdrop-blur-md sm:px-6 lg:px-8">
    <div class="flex items-center gap-2">
        <button type="button" class="btn-icon -ml-2 lg:hidden" x-on:click="sidebar = true" aria-label="Buka menu">
            <x-icon name="menu" class="size-[22px]"/>
        </button>
        <p class="hidden text-sm text-slate-500 sm:block">{{ tanggal(now(), 'l, j F Y') }}</p>
    </div>

    @if ($user)
        <x-menu>
            <x-slot:trigger>
                <button type="button" class="flex items-center gap-3 rounded-kontrol py-1 pr-1 pl-1 transition-colors hover:bg-slate-100 sm:pl-3" aria-haspopup="menu">
                    <span class="hidden text-right leading-tight sm:block">
                        <span class="block text-sm font-medium text-slate-800">{{ $nama }}</span>
                        <span class="block text-xs text-slate-500">{{ $peran }}</span>
                    </span>
                    <span class="flex size-9 items-center justify-center rounded-full bg-brand-600 text-sm font-semibold text-white">{{ $inisial ?: '?' }}</span>
                    <x-icon name="chevron-down" class="size-4 text-slate-400"/>
                </button>
            </x-slot:trigger>
            <div class="border-b border-slate-100 px-3 py-2.5 sm:hidden">
                <p class="text-sm font-medium text-slate-800">{{ $nama }}</p>
                <p class="text-xs text-slate-500">{{ $peran }}</p>
            </div>
            <x-menu-item :href="route('profil')" icon="user">Profil & Kata Sandi</x-menu-item>
            @if (auth()->user()->isAdmin())
                <x-menu-item :href="route('proyek.index')" icon="cog">Pengaturan Proyek</x-menu-item>
            @endif
            <div class="my-1 border-t border-slate-100" role="separator"></div>
            <form method="POST" action="{{ route('logout') }}" data-confirm="Anda perlu login lagi untuk masuk ke aplikasi." data-confirm-title="Keluar dari aplikasi?" data-confirm-ok="Ya, keluar">
                @csrf
                <x-menu-item type="submit" icon="logout" danger>Keluar</x-menu-item>
            </form>
        </x-menu>
    @endif
</header>
