<?php

namespace App\Http\Controllers;

use App\Services\HargaService;
use App\Services\Pengaturan;
use App\Services\TransaksiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class ProyekController extends Controller
{
    public function index(HargaService $harga)
    {
        return view('proyek.index', [
            'nilai'      => Pengaturan::semua(),
            'definisi'   => Pengaturan::DEFINISI,
            'grup'       => Pengaturan::GRUP,
            'tahap'      => $harga->daftarTahap(),
            'tahapAktif' => $harga->nomorTahapAktif(),
            'terjual'    => $harga->jumlahTerjual(),
        ]);
    }

    public function update(Request $request, HargaService $harga, TransaksiService $transaksi)
    {
        // Kolom desimal/persen boleh diketik dengan koma (17,34) → ubah ke titik sebelum divalidasi.
        // Sebelumnya kolom ini bertipe number sehingga browser membuang koma: 17,34 tersimpan 1734.
        $desimal = collect(Pengaturan::DEFINISI)->filter(fn ($d) => in_array($d[2], ['desimal', 'persen']))->keys();
        $request->merge($desimal->filter(fn ($k) => $request->filled($k))
            ->mapWithKeys(fn ($k) => [$k => str_replace([' ', ','], ['', '.'], (string) $request->input($k))])->all());

        $aturan = [];
        foreach (Pengaturan::DEFINISI as $kunci => [$grup, , $tipe]) {
            if ($grup === 'tanah') {
                continue; // dikelola di modul Kewajiban Tanah
            }
            $aturan[$kunci] = match ($tipe) {
                'pilihan'         => ['required', \Illuminate\Validation\Rule::in(array_keys(Pengaturan::PILIHAN[$kunci] ?? []))],
                'rupiah', 'angka' => ['nullable', 'integer', 'min:0'],
                'desimal'         => ['nullable', 'numeric', 'min:0'],
                'persen'          => ['nullable', 'numeric', 'min:0', 'max:100'],
                default           => ['nullable', 'string', 'max:255'],
            };
        }
        foreach (['harga_awal_m2', 'unit_per_kenaikan', 'jumlah_tahap', 'jumlah_kavling', 'tenor_maksimal', 'batas_tahan_jam', 'komisi_nominal'] as $wajib) {
            $aturan[$wajib][0] = 'required';
        }
        $aturan['unit_per_kenaikan'][] = 'min:1';
        $aturan['jumlah_tahap'][] = 'min:1';
        $aturan['tenor_maksimal'][] = 'min:1';
        $aturan['batas_tahan_jam'][] = 'min:1';
        foreach (['prefix_konsumen', 'prefix_transaksi', 'prefix_pembayaran', 'prefix_kavling'] as $p) {
            $aturan[$p] = ['required', 'alpha_num', 'max:10'];
        }

        $label = collect(Pengaturan::DEFINISI)->map(fn ($d) => $d[1])->all();

        $data = $request->validate($aturan, [], $label);

        // Aturan silang: alokasi harus 100%, bagi laba harus 100%, DP & refund masuk akal
        validator($data)->after(function (Validator $v) use ($data) {
            $alokasi = collect(['alokasi_tanah', 'alokasi_legal_infra', 'alokasi_marketing', 'alokasi_cadangan', 'alokasi_operasional'])
                ->sum(fn ($k) => (float) ($data[$k] ?? 0));
            if (abs($alokasi - 100) > 0.001) {
                $v->errors()->add('alokasi_tanah', 'Total alokasi kas harus 100% (sekarang ' . persen($alokasi, false, 2) . ').');
            }
            $laba = (float) ($data['laba_pengelola_persen'] ?? 0) + (float) ($data['laba_pemilik_persen'] ?? 0);
            if (abs($laba - 100) > 0.001) {
                $v->errors()->add('laba_pengelola_persen', 'Bagian pengelola + pemilik lahan harus 100%.');
            }
            if ((float) ($data['dp_anjuran_persen'] ?? 0) < (float) ($data['dp_minimal_persen'] ?? 0)) {
                $v->errors()->add('dp_anjuran_persen', 'DP anjuran tidak boleh lebih kecil dari DP minimal.');
            }
            if ((int) ($data['potongan_booking'] ?? 0) > (int) ($data['biaya_booking'] ?? 0)) {
                $v->errors()->add('potongan_booking', 'Potongan booking tidak boleh melebihi booking fee.');
            }
        })->validate();

        DB::transaction(function () use ($data, $harga, $transaksi) {
            Pengaturan::simpan($data);
            $harga->sinkronSkema();
            // Penentu "terjual" bisa berubah → status semua kavling dihitung ulang
            $transaksi->sinkronSemuaKavling();
        });

        return redirect()->route('proyek.index')->with('success', 'Pengaturan proyek berhasil disimpan. Tahap harga & harga kavling tersedia sudah diperbarui.');
    }
}
