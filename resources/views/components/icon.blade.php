@props(['name'])
@php
    // Ikon garis Lucide (lokal: resources/ikon/lucide.json, dibuat oleh scripts/salin-ikon.mjs).
    static $ikon = null;
    $ikon ??= json_decode(file_get_contents(resource_path('ikon/lucide.json')), true);
    $kelas = (string) $attributes->get('class', '');
    // Ukuran bawaan selalu ada kecuali kelas ukuran diberikan (mencegah ikon melebar tanpa batas)
    $ukuran = preg_match('/(^|\s)(size-|h-|w-)/', $kelas) ? '' : 'size-5';
@endphp
<svg {{ $attributes->except('class')->merge(['class' => trim("$ukuran $kelas shrink-0")]) }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikon[$name] ?? $ikon['info'] !!}</svg>
