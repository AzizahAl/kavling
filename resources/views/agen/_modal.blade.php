{{-- Popup tambah/ubah agen beserta akun login. Butuh state Alpine "a" (data agen) dan "aksiAgen" (URL form). --}}
<x-modal name="agen" judul-js="a.id ? 'Ubah Agen' : 'Tambah Agen'" max-width="xl" :show="old('_form') === 'agen' && $errors->any()">
    <form method="POST" :action="aksiAgen" class="flex min-h-0 flex-1 flex-col" novalidate>
        @csrf
        <template x-if="a.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="agen"><input type="hidden" name="_id" :value="a.id">
        <input type="hidden" name="_kode" :value="a.kode"><input type="hidden" name="_punya_akun" :value="a.punya_akun ? 1 : ''">
        <x-modal-body>
            <fieldset class="space-y-4">
                <legend class="mb-3 text-sm font-semibold text-slate-900">Data Agen</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Kode Agen" name="kode">
                        <x-input name="kode" x-model="a.kode" readonly class="bg-slate-50 font-medium tabular-nums" tabindex="-1"/>
                    </x-field>
                    <x-field label="Nama Agen" name="nama_agen" required>
                        <x-input name="nama_agen" x-model="a.nama_agen" autocomplete="off"/>
                    </x-field>
                    <x-field label="Nomor HP" name="no_hp" required>
                        <x-input name="no_hp" type="tel" inputmode="tel" x-model="a.no_hp" placeholder="08xx"/>
                    </x-field>
                    <x-field label="Komisi per Transaksi" name="komisi_nominal" :hint="'Kosong = standar ' . rupiah(\App\Services\Pengaturan::get('komisi_nominal', 0))">
                        <div x-effect="$dispatch('set-money', { name: 'komisi_nominal', value: a.komisi_nominal ?? '' })"
                             x-on:money-changed="if ($event.detail.name === 'komisi_nominal') a.komisi_nominal = $event.detail.value || ''">
                            <x-money name="komisi_nominal"/>
                        </div>
                    </x-field>
                </div>
            </fieldset>

            <fieldset class="space-y-4 border-t border-slate-100 pt-4">
                <legend class="sr-only">Akun Login</legend>
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-slate-900">Akun Login</p>
                    <span x-show="a.id && ! a.punya_akun" class="text-xs text-slate-500">Belum punya akun. Isi untuk membuatkan.</span>
                </div>
                <x-field name="login">
                    <label for="login" class="form-label" :class="{ wajib: ! a.id || a.punya_akun }">Email atau Nama Pengguna</label>
                    <x-input name="login" x-model="a.login" autocomplete="off" autocapitalize="none" spellcheck="false" placeholder="nama@email.com atau namapengguna"/>
                </x-field>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field name="password">
                        <label for="password" class="form-label" :class="{ wajib: ! a.id }">Kata Sandi</label>
                        <x-input name="password" type="password" autocomplete="new-password"/>
                        <p class="form-hint" x-text="a.punya_akun ? 'Kosongkan bila tidak diganti.' : 'Minimal 8 karakter.'" @error('password') hidden @enderror></p>
                    </x-field>
                    <x-field label="Konfirmasi Kata Sandi" name="password_confirmation">
                        <x-input name="password_confirmation" type="password" autocomplete="new-password"/>
                    </x-field>
                </div>
                <label class="flex items-start gap-2.5 text-sm text-slate-700">
                    <input type="hidden" name="aktif" value="0">
                    <input type="checkbox" name="aktif" value="1" x-model="a.aktif" class="mt-0.5 size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <span>Aktif<span class="block text-xs text-slate-500">Nonaktif: tidak bisa login dan tidak muncul di pilihan agen pada lead.</span></span>
                </label>
            </fieldset>
        </x-modal-body>
        <x-modal-footer>
            <button type="submit" class="btn btn-primary" x-text="a.id ? 'Simpan' : 'Tambah Agen'"></button>
        </x-modal-footer>
    </form>
</x-modal>
