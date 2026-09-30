@extends('layouts.app')
@section('title', 'Profil')

@section('content')
<x-page-header title="Profil" :subtitle="\App\Models\User::PERAN[$user->role] . ($user->agen ? ' · ' . $user->agen->nama_agen . ' (' . $user->agen->kode_agen . ')' : '')"/>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('profil.update') }}">
        @csrf @method('PUT')
        <x-card title="Data Akun">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Nama" name="name" required><x-input name="name" :value="$user->name" autocomplete="name"/></x-field>
                <x-field label="Email" name="email" required><x-input name="email" type="email" :value="$user->email" autocomplete="email"/></x-field>
            </div>
        </x-card>

        <x-card title="Ganti Kata Sandi" subtitle="Kosongkan bila tidak diganti. Min. 8 karakter." class="mt-5">
            <div class="grid gap-4 sm:grid-cols-3">
                <x-field label="Sandi Lama" name="password_lama"><x-input name="password_lama" type="password" autocomplete="current-password"/></x-field>
                <x-field label="Sandi Baru" name="password"><x-input name="password" type="password" autocomplete="new-password"/></x-field>
                <x-field label="Ulangi Sandi Baru" name="password_confirmation"><x-input name="password_confirmation" type="password" autocomplete="new-password"/></x-field>
            </div>
        </x-card>

        <div class="mt-5 flex flex-col-reverse items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
            @if ($user->terakhir_masuk)
                <p class="text-xs text-slate-500">Terakhir masuk {{ tanggal($user->terakhir_masuk, 'j F Y, H:i') }}</p>
            @else
                <span></span>
            @endif
            <x-button type="submit" icon="check">Simpan</x-button>
        </div>
    </form>
</div>
@endsection
