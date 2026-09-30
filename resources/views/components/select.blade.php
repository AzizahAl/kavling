@props(['name', 'options' => [], 'value' => null, 'placeholder' => null, 'cari' => null, 'disabled' => false])
{{--
    Dropdown modern pengganti <select>. Nilai terkirim lewat <input type="hidden" name="...">.
      - $options: [nilai => label]  atau  opsi dinamis Alpine:  data-opsi-expr="daftar" (array {v,l} / {id,label})
      - x-model="..." di komponen ini terhubung ke nilainya (x-modelable)
      - Memancarkan event "change" (bubbling) dari input tersembunyi saat nilai berubah
    Bisa dipakai dengan keyboard: panah, Enter, Esc, dan ketik huruf awal / cari.
--}}
@php
    $opsi = collect($options)->map(fn ($l, $v) => ['v' => (string) $v, 'l' => (string) $l])->values();
    // Placeholder = pilihan kosong yang bisa dipilih (mis. "Semua status"), sama seperti <option value=""> sebelumnya
    if ($placeholder !== null) {
        $opsi->prepend(['v' => '', 'l' => $placeholder]);
    }
    $ikatNonaktif = $attributes->get('x-bind:disabled');
    $nilai = (string) old($name, $value);
    // Sama seperti <select> biasa: tanpa placeholder, opsi pertama otomatis terpilih
    if ($placeholder === null && $nilai === '' && $opsi->isNotEmpty()) {
        $nilai = $opsi[0]['v'];
    }
    $cari ??= $opsi->count() > 8;
    $id = $attributes->get('id', $name);
    $salah = $errors->has($name);
@endphp
<div x-data="pilihan(@js(['nilai' => $nilai, 'opsi' => $opsi, 'placeholder' => $placeholder ?? 'Pilih…', 'cari' => $cari]))" x-modelable="nilai"
     {{ $attributes->only(['x-model', 'x-model.number', 'data-opsi-expr', 'x-on:change', '@change'])->merge(['class' => 'relative min-w-0 ' . $attributes->get('class')]) }}>
    <input type="hidden" name="{{ $name }}" x-ref="plh_nilai" :value="nilai" @if ($ikatNonaktif) x-bind:disabled="{{ $ikatNonaktif }}" @endif>
    <button type="button" x-ref="plh_tombol" id="{{ $id }}" @disabled($disabled)
            @if ($ikatNonaktif) x-bind:disabled="{{ $ikatNonaktif }}" @endif
            x-on:click="buka ? tutup() : bukaPanel()" x-on:keydown="tombolKey($event)"
            aria-haspopup="listbox" :aria-expanded="buka" @if ($salah) aria-invalid="true" @endif
            @class(['form-input flex items-center justify-between gap-2 text-left', 'is-invalid' => $salah])
            :class="buka && 'border-brand-500 ring-3 ring-brand-500/15'">
        <span class="truncate" :class="label ? 'text-slate-900' : 'text-slate-400'" x-text="label || placeholder">{{ $opsi->firstWhere('v', $nilai)['l'] ?? ($placeholder ?? 'Pilih…') }}</span>
        <x-icon name="chevrons-up-down" class="size-4 text-slate-400"/>
    </button>

    <template x-teleport="body">
        <div x-show="buka" x-cloak x-on:click.outside="if (! $refs.plh_tombol.contains($event.target)) tutup(false)"
             x-on:scroll.window.passive="if (buka) atur()" x-on:resize.window="if (buka) atur()"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-end="opacity-0"
             :style="pos" x-on:keydown="panelKey($event)"
             class="fixed z-[70] overflow-hidden rounded-kontrol border border-slate-200 bg-white shadow-menu">
            <template x-if="cariAktif">
                <div class="border-b border-slate-100 p-2">
                    <input type="search" x-ref="plh_cari" x-model="q" x-on:input="sorot = 0" placeholder="Cari…" class="form-input h-9 shadow-none" autocomplete="off">
                </div>
            </template>
            <ul x-ref="plh_daftar" tabindex="-1" role="listbox" class="max-h-64 overflow-y-auto overscroll-contain p-1 outline-none">
                <template x-for="(o, i) in tersaring" :key="o.v">
                    <li role="option" :data-i="i" :aria-selected="o.v === String(nilai)"
                        x-on:click="pilih(o)" x-on:mousemove="sorot = i"
                        :class="i === sorot ? 'bg-slate-100 text-slate-900' : 'text-slate-700'"
                        class="flex min-h-10 cursor-pointer items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm sm:min-h-9">
                        <span x-text="o.l" :class="o.v === String(nilai) && 'font-medium text-brand-700'"></span>
                        <x-icon name="check" class="size-4 text-brand-600" x-show="o.v === String(nilai)"/>
                    </li>
                </template>
                <li x-show="!tersaring.length" class="px-3 py-2 text-sm text-slate-400">Tidak ada pilihan</li>
            </ul>
        </div>
    </template>
</div>
