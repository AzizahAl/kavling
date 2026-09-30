@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<x-page-header title="Profil Saya" :subtitle="\App\Models\User::PERAN[$user->role] . ($user->agen ? ' · ' . $user->agen->nama_agen . ' (' . $user->agen->kode_agen . ')' : '')"/>

<div class="max-w-2xl">
    <x-card title="Data Akun">
        <form method="POST" action="{{ route('profil.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Nama" name="name" required><x-input name="name" :value="$user->name"/></x-field>
                <x-field label="Email" name="email" required><x-input name="email" type="email" :value="$user->email"/></x-field>
            </div>
            <div class="border-t border-slate-100 pt-4">
                <p class="text-sm font-semibold text-slate-900">Ganti Kata Sandi</p>
                <p class="mb-3 text-xs text-slate-500">Kosongkan bila tidak ingin mengganti. Minimal 8 karakter.</p>
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-field label="Kata Sandi Lama" name="password_lama"><x-input name="password_lama" type="password" autocomplete="current-password"/></x-field>
                    <x-field label="Kata Sandi Baru" name="password"><x-input name="password" type="password" autocomplete="new-password"/></x-field>
                    <x-field label="Ulangi Kata Sandi Baru" name="password_confirmation"><x-input name="password_confirmation" type="password" autocomplete="new-password"/></x-field>
                </div>
            </div>
            <div class="flex justify-end"><button type="submit" class="btn btn-primary">Simpan Profil</button></div>
        </form>
    </x-card>
    @if ($user->terakhir_masuk)
        <p class="mt-3 text-xs text-slate-500">Terakhir masuk {{ tanggal($user->terakhir_masuk, 'j F Y, H:i') }}</p>
    @endif
</div>
@endsection
