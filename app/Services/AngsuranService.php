<?php

namespace App\Services;

use App\Models\TransaksiPenjualan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Jadwal angsuran:
 * - sisa harga setelah DP dibagi rata sesuai tenor, selisih pembulatan masuk cicilan terakhir,
 * - cicilan ke-1 jatuh tempo 1 bulan setelah tanggal transaksi, lalu tanggal yang sama tiap bulan,
 * - status tiap cicilan dihitung dari pembayaran (dialokasikan berurutan dari cicilan tertua).
 */
class AngsuranService
{
    public function buatJadwal(TransaksiPenjualan $t): void
    {
        $t->jadwalAngsurans()->delete();

        if (! $t->isAngsuran() || $t->tenor < 1 || $t->isBatal()) {
            return;
        }

        $pokok = (int) round($t->pokokDiangsur());
        $per = intdiv($pokok, $t->tenor);

        for ($ke = 1; $ke <= $t->tenor; $ke++) {
            $t->jadwalAngsurans()->create([
                'ke'          => $ke,
                'jatuh_tempo' => $t->tanggal->copy()->addMonthsNoOverflow($ke),
                'nominal'     => $ke === $t->tenor ? $pokok - $per * ($t->tenor - 1) : $per,
            ]);
        }
    }

    /** Nominal cicilan per bulan (cicilan pertama; cicilan terakhir bisa berbeda karena pembulatan). */
    public function cicilanPerBulan(TransaksiPenjualan $t): float
    {
        return $t->isAngsuran() && $t->tenor > 0 ? intdiv((int) round($t->pokokDiangsur()), $t->tenor) : 0;
    }

    /**
     * Status setiap cicilan. Dana untuk cicilan = angsuran + pelunasan + kelebihan DP di atas rencana DP.
     * @return Collection<int, object{ke,jatuh_tempo,nominal,terbayar,sisa,status,hari_telat}>
     */
    public function statusJadwal(TransaksiPenjualan $t, ?Carbon $hariIni = null): Collection
    {
        $hariIni ??= today();
        $t->loadMissing('jadwalAngsurans', 'pembayarans');

        $dpBayar = $t->terbayarJenis('dp');
        $dana = $t->terbayarJenis('angsuran') + $t->terbayarJenis('pelunasan')
              + max(0, $dpBayar - (float) $t->nominal_dp);

        return $t->jadwalAngsurans->map(function ($j) use (&$dana, $hariIni, $t) {
            $nominal = (float) $j->nominal;
            $terbayar = min($nominal, max(0, $dana));
            $dana -= $terbayar;
            $sisa = $nominal - $terbayar;

            $telat = $sisa > 0 && $j->jatuh_tempo->lt($hariIni) && ! $t->isBatal();
            $status = match (true) {
                $sisa <= 0     => 'lunas',
                $telat         => 'terlambat',
                $terbayar > 0  => 'sebagian',
                default        => 'belum',
            };

            return (object) [
                'id'          => $j->id,
                'ke'          => $j->ke,
                'jatuh_tempo' => $j->jatuh_tempo,
                'nominal'     => $nominal,
                'terbayar'    => $terbayar,
                'sisa'        => $sisa,
                'status'      => $status,
                'hari_telat'  => $telat ? (int) $j->jatuh_tempo->diffInDays($hariIni) : 0,
            ];
        });
    }

    /** Ringkasan piutang satu transaksi: tunggakan, cicilan berikutnya. */
    public function ringkasan(TransaksiPenjualan $t, ?Carbon $hariIni = null): array
    {
        $jadwal = $this->statusJadwal($t, $hariIni);
        $telat = $jadwal->where('status', 'terlambat');
        $berikut = $jadwal->first(fn ($j) => $j->sisa > 0);

        return [
            'jadwal'          => $jadwal,
            'cicilan_lunas'   => $jadwal->where('status', 'lunas')->count(),
            'jumlah_telat'    => $telat->count(),
            'tunggakan'       => $telat->sum('sisa'),
            'hari_telat_maks' => (int) $telat->max('hari_telat'),
            'berikutnya'      => $berikut,
        ];
    }
}
