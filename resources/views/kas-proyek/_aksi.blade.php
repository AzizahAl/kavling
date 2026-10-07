{{-- Aksi satu baris kas ($k). Baris otomatis dikunci: hanya bisa diubah dari menu asalnya. --}}
<div class="flex shrink-0 justify-end gap-0.5">
    @if ($k->isOtomatis())
        @php $kunci = 'Dibuat otomatis. Ubah atau hapus lewat menu ' . (\App\Models\KasTransaksi::MENU_ASAL[$k->asal] ?? 'asalnya'); @endphp
        @if ($link = $k->linkSumber())
            <x-icon-button icon="arrow-right" :label="'Buka ' . (\App\Models\KasTransaksi::MENU_ASAL[$k->asal] ?? 'sumber')" :href="$link"/>
        @endif
        <span x-data x-tip="@js($kunci)" tabindex="0" aria-label="{{ $kunci }}"><x-icon-button icon="lock" label="Terkunci" disabled class="pointer-events-none opacity-40"/></span>
    @else
        <x-icon-button icon="pencil" label="Ubah" x-on:click="buka({{ Js::from($dataKas($k)) }})"/>
        <x-delete-button :action="route('kas-proyek.destroy', $k)" :title="'Hapus ' . $k->kode . '?'" message="Transaksi kas ini akan dihapus dan saldo dihitung ulang."/>
    @endif
</div>
