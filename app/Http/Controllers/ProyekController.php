<?php

namespace App\Http\Controllers;

use App\Services\HargaService;
use App\Services\Pengaturan;
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

    public function update(Request $request, HargaService $harga)
    {
        $aturan = [];
        foreach (Pengaturan::DEFINISI as $kunci => [, , $tipe]) {
            $aturan[$kunci] = match ($tipe) {
                'rupiah', 'angka' => ['nullable', 'integer', 'min:0'],
                'desimal'         => ['nullable', 'numeric', 'min:0'],
                'persen'          => ['nullable', 'numeric', 'min:0', 'max:100'],
                default           => ['nullable', 'string', 'max:255'],
            };
        }
        foreach (['harga_awal_m2', 'unit_per_kenaikan', 'jumlah_tahap', 'jumlah_kavling', 'tenor_maksimal'] as $wajib) {
            $aturan[$wajib][0] = 'required';
        }
        $aturan['unit_per_kenaikan'][] = 'min:1';
        $aturan['jumlah_tahap'][] = 'min:1';
        $aturan['tenor_maksimal'][] = 'min:1';
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
            foreach (['reservasi', 'booking'] as $j) {
                if ((int) ($data["refund_{$j}"] ?? 0) > (int) ($data["biaya_{$j}"] ?? 0)) {
                    $v->errors()->add("refund_{$j}", 'Refund ' . $j . ' tidak boleh melebihi biayanya.');
                }
            }
        })->validate();

        DB::transaction(function () use ($data, $harga) {
            Pengaturan::simpan($data);
            $harga->sinkronSkema();
        });

        return redirect()->route('proyek.index')->with('success', 'Pengaturan proyek berhasil disimpan. Tahap harga & harga kavling tersedia sudah diperbarui.');
    }
}
