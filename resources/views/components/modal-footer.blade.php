@props(['batal' => 'Batal'])
{{-- Susunan tombol popup seragam: [Batal] [Aksi utama] di kanan; di HP tombol utama di atas, lebar penuh. --}}
<div {{ $attributes->merge(['class' => 'flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50/60 px-5 py-3.5 sm:flex-row sm:justify-end']) }}>
    <button type="button" class="btn btn-secondary" x-on:click="open = false">{{ $batal }}</button>
    {{ $slot }}
</div>
