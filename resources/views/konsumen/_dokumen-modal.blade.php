{{-- Popup ubah status dokumen (SPK, PPJB, AJB). Butuh state Alpine "dok" berisi ChecklistLegal::dataForm(). --}}
@php $opsiDok = ['belum' => 'Belum', 'proses' => 'Proses', 'selesai' => 'Selesai']; @endphp
<x-modal name="dokumen" judul-js="'Dokumen · Kavling ' + (dok.kavling || '')" :show="(bool) old('_dok_id') && $errors->any()">
    <form method="POST" :action="'{{ url('data-konsumen/dokumen') }}/' + dok.id" class="flex min-h-0 flex-1 flex-col">
        @csrf @method('PUT')
        <input type="hidden" name="_dok_id" :value="dok.id"><input type="hidden" name="_dok_kavling" :value="dok.kavling">
        <x-modal-body>
            @foreach (\App\Models\ChecklistLegal::ITEM as $item => $label)
                <div class="grid grid-cols-2 gap-3">
                    <x-field :label="$label" :name="$item . '_status'" required>
                        <x-select :name="$item . '_status'" :options="$opsiDok" x-model="dok.{{ $item }}_status"/>
                    </x-field>
                    <x-field label="Tanggal" :name="$item . '_tanggal'">
                        <x-input type="date" :name="$item . '_tanggal'" x-model="dok.{{ $item }}_tanggal" :max="now()->toDateString()"/>
                    </x-field>
                </div>
            @endforeach
            <p x-show="dok.spk_alasan" x-cloak class="flex gap-2 rounded-kontrol bg-amber-50 px-3 py-2 text-xs text-amber-900"><x-icon name="warning" class="size-4 text-amber-500"/><span>SPK belum bisa diproses: <span x-text="dok.spk_alasan"></span></span></p>
            <p class="flex gap-2 rounded-kontrol bg-brand-50 px-3 py-2 text-xs text-brand-800"><x-icon name="info" class="size-4"/>Perubahan status tercatat di riwayat transaksi. Status kavling & tahap harga diperbarui otomatis.</p>
            <x-field label="Catatan" name="catatan"><x-input name="catatan" x-model="dok.catatan"/></x-field>
            <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-3" x-show="dok.url_ppjb">
                <span class="w-full text-xs text-slate-500">Cetak formulir</span>
                <a :href="dok.url_spk" class="btn btn-secondary btn-sm" x-show="dok.url_spk"><x-icon name="printer" class="size-4"/> SPK</a>
                <span x-show="! dok.url_spk" class="btn btn-secondary btn-sm pointer-events-none opacity-50" :title="dok.spk_alasan"><x-icon name="printer" class="size-4"/> SPK</span>
                <a :href="dok.url_ppjb" class="btn btn-secondary btn-sm"><x-icon name="printer" class="size-4"/> PPJB</a>
            </div>
        </x-modal-body>
        <x-modal-footer><x-button type="submit">Simpan</x-button></x-modal-footer>
    </form>
</x-modal>
