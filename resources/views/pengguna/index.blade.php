@extends('layouts.app')
@section('title', 'Pengguna')

@php
    $kosong = ['id' => null, 'name' => '', 'email' => '', 'role' => 'agen', 'agen_id' => '', 'aktif' => true];
    $awal = old('_form') === 'user' ? array_merge($kosong, request()->old(), ['id' => old('_id'), 'aktif' => (bool) old('aktif')]) : $kosong;
@endphp

@section('content')
<div x-data="{
        u: @js($awal), kosong: @js($kosong),
        get action() { return this.u.id ? '{{ url('pengguna') }}/' + this.u.id : '{{ route('pengguna.store') }}' },
        buka(d) { this.u = { ...d }; this.$dispatch('open-modal', 'user') },
     }">

<x-page-header title="Pengguna & Hak Akses" subtitle="Admin mengakses semua menu. Agen hanya mengelola lead dan melihat data miliknya sendiri."
               :breadcrumbs="['Data Master' => null, 'Pengguna' => null]">
    <x-slot:actions>
        <button type="button" class="btn btn-primary" x-on:click="buka(kosong)"><x-icon name="plus" class="h-4 w-4"/> Tambah Akun</button>
    </x-slot:actions>
</x-page-header>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Agen</th><th>Terakhir Masuk</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
            <tbody>
                @foreach ($users as $u)
                    <tr>
                        <td class="font-medium text-slate-900">{{ $u->name }} @if ($u->is(auth()->user()))<span class="text-xs text-slate-400">(Anda)</span>@endif</td>
                        <td>{{ $u->email }}</td>
                        <td><x-badge :status="$u->role" :label="\App\Models\User::PERAN[$u->role]" :warna="$u->isAdmin() ? 'gelap' : 'emas'"/></td>
                        <td>{{ $u->agen ? $u->agen->nama_agen . ' (' . $u->agen->kode_agen . ')' : '—' }}</td>
                        <td class="text-slate-500">{{ $u->terakhir_masuk ? tanggal($u->terakhir_masuk, 'j M Y H:i') : 'Belum pernah' }}</td>
                        <td><x-badge :status="$u->aktif ? 'aktif' : 'batal'" :label="$u->aktif ? 'Aktif' : 'Nonaktif'"/></td>
                        <td>
                            <div class="flex justify-end gap-1">
                                <button type="button" class="btn-icon" title="Ubah" x-on:click="buka(@js(['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role, 'agen_id' => (string) $u->agen_id, 'aktif' => $u->aktif]))"><x-icon name="pencil" class="h-[18px] w-[18px]"/></button>
                                @unless ($u->is(auth()->user()))
                                    <x-delete-button :action="route('pengguna.destroy', $u)" title="Hapus akun {{ $u->email }}?" message="Akun tidak bisa lagi dipakai untuk masuk. Data yang pernah diinputnya tetap tersimpan."/>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<x-modal name="user" max-width="lg" :show="old('_form') === 'user' && $errors->any()">
    <form method="POST" :action="action">
        @csrf
        <template x-if="u.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="user"><input type="hidden" name="_id" :value="u.id">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-900" x-text="u.id ? 'Ubah Akun' : 'Tambah Akun'"></h3>
            <button type="button" class="btn-icon -mr-2" x-on:click="$dispatch('close-modal', 'user')"><x-icon name="x"/></button>
        </div>
        <div class="grid gap-4 p-5 sm:grid-cols-2">
            <x-field label="Nama" name="name" required><input type="text" name="name" x-model="u.name" class="form-input"></x-field>
            <x-field label="Email" name="email" required><input type="email" name="email" x-model="u.email" class="form-input"></x-field>
            <x-field label="Peran" name="role" required>
                <select name="role" x-model="u.role" class="form-input">
                    @foreach (\App\Models\User::PERAN as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                </select>
            </x-field>
            <x-field label="Data Agen" name="agen_id" x-show="u.role === 'agen'">
                <select name="agen_id" x-model="u.agen_id" class="form-input" :disabled="u.role !== 'agen'">
                    <option value="">— Pilih agen —</option>
                    @foreach ($semuaAgen as $id => $nama)<option value="{{ $id }}">{{ $nama }}</option>@endforeach
                </select>
            </x-field>
            <x-field label="Kata Sandi" name="password" class="sm:col-span-2" hint="Minimal 8 karakter. Saat mengubah akun, kosongkan bila tidak diganti.">
                <input type="password" name="password" autocomplete="new-password" class="form-input">
            </x-field>
            <label class="flex items-center gap-2 text-sm text-slate-700 sm:col-span-2">
                <input type="hidden" name="aktif" value="0">
                <input type="checkbox" name="aktif" value="1" x-model="u.aktif" class="h-4 w-4 rounded border-slate-300 text-forest-600 focus:ring-forest-500"> Akun aktif (boleh masuk)
            </label>
        </div>
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'user')">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</x-modal>
</div>
@endsection
