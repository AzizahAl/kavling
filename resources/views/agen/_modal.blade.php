{{-- Modal tambah/ubah agen. Butuh state Alpine "a" (data agen) dan "aksiAgen" (URL form). --}}
<x-modal name="agen" max-width="lg" :show="old('_form') === 'agen' && $errors->any()">
    <form method="POST" :action="aksiAgen">
        @csrf
        <template x-if="a.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="agen"><input type="hidden" name="_id" :value="a.id">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-900" x-text="a.id ? 'Ubah Agen' : 'Tambah Agen'"></h3>
            <button type="button" class="btn-icon -mr-2" x-on:click="$dispatch('close-modal', 'agen')"><x-icon name="x"/></button>
        </div>
        <div class="grid gap-4 p-5 sm:grid-cols-2">
            <x-field label="Nama Agen" name="nama_agen" required class="sm:col-span-2">
                <input type="text" name="nama_agen" id="nama_agen" x-model="a.nama_agen" class="form-input">
            </x-field>
            <x-field label="No. HP" name="no_hp">
                <input type="tel" name="no_hp" id="no_hp" x-model="a.no_hp" class="form-input">
            </x-field>
            <x-field label="Email" name="email">
                <input type="email" name="email" id="email" x-model="a.email" class="form-input">
            </x-field>
            <x-field label="Komisi (%)" name="komisi_persen"
                     hint="Kosongkan untuk memakai komisi bawaan ({{ \App\Services\Pengaturan::get('komisi_default_persen') !== null ? persen(\App\Services\Pengaturan::get('komisi_default_persen'), false, 2) : 'belum diatur' }})">
                <div class="relative">
                    <input type="number" step="0.01" min="0" max="100" name="komisi_persen" id="komisi_persen" x-model="a.komisi_persen" class="form-input pr-9">
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-slate-400">%</span>
                </div>
            </x-field>
            <x-field label="Status" name="aktif">
                <input type="hidden" name="aktif" value="0">
                <label class="flex h-[42px] items-center gap-2 text-sm">
                    <input type="checkbox" name="aktif" value="1" x-model="a.aktif" class="h-4 w-4 rounded border-slate-300 text-forest-600 focus:ring-forest-500"> Agen aktif
                </label>
            </x-field>
            <x-field label="Catatan" name="catatan" class="sm:col-span-2">
                <textarea name="catatan" id="catatan_agen" rows="2" x-model="a.catatan" class="form-input"></textarea>
            </x-field>
        </div>
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'agen')">Batal</button>
            <button type="submit" class="btn btn-primary" x-text="a.id ? 'Simpan Perubahan' : 'Tambah Agen'"></button>
        </div>
    </form>
</x-modal>
