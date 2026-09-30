{{-- Aksi satu item RAB. Butuh $r, $dataRab, $dataRealisasi. --}}
<div class="flex items-center justify-end gap-1">
    <x-button variant="secondary" size="sm" icon="banknotes" x-on:click="bukaRealisasi({{ Js::from($dataRealisasi($r)) }})">Catat Realisasi</x-button>
    <x-icon-button icon="pencil" label="Ubah anggaran" x-on:click="bukaForm({{ Js::from($dataRab($r)) }})"/>
    <x-delete-button :action="route('rab.destroy', $r)" title="Hapus item RAB?" message="Item yang sudah punya realisasi tidak bisa dihapus."/>
</div>
