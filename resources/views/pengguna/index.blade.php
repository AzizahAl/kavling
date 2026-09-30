@extends('layouts.app')
@section('title', 'Pengguna')

@php
    $kosong = ['id' => null, 'name' => '', 'email' => '', 'role' => 'agen', 'agen_id' => '', 'aktif' => true];
    $awal = old('_form') === 'user' ? array_merge($kosong, request()->old(), ['id' => old('_id'), 'aktif' => (bool) old('aktif')]) : $kosong;
@endphp

@section('content')
<div x-data="{
        u: @js($awal), kosong: @js($kosong), q: '', peran: '', status: '',
        get action() { return this.u.id ? '{{ url('pengguna') }}/' + this.u.id : '{{ route('pengguna.store') }}' },
        buka(d) { this.u = { ...d }; this.$dispatch('open-modal', 'user') },
        tampil(teks, role, aktif) {
            return (! this.q || teks.toLowerCase().includes(this.q.toLowerCase()))
                && (! this.peran || this.peran === role) && (! this.status || this.status === (aktif ? 'aktif' : 'nonaktif'));
        },
     }">

<x-page-header title="Pengguna" subtitle="Admin: semua menu. Agen: lead & data miliknya." :breadcrumbs="['Data Master' => null, 'Pengguna' => null]">
    <x-slot:actions>
        <x-button icon="plus" x-on:click="buka(kosong)">Tambah Akun</x-button>
    </x-slot:actions>
</x-page-header>

<div class="card">
    <div class="flex flex-col gap-2.5 border-b border-slate-100 p-3 sm:flex-row sm:flex-wrap sm:items-center sm:gap-3 sm:p-4">
        <div class="relative min-w-0 flex-1 sm:max-w-xs">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"/>
            <input type="search" x-model="q" placeholder="Cari nama atau email…" class="form-input pl-9" aria-label="Cari">
        </div>
        <x-select name="f_peran" :options="\App\Models\User::PERAN" placeholder="Semua peran" x-model="peran" class="sm:w-48"/>
        <x-select name="f_status" :options="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']" placeholder="Semua status" x-model="status" class="sm:w-44"/>
        <button type="button" class="btn btn-ghost btn-sm text-slate-500 sm:ml-auto" x-show="q || peran || status" x-cloak x-on:click="q = ''; peran = ''; status = ''">
            <x-icon name="filter-x" class="size-4"/> Hapus filter
        </button>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Agen</th><th>Terakhir Masuk</th><th>Status</th><th class="w-px"></th></tr></thead>
            <tbody>
                @foreach ($users as $u)
                    <tr x-show="tampil({{ Js::from($u->name . ' ' . $u->email) }}, '{{ $u->role }}', {{ $u->aktif ? 'true' : 'false' }})">
                        <td class="font-medium text-slate-900">{{ $u->name }} @if ($u->is(auth()->user()))<span class="text-xs font-normal text-slate-400">(Anda)</span>@endif</td>
                        <td>{{ $u->email }}</td>
                        <td><x-badge :status="$u->role" :label="\App\Models\User::PERAN[$u->role]" :warna="$u->isAdmin() ? 'merek' : 'abu'"/></td>
                        <td>{{ $u->agen ? $u->agen->nama_agen . ' (' . $u->agen->kode_agen . ')' : '—' }}</td>
                        <td class="text-slate-500">{{ $u->terakhir_masuk ? tanggal($u->terakhir_masuk, 'j M Y, H:i') : 'Belum pernah' }}</td>
                        <td><x-badge :status="$u->aktif ? 'aktif' : 'nonaktif'"/></td>
                        <td>
                            <div class="flex justify-end gap-0.5">
                                <x-icon-button icon="pencil" label="Ubah" x-on:click="buka({{ Js::from(['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role, 'agen_id' => (string) $u->agen_id, 'aktif' => $u->aktif]) }})"/>
                                @unless ($u->is(auth()->user()))
                                    <x-delete-button :action="route('pengguna.destroy', $u)" :title="'Hapus akun ' . $u->email . '?'" message="Akun tidak bisa dipakai masuk lagi. Data yang pernah diinput tetap tersimpan."/>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<x-modal name="user" judul-js="u.id ? 'Ubah Akun' : 'Tambah Akun'" :show="old('_form') === 'user' && $errors->any()">
    <form method="POST" :action="action" class="flex min-h-0 flex-1 flex-col">
        @csrf
        <template x-if="u.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="user"><input type="hidden" name="_id" :value="u.id">
        <x-modal-body>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Nama" name="name" required><x-input name="name" x-model="u.name"/></x-field>
                <x-field label="Email" name="email" required><x-input name="email" type="email" x-model="u.email"/></x-field>
                <x-field label="Peran" name="role" required><x-select name="role" :options="\App\Models\User::PERAN" x-model="u.role"/></x-field>
                <x-field label="Data Agen" name="agen_id" required x-show="u.role === 'agen'">
                    <x-select name="agen_id" :options="$semuaAgen" placeholder="Pilih agen" x-model="u.agen_id" x-bind:disabled="u.role !== 'agen'"/>
                </x-field>
            </div>
            <x-field label="Kata Sandi" name="password" hint="Min. 8 karakter. Saat mengubah, kosongkan bila tidak diganti.">
                <x-input name="password" type="password" autocomplete="new-password"/>
            </x-field>
            <label class="flex items-center gap-2.5 text-sm text-slate-700">
                <input type="hidden" name="aktif" value="0">
                <input type="checkbox" name="aktif" value="1" x-model="u.aktif" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"> Akun aktif (boleh masuk)
            </label>
        </x-modal-body>
        <x-modal-footer><x-button type="submit">Simpan</x-button></x-modal-footer>
    </form>
</x-modal>
</div>
@endsection
