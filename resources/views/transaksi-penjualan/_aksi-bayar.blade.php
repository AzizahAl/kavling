{{-- Aksi satu pembayaran (tabel & kartu HP). Butuh $p, $t, $admin, $dataBayar. --}}
<div class="flex justify-end gap-0.5">
    @if ($admin)
        <x-icon-button icon="receipt" label="Kwitansi" :href="route('pembayaran.kwitansi', $p)"/>
        @unless ($t->isBatal())
            <x-icon-button icon="pencil" label="Ubah" x-on:click="ubah({{ Js::from($dataBayar($p)) }})"/>
            <x-delete-button :action="route('pembayaran.destroy', [$t, $p])" :title="'Hapus pembayaran ' . $p->kode . '?'"
                             message="Catatan kas masuknya ikut terhapus dan status transaksi dihitung ulang."/>
        @endunless
    @endif
</div>
