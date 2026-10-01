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
 * status pembayaran & status kavling (terpisah dari status dokumen), riwayat perubahan status,
 * penahanan kavling selama menunggu uang reservasi, dan pembatalan + pengembalian per jenis uang.
 */
class TransaksiService
{
    public function __construct(
        private HargaService $harga,
        private KasService $kas,
        private AngsuranService $angsuran,
        private AlokasiService $alokasi,
        private RiwayatService $riwayat,
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
                'status'           => 'menunggu',
                'batas_tahan'      => now()->addHours(max(1, (int) Pengaturan::get('batas_tahan_jam', 48))),
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
            $this->riwayat->catat('pembayaran', $t->id, $kavling->id, null, 'menunggu', 'Transaksi dibuat');

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

    /**
     * Rincian pembatalan per jenis uang:
     *  - reservasi dikembalikan penuh,
     *  - booking dipotong (Pengaturan: potongan_booking) untuk komisi & marketing, sisanya dikembalikan,
     *  - DP + angsuran + pelunasan: potongan diisi admin sesuai ketentuan PPJB, sisanya dikembalikan.
     */
    public function rincianPembatalan(TransaksiPenjualan $t, float $potonganPokok = 0): array
    {
        $t->loadMissing('pembayarans');
        $reservasi = $t->terbayarJenis('reservasi');
        $booking = $t->terbayarJenis('booking');
        $pokok = $t->pokokTerbayar();
        $potBooking = min($booking, (float) Pengaturan::get('potongan_booking', 0));
        $potPokok = min(max(0, $potonganPokok), $pokok);

        $r = [
            'reservasi_dibayar' => $reservasi, 'reservasi_refund' => $reservasi,
            'booking_dibayar' => $booking, 'booking_potongan' => $potBooking, 'booking_refund' => $booking - $potBooking,
            'pokok_dibayar' => $pokok, 'pokok_potongan' => $potPokok, 'pokok_refund' => $pokok - $potPokok,
        ];
        $r['total_dibayar'] = $reservasi + $booking + $pokok;
        $r['total_refund'] = $r['reservasi_refund'] + $r['booking_refund'] + $r['pokok_refund'];

        return $r;
    }

    /**
     * @param array $data tanggal, alasan, pokok_potongan, dasar_ketentuan
     */
    public function batal(TransaksiPenjualan $t, array $data, bool $kedaluwarsa = false): TransaksiPenjualan
    {
        return DB::transaction(function () use ($t, $data, $kedaluwarsa) {
            $t = TransaksiPenjualan::with(['pembayarans', 'kavling', 'konsumen'])->lockForUpdate()->findOrFail($t->id);
            $this->pastikanAktif($t, bolehLunas: true);

            $pokok = $t->pokokTerbayar();
            $potongan = (float) ($data['pokok_potongan'] ?? 0);
            if ($potongan < 0 || $potongan > $pokok) {
                $this->gagal('pokok_potongan', 'Potongan DP & angsuran harus 0 sampai ' . rupiah($pokok) . '.');
            }

            $r = $this->rincianPembatalan($t, $potongan);
            $pembatalan = $t->pembatalan()->create(collect($r)->except('total_dibayar')->all() + [
                'tanggal' => $data['tanggal'], 'alasan' => $data['alasan'] ?? null,
                'dasar_ketentuan' => $data['dasar_ketentuan'] ?? null, 'kedaluwarsa' => $kedaluwarsa,
                'dibuat_oleh' => auth()->id(),
            ]);

            if ($r['total_refund'] > 0) {
                $kas = $this->kas->catatRefund($t, $r['total_refund'], $data['tanggal'], $this->teksRincian($r, $data['dasar_ketentuan'] ?? null), alokasikan: false);
                $pembatalan->update(['kas_transaksi_id' => $kas->id]);
            }
            // Alokasi: balik alokasi uang yang dikembalikan + potongan booking masuk pos Marketing
            $this->alokasi->alokasikanPembatalan($pembatalan);

            $lama = $t->status;
            $t->update(['status' => 'batal', 'tanggal_batal' => $data['tanggal'], 'alasan_batal' => $data['alasan'] ?? null]);
            $t->jadwalAngsurans()->delete();

            $this->riwayat->catat('pembatalan', $t->id, $t->kavling_id, null, null,
                ($kedaluwarsa ? 'Otomatis: batas tahan kavling habis tanpa pembayaran. ' : '') . $this->teksRincian($r, $data['dasar_ketentuan'] ?? null)
                . (! empty($data['alasan']) && ! $kedaluwarsa ? ' Alasan: ' . $data['alasan'] : ''));
            $this->riwayat->catat('pembayaran', $t->id, $t->kavling_id, $lama, 'batal', $data['alasan'] ?? null);

            $this->sinkronKavling($t->kavling);

            return $t;
        });
    }

    /**
     * Transaksi "Menunggu Pembayaran" yang melewati batas tahan dibatalkan otomatis (kedaluwarsa) dan kavlingnya dilepas.
     * Dipanggil setiap ada permintaan halaman (XAMPP tanpa penjadwal) dan oleh perintah tectona:lepas-kedaluwarsa.
     */
    public function lepasKedaluwarsa(): int
    {
        $jumlah = 0;
        TransaksiPenjualan::menunggu()->whereNotNull('batas_tahan')->where('batas_tahan', '<', now())
            ->whereDoesntHave('pembayarans')->get()
            ->each(function (TransaksiPenjualan $t) use (&$jumlah) {
                $this->batal($t, [
                    'tanggal' => now()->toDateString(),
                    'alasan'  => 'Kedaluwarsa: tidak ada pembayaran reservasi sampai ' . tanggal($t->batas_tahan, 'j M Y H:i'),
                ], kedaluwarsa: true);
                $jumlah++;
            });

        return $jumlah;
    }

    private function teksRincian(array $r, ?string $dasar): string
    {
        $bagian = [];
        if ($r['reservasi_dibayar'] > 0) {
            $bagian[] = 'Reservasi ' . rupiah($r['reservasi_dibayar']) . ' dikembalikan penuh';
        }
        if ($r['booking_dibayar'] > 0) {
            $bagian[] = 'Booking ' . rupiah($r['booking_dibayar']) . ' − potongan ' . rupiah($r['booking_potongan']) . ' = ' . rupiah($r['booking_refund']);
        }
        if ($r['pokok_dibayar'] > 0) {
            $bagian[] = 'DP & angsuran ' . rupiah($r['pokok_dibayar']) . ' − potongan ' . rupiah($r['pokok_potongan']) . ' = ' . rupiah($r['pokok_refund']);
        }
        $teks = $bagian ? implode('; ', $bagian) . '. Total dikembalikan ' . rupiah($r['total_refund']) . '.' : 'Tidak ada uang masuk.';

        return $teks . ($dasar ? ' Dasar: ' . $dasar : '');
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

    /** Hitung ulang status pembayaran dari uang yang masuk, lalu status kavling. Perubahan dicatat di riwayat. */
    public function sinkron(TransaksiPenjualan $t, ?string $catatan = null): void
    {
        $t = $t->fresh(['pembayarans', 'kavling']);

        if (! $t->isBatal()) {
            $lama = $t->status;
            $baru = $this->hitungStatus($t);
            if ($lama !== $baru) {
                $ubah = ['status' => $baru];
                // Kembali ke "menunggu" (mis. satu-satunya pembayaran dihapus) → batas tahan dimulai lagi
                if ($baru === 'menunggu') {
                    $ubah['batas_tahan'] = now()->addHours(max(1, (int) Pengaturan::get('batas_tahan_jam', 48)));
                }
                $t->update($ubah);
                $this->riwayat->catat('pembayaran', $t->id, $t->kavling_id, $lama, $baru, $catatan);
            }
        }

        $this->sinkronKavling($t->kavling);
    }

    /**
     * Status pembayaran = tahap terjauh yang sudah dibayar (tahap boleh dilewati, DP boleh 0):
     * lunas > angsuran > dp > booking > reservasi (terbayar) > menunggu (belum ada uang masuk).
     */
    public function hitungStatus(TransaksiPenjualan $t): string
    {
        $ada = fn (string $jenis) => $t->terbayarJenis($jenis) > 0;

        return match (true) {
            $t->nilai_jual > 0 && $t->sisa() <= 0          => 'lunas',
            $ada('angsuran') || ($t->isAngsuran() && $ada('pelunasan')) => 'angsuran',
            $ada('dp') || $ada('pelunasan')                => 'dp',
            $ada('booking')                                => 'booking',
            $ada('reservasi')                              => 'reservasi',
            default                                        => 'menunggu',
        };
    }

    /**
     * Status UNIT kavling dari transaksi aktifnya:
     * tidak ada transaksi → Tersedia; menunggu/reservasi → Reservasi; booking → Booking;
     * DP/angsuran → DP/Angsuran; lunas → Lunas.
     * Terjual bila lunas + PPJB, atau sesuai Pengaturan "terjual_saat" (PPJB ditandatangani / lunas).
     */
    public function sinkronKavling(Kavling $kavling): void
    {
        $terjualSebelum = $this->harga->jumlahTerjual();
        $kavling->refresh();

        $aktif = $kavling->transaksiAktif()->with('checklist')->first();
        $status = 'tersedia';
        if ($aktif) {
            $ppjb = (bool) $aktif->checklist?->ppjbDitandatangani();
            $lunas = $aktif->status === 'lunas';
            $penentu = Pengaturan::get('terjual_saat', 'ppjb');
            // Transaksi "menunggu" belum menerima uang: tidak pernah dihitung terjual (tidak memengaruhi tahap harga)
            $terjual = ! $aktif->isMenunggu() && (($ppjb && $lunas) || ($penentu === 'ppjb' && $ppjb) || ($penentu === 'lunas' && $lunas));

            $status = $terjual ? 'terjual' : match ($aktif->status) {
                'menunggu', 'reservasi' => 'reservasi',
                'booking'               => 'booking',
                'lunas'                 => 'lunas',
                default                 => 'dp',
            };
        }

        if ($kavling->status !== $status) {
            $lama = $kavling->status;
            $kavling->update(['status' => $status]);
            $this->riwayat->catat('kavling', $aktif?->id ?? $kavling->transaksiPenjualans()->latest('id')->value('id'), $kavling->id, $lama, $status);
        }

        // Jumlah terjual berubah → tahap harga bisa naik/turun → harga kavling tersedia diperbarui
        if ($status === 'tersedia' || $this->harga->jumlahTerjual() !== $terjualSebelum) {
            $this->harga->sinkronHargaKavling();
        }
    }

    /** Hitung ulang status semua kavling (mis. setelah Pengaturan "terjual_saat" diubah). */
    public function sinkronSemuaKavling(): void
    {
        Kavling::all()->each(fn (Kavling $k) => $this->sinkronKavling($k));
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
