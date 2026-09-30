@props(['status', 'label' => null, 'warna' => null])
<span {{ $attributes->merge(['class' => 'badge ' . \App\Support\Status::kelas($status, $warna)]) }}>{{ $label ?? \App\Support\Status::label($status) }}</span>
