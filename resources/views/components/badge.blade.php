@props(['status', 'label' => null, 'warna' => null, 'ikon' => null])
@php
    $ikon ??= $warna ? null : \App\Support\Status::ikon($status);
@endphp
<span {{ $attributes->merge(['class' => 'badge ' . \App\Support\Status::kelas($status, $warna)]) }}>
    @if ($ikon === 'titik')<span class="size-1.5 rounded-full bg-current opacity-80"></span>@elseif ($ikon)<x-icon :name="$ikon" class="size-3.5 -ml-0.5" stroke-width="2.25"/>@endif{{ $label ?? \App\Support\Status::label($status) }}
</span>
