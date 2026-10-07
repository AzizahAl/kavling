@extends('layouts.app')
@section('title', 'Profil')

@php
    $inisial = collect(preg_split('/\s+/', trim($user->name)))->filter()->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
    $peran = \App\Models\User::PERAN[$user->role] ?? $user->role;
    $info = array_filter([
        ['user', 'Email / Nama Pengguna', $user->email],
        $user->agen ? ['users', 'Data Agen', $user->agen->nama_agen . ' · ' . $user->agen->kode_agen] : null,
        $user->agen?->no_hp ? ['phone', 'No. HP', $user->agen->no_hp] : null,
        ['clock', 'Terakhir Masuk', $user->terakhir_masuk ? tanggal($user->terakhir_masuk, 'j F Y, H:i') : 'Belum tercatat'],
        ['calendar', 'Akun Dibuat', $user->created_at ? tanggal($user->created_at, 'j F Y') : '—'],
    ]);
    $adaErrorSandi = $errors->hasAny(['password_lama', 'password']);
@endphp

@section('content')
<x-page-header title="Profil" subtitle="Kelola identitas akun dan keamanan login Anda."/>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    {{-- Kartu identitas --}}
    <aside class="lg:col-span-1">
        <div class="overflow-hidden rounded-kartu border border-slate-200 bg-white shadow-kartu lg:sticky lg:top-24">
            <div class="h-20 bg-linear-to-br from-brand-700 via-brand-600 to-brand-500"></div>
            <div class="-mt-10 px-5 pb-5">
                <div class="flex size-20 items-center justify-center rounded-2xl bg-white p-1 shadow-kartu">
                    <span class="flex size-full items-center justify-center rounded-xl bg-brand-50 text-2xl font-semibold text-brand-700">{{ $inisial ?: '?' }}</span>
                </div>
                <h2 class="mt-3 truncate text-lg font-semibold text-slate-900">{{ $user->name }}</h2>
                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                    <x-badge :status="$user->role" :label="$peran" :warna="$user->isAdmin() ? 'merek' : 'abu'" :ikon="$user->isAdmin() ? 'shield' : 'user'"/>
                    <x-badge :status="$user->aktif ? 'aktif' : 'nonaktif'"/>
                </div>
                <dl class="mt-5 space-y-3.5 border-t border-slate-100 pt-4 text-sm">
                    @foreach ($info as [$ikon, $label, $nilai])
                        <div class="flex gap-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"><x-icon :name="$ikon" class="size-4"/></span>
                            <div class="min-w-0">
                                <dt class="text-xs text-slate-500">{{ $label }}</dt>
                                <dd class="truncate font-medium text-slate-800" title="{{ $nilai }}">{{ $nilai }}</dd>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </aside>

    {{-- Form --}}
    <form method="POST" action="{{ route('profil.update') }}" class="min-w-0 space-y-5 lg:col-span-2" novalidate>
        @csrf @method('PUT')

        <section class="rounded-kartu border border-slate-200 bg-white shadow-kartu">
            <header class="flex items-start gap-3 border-b border-slate-100 px-5 py-4">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700"><x-icon name="user" class="size-[18px]"/></span>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Data Akun</h3>
                    <p class="text-xs text-slate-500">Nama tampil di aplikasi dan dipakai untuk masuk.</p>
                </div>
            </header>
            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <x-field label="Nama" name="name" required><x-input name="name" :value="$user->name" autocomplete="name"/></x-field>
                <x-field label="Email atau Nama Pengguna" name="email" required hint="Dipakai saat masuk.">
                    <x-input name="email" :value="$user->email" autocomplete="username" autocapitalize="none" spellcheck="false"/>
                </x-field>
            </div>
        </section>

        <section class="rounded-kartu border border-slate-200 bg-white shadow-kartu" x-data="{ lihat: false, buka: @js($adaErrorSandi) }">
            <header class="flex items-start gap-3 border-b border-slate-100 px-5 py-4">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><x-icon name="key" class="size-[18px]"/></span>
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-semibold text-slate-900">Keamanan</h3>
                    <p class="text-xs text-slate-500">Ganti kata sandi secara berkala. Minimal 8 karakter.</p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm shrink-0" x-show="! buka" x-on:click="buka = true; $nextTick(() => $refs.lama.focus())">Ganti Kata Sandi</button>
            </header>
            <div x-show="buka" x-cloak x-collapse>
                <div class="space-y-4 p-5">
                    <x-field label="Kata Sandi Lama" name="password_lama">
                        <x-input name="password_lama" type="password" x-ref="lama" x-bind:type="lihat ? 'text' : 'password'" autocomplete="current-password"/>
                    </x-field>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-field label="Kata Sandi Baru" name="password">
                            <x-input name="password" type="password" x-bind:type="lihat ? 'text' : 'password'" autocomplete="new-password"/>
                        </x-field>
                        <x-field label="Ulangi Kata Sandi Baru" name="password_confirmation">
                            <x-input name="password_confirmation" type="password" x-bind:type="lihat ? 'text' : 'password'" autocomplete="new-password"/>
                        </x-field>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" x-model="lihat" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"> Tampilkan kata sandi
                        </label>
                        <button type="button" class="btn btn-ghost btn-sm text-slate-500" x-on:click="buka = false; $el.closest('section').querySelectorAll('input[type=password],input[type=text]').forEach(i => i.value = '')">Batal ganti</button>
                    </div>
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
            <x-button variant="secondary" :href="route('beranda')">Kembali</x-button>
            <x-button type="submit" icon="check">Simpan Perubahan</x-button>
        </div>
    </form>
</div>
@endsection
