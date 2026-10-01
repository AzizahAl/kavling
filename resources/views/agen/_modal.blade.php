{{-- Popup tambah/ubah agen. Butuh state Alpine "a" (data agen) dan "aksiAgen" (URL form). --}}
<x-modal name="agen" judul-js="a.id ? 'Ubah Agen' : 'Tambah Agen'" :show="old('_form') === 'agen' && $errors->any()">
    <form method="POST" :action="aksiAgen" class="flex min-h-0 flex-1 flex-col">
        @csrf
        <template x-if="a.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="agen"><input type="hidden" name="_id" :value="a.id">
        <x-modal-body>
            <x-field label="Nama Agen" name="nama_agen" required>
                <x-input name="nama_agen" x-model="a.nama_agen" autocomplete="off"/>
            </x-field>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="No. HP" name="no_hp"><x-input name="no_hp" type="tel" inputmode="tel" x-model="a.no_hp"/></x-field>
                <x-field label="Email" name="email"><x-input name="email" type="email" x-model="a.email"/></x-field>
            </div>
            <x-field label="Komisi per Transaksi" name="komisi_nominal" :hint="'Kosong = standar ' . rupiah(\App\Services\Pengaturan::get('komisi_nominal', 0)) . ' (Pengaturan)'">
                <div x-effect="$dispatch('set-money', { name: 'komisi_nominal', value: a.komisi_nominal ?? '' })"
                     x-on:money-changed="if ($event.detail.name === 'komisi_nominal') a.komisi_nominal = $event.detail.value || ''">
                    <x-money name="komisi_nominal"/>
                </div>
            </x-field>
            <label class="flex items-center gap-2.5 text-sm text-slate-700">
                <input type="hidden" name="aktif" value="0">
                <input type="checkbox" name="aktif" value="1" x-model="a.aktif" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"> Agen aktif
            </label>
            <x-field label="Catatan" name="catatan"><x-textarea name="catatan" rows="2" x-model="a.catatan"/></x-field>
        </x-modal-body>
        <x-modal-footer>
            <button type="submit" class="btn btn-primary" x-text="a.id ? 'Simpan' : 'Tambah'"></button>
        </x-modal-footer>
    </form>
</x-modal>
