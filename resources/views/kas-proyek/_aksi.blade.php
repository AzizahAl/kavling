{{-- Aksi satu baris kas. Baris otomatis hanya bisa dibuka sumbernya. --}}
<div class="flex shrink-0 justify-end gap-0.5">
    @if ($k->isOtomatis())
        @if ($link = $k->linkSumber())<x-icon-button icon="arrow-right" label="Buka sumber" :href="$link"/>@endif
    @else
        <x-icon-button icon="pencil" label="Ubah" x-on:click="buka({{ Js::from($dataKas($k)) }})"/>
        <x-delete-button :action="route('kas-proyek.destroy', $k)" :title="'Hapus ' . $k->kode . '?'"/>
    @endif
</div>
