@props(['title' => null, 'subtitle' => null, 'breadcrumbs' => [], 'back' => null])
{{--
    Baris atas halaman. Halaman daftar tidak menampilkan judul (posisi terlihat dari menu aktif): hanya tombol aksi, rata kanan.
    Halaman detail (punya $back) menampilkan tombol kembali dan identitas datanya (mis. kode transaksi), tanpa kalimat tambahan.
--}}
@if ($back || isset($actions))
    <div {{ $attributes->merge(['class' => 'mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between']) }}>
        @if ($back)
            <div class="flex min-w-0 items-center gap-2">
                <x-icon-button :href="$back" icon="arrow-left" label="Kembali" class="-ml-2 shrink-0"/>
                <div class="min-w-0">
                    <h1 class="truncate text-lg font-semibold text-slate-900">{{ $title }}</h1>
                    @if ($subtitle)<p class="truncate text-[13px] text-slate-500">{{ $subtitle }}</p>@endif
                </div>
            </div>
        @endif
        @isset($actions)
            <div class="flex flex-wrap items-center gap-2 sm:ml-auto sm:justify-end [&>*]:grow sm:[&>*]:grow-0">{{ $actions }}</div>
        @endisset
    </div>
@endif
