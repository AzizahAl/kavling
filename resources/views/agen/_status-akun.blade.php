{{-- Badge kecil status akun login agen ($ag). --}}
@if (! $ag->user)
    <x-badge status="tanpa-akun" label="Belum ada akun" warna="abu" class="text-[11px]"/>
@elseif ($ag->user->aktif)
    <x-badge status="aktif" label="Akun aktif" class="text-[11px]"/>
@else
    <x-badge status="nonaktif" label="Akun nonaktif" class="text-[11px]"/>
@endif
