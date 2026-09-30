{{-- Pesan sukses / gagal / validasi, seragam untuk semua halaman --}}
@php
    $pesan = collect([
        ['success', session('success'), 'check-circle', 'border-forest-200 bg-forest-50 text-forest-700'],
        ['error', session('error'), 'x-circle', 'border-red-200 bg-red-50 text-red-700'],
        ['warning', session('warning'), 'warning', 'border-amber-200 bg-amber-50 text-amber-800'],
        ['info', session('info'), 'info', 'border-sky-200 bg-sky-50 text-sky-800'],
    ])->filter(fn ($p) => filled($p[1]));
@endphp

@foreach ($pesan as [$jenis, $teks, $ikon, $kelas])
    <div x-data="{ show: true }" x-show="show" x-transition.opacity
         @if ($jenis === 'success') x-init="setTimeout(() => show = false, 6000)" @endif
         class="mb-5 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm {{ $kelas }}" role="alert">
        <x-icon :name="$ikon" class="mt-0.5 h-5 w-5"/>
        <p class="flex-1">{{ $teks }}</p>
        <button type="button" class="-m-1 rounded p-1 opacity-60 hover:opacity-100" x-on:click="show = false" aria-label="Tutup"><x-icon name="x" class="h-4 w-4"/></button>
    </div>
@endforeach

@if ($errors->any() && empty($tanpaValidasi))
    <div x-data="{ show: true }" x-show="show" class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        <x-icon name="warning" class="mt-0.5 h-5 w-5"/>
        <div class="flex-1">
            <p class="font-medium">Data belum bisa disimpan. Periksa isian berikut:</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        <button type="button" class="-m-1 rounded p-1 opacity-60 hover:opacity-100" x-on:click="show = false" aria-label="Tutup"><x-icon name="x" class="h-4 w-4"/></button>
    </div>
@endif
