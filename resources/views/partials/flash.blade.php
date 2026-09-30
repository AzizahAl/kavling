{{--
    Pesan berhasil / gagal tampil sebagai popup kecil yang mengambang di tengah layar.
    - Berhasil: menutup sendiri setelah beberapa detik (ada garis waktu), bisa ditutup lebih cepat.
    - Gagal / perhatian: tetap tampil sampai ditekan "OK" atau Esc.
    Kesalahan per kolom tetap ditampilkan tepat di bawah kolomnya.
--}}
@php
    $awal = collect([
        ['success', session('success')], ['error', session('error')], ['warning', session('warning')], ['info', session('info')],
    ])->filter(fn ($p) => filled($p[1]))->values();
    if ($errors->any() && empty($tanpaValidasi)) {
        $pesan = $errors->count() === 1 ? $errors->first() : $errors->first() . ' (dan ' . ($errors->count() - 1) . ' isian lain)';
        $awal->push(['error', $pesan]);
    }
    $gaya = [
        'success' => ['check', 'bg-green-50 text-green-600 ring-green-100', 'Berhasil'],
        'error'   => ['x', 'bg-red-50 text-red-600 ring-red-100', 'Gagal'],
        'warning' => ['warning', 'bg-amber-50 text-amber-600 ring-amber-100', 'Perhatian'],
        'info'    => ['info', 'bg-sky-50 text-sky-600 ring-sky-100', 'Info'],
    ];
@endphp
<div x-data x-init="@js($awal->all()).forEach(([j, p]) => $store.toast.tambah(j, p))"
     x-on:keydown.escape.window="$store.toast.daftar[0] && $store.toast.hapus($store.toast.daftar[0].id)">
    <template x-for="(t, i) in $store.toast.daftar" :key="t.id">
        <div x-show="i === 0 && t.tampil" class="fixed inset-0 z-[75] flex items-center justify-center p-4" role="alertdialog" aria-modal="true" :aria-label="t.pesan">
            {{-- Latar tipis; klik untuk menutup --}}
            <div x-show="i === 0 && t.tampil" x-transition.opacity.duration.200ms class="absolute inset-0 bg-slate-900/30 backdrop-blur-[1px]" x-on:click="$store.toast.hapus(t.id)"></div>

            <div x-show="i === 0 && t.tampil"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="relative w-full max-w-sm overflow-hidden rounded-popup bg-white px-6 pt-7 pb-6 text-center shadow-popup">
                @foreach ($gaya as $jenis => [$ikon, $warna, $judul])
                    <template x-if="t.jenis === @js($jenis)">
                        <div>
                            <span class="mx-auto flex size-14 items-center justify-center rounded-full ring-8 {{ $warna }}">
                                <x-icon :name="$ikon" class="size-7" stroke-width="2.25"/>
                            </span>
                            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $judul }}</h3>
                        </div>
                    </template>
                @endforeach
                <p class="mt-1.5 text-sm text-slate-600" x-text="t.pesan"></p>
                <button type="button" class="btn mt-6 w-full" :class="t.jenis === 'error' ? 'btn-danger' : (t.jenis === 'success' ? 'btn-primary' : 'btn-secondary')"
                        x-on:click="$store.toast.hapus(t.id)" x-init="$nextTick(() => $el.focus())">OK</button>
                {{-- Garis waktu tutup otomatis (hanya untuk pesan berhasil) --}}
                <template x-if="t.jenis === 'success'">
                    <div class="absolute inset-x-0 bottom-0 h-1 bg-green-100"><div class="h-full origin-left bg-green-500 animate-[habis_3s_linear_forwards]"></div></div>
                </template>
            </div>
        </div>
    </template>
</div>
