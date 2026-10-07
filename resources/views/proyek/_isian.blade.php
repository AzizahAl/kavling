{{--
    Satu isian pengaturan sesuai tipenya. Butuh $kunci, $definisi, $nilai.
    Opsional: $label (label singkat), $satuan (akhiran mis. "bulan"), $model (x-model Alpine), $lebar (kelas kolom).
--}}
@php
    [, $labelAsli, $tipe, , $ket] = $definisi[$kunci];
    $label ??= $labelAsli;
    $satuan ??= null;
    $model ??= null;
    $lebar ??= '';
    $val = is_array($nilai[$kunci]) ? implode(', ', $nilai[$kunci]) : $nilai[$kunci];
    $wajib = in_array($kunci, ['jumlah_kavling', 'tenor_maksimal', 'batas_tahan_jam', 'komisi_nominal', 'prefix_konsumen', 'prefix_transaksi', 'prefix_pembayaran', 'prefix_kavling', 'terjual_saat', 'komisi_hak_saat', 'komisi_saat_batal']);
    $salah = $errors->has($kunci);
@endphp
<x-field :label="$tipe === 'pilihan' ? null : $label" :name="$kunci" :hint="$ket" :required="$wajib" :class="$lebar">
    @switch($tipe)
        @case('rupiah')
            <x-money :name="$kunci" :value="$val"/>
            @break
        @case('persen')
        @case('desimal')
        @case('angka')
            <div class="relative">
                <input type="text" inputmode="{{ $tipe === 'angka' ? 'numeric' : 'decimal' }}" autocomplete="off" name="{{ $kunci }}" id="{{ $kunci }}"
                       value="{{ old($kunci, $val) }}" @if ($model) x-model="{{ $model }}" @endif placeholder="0"
                       @if ($salah) aria-invalid="true" @endif
                       @class(['form-input tabular-nums', 'pr-14' => $satuan && $tipe !== 'persen', 'pr-9' => $tipe === 'persen', 'is-invalid' => $salah])>
                @if ($tipe === 'persen' || $satuan)
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-slate-400">{{ $tipe === 'persen' ? '%' : $satuan }}</span>
                @endif
            </div>
            @break
        @case('pilihan')
            <fieldset>
                <legend @class(['form-label', 'wajib' => $wajib])>{{ $label }}</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Services\Pengaturan::PILIHAN[$kunci] ?? [] as $v => $l)
                        <label class="cursor-pointer">
                            <input type="radio" name="{{ $kunci }}" value="{{ $v }}" class="peer sr-only" @checked((string) old($kunci, $val) === (string) $v)>
                            <span class="inline-flex h-9 items-center gap-1.5 rounded-kontrol border border-slate-200 bg-white px-3 text-sm text-slate-600 transition-colors peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:font-medium peer-checked:text-brand-800 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500/40 hover:border-slate-300">{{ $l }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            @break
        @default
            <x-input :name="$kunci" :value="$val" :class="str_starts_with($kunci, 'prefix_') ? 'uppercase tabular-nums' : ''" :x-model="$model"/>
    @endswitch
</x-field>
