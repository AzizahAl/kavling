{{-- Aksi baris agen ($ag): ubah & hapus. Agen yang punya lead/transaksi tidak dihapus, ditawarkan nonaktifkan akun. --}}
@php $punyaRiwayat = $ag->leads_count + $ag->transaksis_count + $ag->komisi_pembayarans_count > 0; @endphp
<div class="flex shrink-0 justify-end gap-0.5">
    <x-icon-button icon="pencil" label="Ubah" x-on:click="a = {{ Js::from($dataForm($ag)) }}; $dispatch('open-modal', 'agen')"/>
    @if (! $punyaRiwayat)
        <x-delete-button :action="route('agen.destroy', $ag)" :title="'Hapus agen ' . $ag->nama_agen . '?'" message="Data agen beserta akun login-nya akan dihapus."/>
    @elseif ($ag->aktif || $ag->user?->aktif)
        <form method="POST" action="{{ route('agen.nonaktifkan', $ag) }}" class="inline-flex"
              data-confirm="{{ $ag->nama_agen }} sudah memiliki lead atau transaksi sehingga tidak bisa dihapus. Nonaktifkan akunnya? Agen tidak bisa login dan tidak muncul di pilihan lead, riwayatnya tetap tersimpan."
              data-confirm-title="Agen tidak bisa dihapus" data-confirm-ok="Nonaktifkan Akun">
            @csrf @method('PATCH')
            <x-icon-button type="submit" icon="trash" label="Hapus" variant="danger"/>
        </form>
    @else
        <x-icon-button icon="trash" label="Sudah nonaktif. Agen yang punya riwayat tidak bisa dihapus" disabled class="cursor-not-allowed opacity-40"/>
    @endif
</div>
