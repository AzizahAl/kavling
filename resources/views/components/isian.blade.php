{{-- Kolom isian formulir: garis titik-titik; terisi bila ada nilai. --}}
@props(['nilai' => null, 'lebar' => null, 'penuh' => false])
@php $terisi = filled($nilai); @endphp
<span {{ $attributes->class(['isian', 'isian-penuh' => $penuh, 'isian-teks' => $terisi && ! $penuh]) }}@if ($lebar && ! $terisi) style="width: {{ $lebar }}"@endif>{{ $terisi ? $nilai : "\u{00A0}" }}</span>
