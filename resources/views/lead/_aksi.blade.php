{{-- Aksi satu baris lead (tabel & kartu HP). $l = lead, $data = data untuk form ubah. --}}
<div @class(['flex items-center gap-1', 'justify-end' => empty($hp)])>
    @if ($l->tahap === 'lead')
        <x-button variant="secondary" size="sm" x-on:click="maju('prospek', {{ Js::from($data) }})">Jadikan Prospek</x-button>
    @elseif ($l->tahap === 'prospek')
        <x-button size="sm" icon="check" x-on:click="maju('closing', {{ Js::from($data) }})">Closing</x-button>
    @endif
    <span @class(['flex', 'ml-auto' => ! empty($hp)])>
        <x-icon-button icon="pencil" label="Ubah" x-on:click="ubah({{ Js::from($data) }})"/>
        @if ($l->tahap !== 'lead')
            <form method="POST" action="{{ route('lead.mundur', $l) }}" class="inline-flex"
                  data-confirm="{{ $l->tahap === 'closing' ? 'Closing → Prospek. Tautan ke transaksi dilepas.' : 'Prospek → Lead.' }}"
                  data-confirm-title="Kembalikan tahap {{ $l->nama }}?" data-confirm-ok="Kembalikan" data-confirm-danger="false">
                @csrf <x-icon-button type="submit" icon="undo" label="Kembalikan tahap"/>
            </form>
        @endif
        @if ($l->tahap !== 'closing')
            <x-delete-button :action="route('lead.destroy', $l)" :title="'Hapus lead ' . $l->nama . '?'"/>
        @endif
    </span>
</div>
