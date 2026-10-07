{{-- Aksi satu item RAB ($r): centang (aktif bila anggaran & realisasi terisi → popup tanggal → masuk kas / batal), ubah, hapus. Ubah & hapus dikunci setelah dicentang. --}}
<div class="flex items-center justify-end gap-0.5">
    @if ($r->isDicentang())
        <form method="POST" action="{{ route('rab.batal-centang', $r) }}" class="inline-flex"
              data-confirm="Catatan pengeluaran di Kas Proyek untuk &quot;{{ $r->uraian }}&quot; akan dihapus dan baris ini bisa diubah lagi."
              data-confirm-title="Batalkan centang?" data-confirm-ok="Batalkan Centang">
            @csrf @method('DELETE')
            <button type="submit" class="btn-icon bg-green-50 text-green-700 hover:bg-green-100" x-data x-tip="'Sudah masuk kas. Klik untuk membatalkan centang'" aria-label="Batalkan centang">
                <x-icon name="check-circle" class="size-[18px]"/>
            </button>
        </form>
        <span x-data x-tip="'Terkunci: sudah masuk kas'" tabindex="0"><x-icon-button icon="pencil" label="Ubah" disabled class="pointer-events-none opacity-40"/></span>
        <span x-data x-tip="'Terkunci: sudah masuk kas'" tabindex="0"><x-icon-button icon="trash" label="Hapus" variant="danger" disabled class="pointer-events-none opacity-40"/></span>
    @else
        @if ($r->anggaran !== null && $r->realisasi > 0)
            <x-icon-button icon="check" label="Centang: catat ke Kas Proyek" x-on:click="bukaCentang({{ Js::from($dataCentang($r)) }})"/>
        @else
            <span x-data x-tip="@js($r->anggaran === null ? 'Isi anggaran dan realisasi dulu untuk mencentang' : 'Isi realisasi dulu untuk mencentang')" tabindex="0">
                <x-icon-button icon="check" label="Centang" disabled class="pointer-events-none opacity-40"/>
            </span>
        @endif
        <x-icon-button icon="pencil" label="Ubah" x-on:click="buka({{ Js::from($dataRab($r)) }})"/>
        <x-delete-button :action="route('rab.destroy', $r)" :title="'Hapus item ' . $r->uraian . '?'" message="Item RAB ini akan dihapus."/>
    @endif
</div>
