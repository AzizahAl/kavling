<?php

namespace App\Services;

use App\Models\Kavling;
use App\Models\Pembayaran;
use App\Models\TransaksiPenjualan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Semua aturan bisnis transaksi penjualan ada di sini (bukan di controller):
 * pembuatan transaksi + penguncian harga, pencatatan pembayaran (otomatis masuk kas),
 * perubahan status transaksi & kavling, dan pembatalan + refund.
 */
class TransaksiService
{
    public function __construct(
        private HargaService $harga,
        private KasService $kas,
        private AngsuranService $angsuran,
    ) {}

    // ================================================================
    // TRANSAKSI
    // ================================================================

    /**
     * @param array $data konsumen_id, kavling_id, agen_id, tanggal, jenis_pembayaran, tenor, nominal_dp, catatan
     * @param array|null $bayarAwal jenis, nominal, metode, no_bukti (opsional)
     */
    public function buat(array $data, ?array $bayarAwal = null, ?int $userId = null): TransaksiPenjualan
    {
        return DB::transaction(function () use ($data, $bayarAwal, $userId) {
            $kavling = Kavling::lockForUpdate()->findOrFail($data['kavling_id']);

            if ($kavling->transaksiAktif()->exists()) {
                $this->gagal('kavling_id', "Kavling {$kavling->kode_kavling} sudah memiliki transaksi aktif.");
            }
            if (! ($kavling->luas > 0)) {
                $this->gagal('kavling_id', "Luas kavling {$kavling->kode_kavling} belum diisi. Lengkapi di Master Kavling dulu.");
            }

            // Harga dikunci pada tahap yang berlaku saat transaksi dibuat
            $hargaM2 = $this->harga->hargaAktif();
            $nilaiJual = round((float) $kavling->luas * $hargaM2);

            $this->validasiSkema($data, $nilaiJual);

            $t = TransaksiPenjualan::create([
                'kode_transaksi'   => Penomoran::berikut('transaksi_penjualans', 'kode_transaksi', Pengaturan::get('prefix_transaksi', 'TRX')),
                'tanggal'          => $data['tanggal'],
                'status'           => 'reservasi',
                'konsumen_id'      => $data['konsumen_id'],
                'kavling_id'       => $kavling->id,
                'agen_id'          => $data['agen_id'] ?? null,
                'skema_harga_id'   => $this->harga->tahapAktif()?->id,
                'luas'             => $kavling->luas,
                'harga_per_m2'     => $hargaM2,
                'nilai_jual'       => $nilaiJual,
                'biaya_reservasi'  => (int) Pengaturan::get('biaya_reservasi', 0),
                'biaya_booking'    => (int) Pengaturan::get('biaya_booking', 0),
                'jenis_pembayaran' => $data['jenis_pembayaran'],
                'tenor'            => $data['jenis_pembayaran'] === 'angsuran' ? (int) $data['tenor'] : null,
                'nominal_dp'       => (float) ($data['nominal_dp'] ?? 0),
                'catatan'          => $data['catatan'] ?? null,
                'dibuat_oleh'      => $userId,
            ]);

            $t->checklist()->create([]);
            $this->angsuran->buatJadwal($t);

            if ($bayarAwal && (float) ($bayarAwal['nominal'] ?? 0) > 0) {
                $this->catatPembayaran($t, $bayarAwal + ['tanggal' => $data['tanggal']], $userId, 'bayar_');
            }

            $this->sinkron($t);

            return $t;
        });
    }

    /** Ubah transaksi aktif. Kavling & harga tidak bisa diubah (batalkan lalu buat transaksi baru). */
    public function ubah(TransaksiPenjualan $t, array $data): TransaksiPenjualan
    {
        return DB::transaction(function () use ($t, $data) {
            $t = TransaksiPenjualan::lockForUpdate()->findOrFail($t->id);
            $this->pastikanAktif($t);
            $this->validasiSkema($data, (float) $t->nilai_jual);

            if ($data['jenis_pembayaran'] === 'cash' && $t->pembayarans()->where('jenis', 'angsuran')->exists()) {
                $this->gagal('jenis_pembayaran', 'Transaksi ini sudah menerima pembayaran angsuran, tidak bisa diubah menjadi cash.');
            }

            $t->update([
                'tanggal'          => $data['tanggal'],
                'konsumen_id'      => $data['konsumen_id'] ?? $t->konsumen_id,
                'agen_id'          => $data['agen_id'] ?? null,
                'jenis_pembayaran' => $data['jenis_pembayaran'],
                'tenor'            => $data['jenis_pembayaran'] === 'angsuran' ? (int) $data['tenor'] : null,
                'nominal_dp'       => (float) ($data['nominal_dp'] ?? 0),
                'catatan'          => $data['catatan'] ?? null,
            ]);

            $this->angsuran->buatJadwal($t->fresh());
            $this->sinkron($t);

            return $t;
        });
    }

    /** Hitung rincian refund bila transaksi dibatalkan sekarang (dipakai untuk pratinjau & eksekusi). */
    public function rincianRefund(TransaksiPenjualan $t): array
    {
        $t->loadMissing('pembayarans');
        $rasio = fn (float $refund, float $biaya) => $biaya > 0 ? min(1, max(0, $refund / $biaya)) : 0;

        $baris = [];
        $tambah = function (string $label, float $dibayar, ?float $persen, string $aturan) use (&$baris) {
            if ($dibayar <= 0) {
                return;
            }
            $baris[] = [
                'label'   => $label,
                'dibayar' => $dibayar,
                'refund'  => $persen === null ? null : round($dibayar * $persen),
                'aturan'  => $aturan,
            ];
        };

        $tambah('Reservasi', $t->terbayarJenis('reservasi'),
            $rasio(Pengaturan::get('refund_reservasi', 0), (float) $t->biaya_reservasi),
            'Refund ' . rupiah(Pengaturan::get('refund_reservasi')) . ' dari ' . rupiah($t->biaya_reservasi));
        $tambah('Booking Fee', $t->terbayarJenis('booking'),
            $rasio(Pengaturan::get('refund_booking', 0), (float) $t->biaya_booking),
            'Refund ' . rupiah(Pengaturan::get('refund_booking')) . ' dari ' . rupiah($t->biaya_booking));

        $pDp = Pengaturan::get('refund_dp_persen');
        $tambah('Down Payment (DP)', $t->terbayarJenis('dp'), $pDp === null ? null : $pDp / 100,
            $pDp === null ? 'Aturan refund DP belum diisi di Pengaturan' : 'Refund ' . persen($pDp, false));

        $pAng = Pengaturan::get('refund_angsuran_persen');
        $tambah('Angsuran & Pelunasan', $t->terbayarJenis('angsuran') + $t->terbayarJenis('pelunasan'), $pAng === null ? null : $pAng / 100,
            $pAng === null ? 'Aturan refund angsuran belum diisi di Pengaturan' : 'Refund ' . persen($pAng, false));

        $lengkap = collect($baris)->every(fn ($b) => $b['refund'] !== null);

        return [
            'baris'        => $baris,
            'lengkap'      => $lengkap,
            'total_bayar'  => collect($baris)->sum('dibayar'),
            'total_refund' => $lengkap ? collect($baris)->sum('refund') : null,
        ];
    }

    public function batal(TransaksiPenjualan $t, string $tanggal, string $alasan): TransaksiPenjualan
    {
        return DB::transaction(function () use ($t, $tanggal, $alasan) {
            $t = TransaksiPenjualan::with(['pembayarans', 'kavling', 'konsumen'])->lockForUpdate()->findOrFail($t->id);
            $this->pastikanAktif($t, bolehLunas: true);

            $refund = $this->rincianRefund($t);
            if (! $refund['lengkap']) {
                throw ValidationException::withMessages([
                    'alasan' => 'Aturan refund DP/angsuran belum diisi di Pengaturan Proyek, jadi nilai refund belum bisa dihitung.',
                ]);
            }

            if ($refund['total_refund'] > 0) {
                $rincian = collect($refund['baris'])
                    ->map(fn ($b) => "{$b['label']}: dibayar " . rupiah($b['dibayar']) . ', refund ' . rupiah($b['refund']))
                    ->implode('; ');
                $this->kas->catatRefund($t, $refund['total_refund'], $tanggal, $rincian);
            }

            $t->update(['status' => 'batal', 'tanggal_batal' => $tanggal, 'alasan_batal' => $alasan]);
            $t->jadwalAngsurans()->delete();

            $this->sinkronKavling($t->kavling);

            return $t;
        });
    }

    // ================================================================
    // PEMBAYARAN
    // ================================================================

    /** $prefixError dipakai agar pesan error menempel ke field form yang tepat (mis. "bayar_nominal"). */
    public function catatPembayaran(TransaksiPenjualan $t, array $data, ?int $userId = null, string $prefixError = ''): Pembayaran
    {
        return DB::transaction(function () use ($t, $data, $userId, $prefixError) {
            $t = TransaksiPenjualan::with('pembayarans')->lockForUpdate()->findOrFail($t->id);
            $this->pastikanAktif($t);
            $this->validasiPembayaran($t, $data, null, $prefixError);

            $p = $t->pembayarans()->create([
                'kode'        => Penomoran::berikut('pembayarans', 'kode', Pengaturan::get('prefix_pembayaran', 'KWT'), \Illuminate\Support\Carbon::parse($data['tanggal'])->year),
                'tanggal'     => $data['tanggal'],
                'jenis'       => $data['jenis'],
                'nominal'     => $data['nominal'],
                'metode'      => $data['metode'] ?? 'transfer',
                'no_bukti'    => $data['no_bukti'] ?? null,
                'catatan'     => $data['catatan'] ?? null,
                'dibuat_oleh' => $userId,
            ]);

            $this->kas->catatPembayaran($p);
            $this->sinkron($t);

            return $p;
        });
    }

    public function ubahPembayaran(Pembayaran $p, array $data): Pembayaran
    {
        return DB::transaction(function () use ($p, $data) {
            $t = TransaksiPenjualan::with('pembayarans')->lockForUpdate()->findOrFail($p->transaksi_id);
            $this->pastikanAktif($t, bolehLunas: true);
            $this->validasiPembayaran($t, $data, $p);

            $p->update(collect($data)->only(['tanggal', 'jenis', 'nominal', 'metode', 'no_bukti', 'catatan'])->all());
            $this->kas->catatPembayaran($p);
            $this->sinkron($t);

            return $p;
        });
    }

    public function hapusPembayaran(Pembayaran $p): void
    {
        DB::transaction(function () use ($p) {
            $t = TransaksiPenjualan::lockForUpdate()->findOrFail($p->transaksi_id);
            $this->pastikanAktif($t, bolehLunas: true);

            $p->kas()->delete();
            $p->delete();
            $this->sinkron($t);
        });
    }

    // ================================================================
    // SINKRONISASI STATUS
    // ================================================================

    /** Hitung ulang status transaksi dari pembayaran, lalu status kavling. */
    public function sinkron(TransaksiPenjualan $t): void
    {
        $t = $t->fresh(['pembayarans', 'kavling']);

        if (! $t->isBatal()) {
            $t->update(['status' => $this->hitungStatus($t)]);
        }

        $this->sinkronKavling($t->kavling);
    }

    /**
     * Tahap boleh dilewati (mis. langsung angsuran tanpa DP). Status = tahap terjauh yang sudah dibayar:
     * lunas > angsuran > dp > booking > reservasi.
     */
    public function hitungStatus(TransaksiPenjualan $t): string
    {
        $ada = fn (string $jenis) => $t->terbayarJenis($jenis) > 0;

        return match (true) {
            $t->nilai_jual > 0 && $t->sisa() <= 0          => 'lunas',
            $ada('angsuran') || ($t->isAngsuran() && $ada('pelunasan')) => 'angsuran',
            $ada('dp') || $ada('pelunasan')                => 'dp',
            $ada('booking')                                => 'booking',
            default                                        => 'reservasi',
        };
    }

    /**
     * Status kavling mengikuti transaksi aktifnya:
     * tidak ada transaksi → tersedia; PPJB ditandatangani → terjual;
     * selain itu reservasi / booking / dp (DP, angsuran, maupun lunas sebelum PPJB).
     */
    public function sinkronKavling(Kavling $kavling): void
    {
        $terjualSebelum = $this->harga->jumlahTerjual();

        $aktif = $kavling->transaksiAktif()->with('checklist')->first();
        $status = match (true) {
            ! $aktif                                         => 'tersedia',
            (bool) $aktif->checklist?->ppjbDitandatangani()  => 'terjual',
            in_array($aktif->status, ['reservasi', 'booking']) => $aktif->status,
            default                                          => 'dp',
        };

        if ($kavling->status !== $status) {
            $kavling->update(['status' => $status]);
        }

        // Jumlah terjual berubah → tahap harga bisa naik/turun → harga kavling tersedia diperbarui
        if ($status === 'tersedia' || $this->harga->jumlahTerjual() !== $terjualSebelum) {
            $this->harga->sinkronHargaKavling();
        }
    }

    // ================================================================
    // VALIDASI ATURAN BISNIS
    // ================================================================

    private function validasiSkema(array $data, float $nilaiJual): void
    {
        $dp = (float) ($data['nominal_dp'] ?? 0);
        $minPersen = (float) Pengaturan::get('dp_minimal_persen', 0);
        $minDp = ceil($nilaiJual * $minPersen / 100);
        $tenorMaks = (int) Pengaturan::get('tenor_maksimal', 18);

        if ($dp < $minDp) {
            $this->gagal('nominal_dp', 'DP minimal ' . persen($minPersen, false) . ' dari harga jual (' . rupiah($minDp) . ').');
        }
        if ($dp > $nilaiJual) {
            $this->gagal('nominal_dp', 'DP tidak boleh melebihi harga jual (' . rupiah($nilaiJual) . ').');
        }
        if ($data['jenis_pembayaran'] === 'angsuran') {
            $tenor = (int) ($data['tenor'] ?? 0);
            if ($tenor < 1 || $tenor > $tenorMaks) {
                $this->gagal('tenor', "Tenor angsuran harus 1 sampai {$tenorMaks} bulan.");
            }
            if ($dp >= $nilaiJual) {
                $this->gagal('nominal_dp', 'DP sama dengan harga jual — pilih pembayaran Cash, bukan Angsuran.');
            }
        }
    }

    private function validasiPembayaran(TransaksiPenjualan $t, array $data, ?Pembayaran $abaikan = null, string $prefix = ''): void
    {
        $nominal = (float) $data['nominal'];
        $jenis = $data['jenis'];
        $lain = $t->pembayarans->when($abaikan, fn ($c) => $c->where('id', '!=', $abaikan->id));

        if ($nominal <= 0) {
            $this->gagal($prefix . 'nominal', 'Nominal pembayaran harus lebih dari 0.');
        }
        if ($jenis === 'angsuran' && ! $t->isAngsuran()) {
            $this->gagal($prefix . 'jenis', 'Transaksi cash tidak memiliki angsuran. Gunakan jenis Pelunasan.');
        }

        if (in_array($jenis, ['reservasi', 'booking'])) {
            $biaya = (float) ($jenis === 'reservasi' ? $t->biaya_reservasi : $t->biaya_booking);
            $sudah = (float) $lain->where('jenis', $jenis)->sum('nominal');
            if ($sudah + $nominal > $biaya) {
                $this->gagal($prefix . 'nominal', ucfirst($jenis) . ' untuk transaksi ini ' . rupiah($biaya)
                    . ', sudah dibayar ' . rupiah($sudah) . '. Sisa yang bisa diterima ' . rupiah(max(0, $biaya - $sudah)) . '.');
            }

            return;
        }

        $pokok = (float) $lain->whereIn('jenis', TransaksiPenjualan::JENIS_POKOK)->sum('nominal');
        $sisa = (float) $t->nilai_jual - $pokok;
        if ($nominal > $sisa) {
            $this->gagal($prefix . 'nominal', 'Pembayaran melebihi sisa harga kavling (' . rupiah(max(0, $sisa)) . ').');
        }
    }

    private function pastikanAktif(TransaksiPenjualan $t, bool $bolehLunas = false): void
    {
        if ($t->isBatal()) {
            throw ValidationException::withMessages(['transaksi' => "Transaksi {$t->kode_transaksi} sudah dibatalkan."]);
        }
        if (! $bolehLunas && $t->status === 'lunas') {
            throw ValidationException::withMessages(['transaksi' => "Transaksi {$t->kode_transaksi} sudah lunas."]);
        }
    }

    private function gagal(string $field, string $pesan): never
    {
        throw ValidationException::withMessages([$field => $pesan]);
    }
}
