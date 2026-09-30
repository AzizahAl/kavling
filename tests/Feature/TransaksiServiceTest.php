<?php

namespace Tests\Feature;

use App\Models\KasTransaksi;
use App\Models\Kavling;
use App\Models\Konsumen;
use App\Models\TransaksiPenjualan;
use App\Services\HargaService;
use App\Services\Pengaturan;
use App\Services\TransaksiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransaksiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private TransaksiService $svc;
    private int $urut = 0;

    protected function setUp(): void
    {
        parent::setUp();
        Pengaturan::lupakan();
        $this->svc = app(TransaksiService::class);
    }

    private function konsumen(string $nik = '3205000000000001'): Konsumen
    {
        return Konsumen::create([
            'id_konsumen' => 'CUS-2026-' . substr($nik, -4), 'nama_lengkap' => 'Uji ' . $nik,
            'nik' => $nik, 'no_hp' => '0812', 'alamat' => 'Garut',
        ]);
    }

    private function buat(string $kode = 'TR-A01', array $ubah = [], ?array $bayar = null): TransaksiPenjualan
    {
        return $this->svc->buat(array_merge([
            'konsumen_id' => $this->konsumen('320500000000' . str_pad((string) ++$this->urut, 4, '0', STR_PAD_LEFT))->id,
            'kavling_id' => Kavling::where('kode_kavling', $kode)->value('id'),
            'tanggal' => '2026-10-01', 'jenis_pembayaran' => 'angsuran', 'tenor' => 18, 'nominal_dp' => 7350000,
        ], $ubah), $bayar);
    }

    private function bayar(TransaksiPenjualan $t, string $jenis, float $nominal, string $tgl = '2026-10-01')
    {
        return $this->svc->catatPembayaran($t, ['tanggal' => $tgl, 'jenis' => $jenis, 'nominal' => $nominal, 'metode' => 'transfer']);
    }

    public function test_seeder_sesuai_excel(): void
    {
        $this->assertSame(14, Kavling::count());
        $this->assertSame(7, Kavling::where('tipe', 'Prima')->count());
        $this->assertNull(Kavling::firstWhere('kode_kavling', 'TR-B07')->luas);
        $this->assertEquals(49000000, Kavling::firstWhere('kode_kavling', 'TR-A01')->harga_jual);
        $this->assertEquals([500000, 550000, 600000, 650000, 700000], \App\Models\SkemaHarga::orderBy('unit_mulai')->pluck('harga_per_m2')->map(fn ($h) => (int) $h)->all());
    }

    public function test_harga_terkunci_dan_jadwal_angsuran(): void
    {
        $t = $this->buat();
        $this->assertEquals(49000000, $t->nilai_jual);
        $this->assertEquals('reservasi', $t->status);
        $this->assertSame('reservasi', $t->kavling->fresh()->status);

        $jadwal = $t->jadwalAngsurans()->get();
        $this->assertCount(18, $jadwal);
        $this->assertEquals(41650000, $jadwal->sum('nominal'));            // 49 jt − DP 7,35 jt
        $this->assertSame('2026-11-01', $jadwal->first()->jatuh_tempo->toDateString());
        $this->assertEquals(2313888, $jadwal->first()->nominal);
        $this->assertEquals(41650000 - 2313888 * 17, $jadwal->last()->nominal); // sisa pembulatan di cicilan terakhir
    }

    public function test_pembayaran_masuk_kas_dan_status_mengikuti(): void
    {
        $t = $this->buat();
        $this->bayar($t, 'reservasi', 500000);
        $this->bayar($t, 'booking', 2000000);
        $this->assertSame('booking', $t->fresh()->status);

        $this->bayar($t, 'dp', 7350000);
        $t->refresh();
        $this->assertSame('dp', $t->status);
        $this->assertSame('dp', $t->kavling->status);
        $this->assertEquals(7350000, $t->pokokTerbayar());           // reservasi & booking di luar harga
        $this->assertEquals(49000000 - 7350000, $t->sisa());
        $this->assertEquals(9850000, $t->totalMasuk());

        $this->assertSame(3, KasTransaksi::where('asal', 'pembayaran')->count());
        $this->assertEquals(9850000, KasTransaksi::where('jenis', 'masuk')->sum('nominal'));

        $this->bayar($t, 'angsuran', 2313888, '2026-11-01');
        $this->assertSame('angsuran', $t->fresh()->status);
    }

    public function test_langsung_angsuran_tanpa_dp_dan_lunas(): void
    {
        $t = $this->buat('TR-B01', ['nominal_dp' => 0, 'tenor' => 2]);
        $this->assertEquals(35000000, $t->nilai_jual);
        $this->bayar($t, 'angsuran', 17500000);
        $this->assertSame('angsuran', $t->fresh()->status);
        $this->bayar($t, 'angsuran', 17500000);
        $t->refresh();
        $this->assertSame('lunas', $t->status);
        $this->assertSame('dp', $t->kavling->status, 'Lunas tapi belum PPJB: kavling belum terjual');
    }

    public function test_validasi_aturan(): void
    {
        $this->expectValidasi(fn () => $this->buat('TR-A01', ['tenor' => 19]), 'tenor');
        $this->expectValidasi(fn () => $this->buat('TR-B07'), 'kavling_id');   // luas belum final

        $t = $this->buat('TR-A02');
        $this->expectValidasi(fn () => $this->buat('TR-A02'), 'kavling_id');   // 1 transaksi aktif per kavling
        $this->expectValidasi(fn () => $this->bayar($t, 'reservasi', 600000), 'nominal');
        $this->expectValidasi(fn () => $this->bayar($t, 'dp', 49000001), 'nominal');

        $c = $this->buat('TR-A03', ['jenis_pembayaran' => 'cash', 'tenor' => null, 'nominal_dp' => 0]);
        $this->expectValidasi(fn () => $this->bayar($c, 'angsuran', 1000), 'jenis');
    }

    public function test_hapus_pembayaran_menghapus_kas(): void
    {
        $t = $this->buat();
        $p = $this->bayar($t, 'booking', 2000000);
        $this->assertSame('booking', $t->fresh()->status);
        $this->svc->hapusPembayaran($p);
        $this->assertSame('reservasi', $t->fresh()->status);
        $this->assertSame(0, KasTransaksi::where('asal', 'pembayaran')->count());
    }

    public function test_batal_refund_dan_kavling_kembali_tersedia(): void
    {
        Pengaturan::simpan(['refund_dp_persen' => 50, 'refund_angsuran_persen' => 0]);
        $t = $this->buat();
        $this->bayar($t, 'reservasi', 500000);
        $this->bayar($t, 'booking', 2000000);
        $this->bayar($t, 'dp', 7350000);

        $r = $this->svc->rincianRefund($t->fresh());
        $this->assertEquals(500000 + 1000000 + 3675000, $r['total_refund']);

        $this->svc->batal($t, '2026-10-10', 'Konsumen mundur');
        $t->refresh();
        $this->assertSame('batal', $t->status);
        $this->assertSame('tersedia', $t->kavling->status);
        $this->assertEquals(5175000, KasTransaksi::where('asal', 'refund')->sum('nominal'));

        // Kavling bisa dijual lagi dengan transaksi baru
        $baru = $this->buat();
        $this->assertSame('reservasi', $baru->status);
    }

    public function test_batal_ditolak_bila_aturan_refund_kosong(): void
    {
        $t = $this->buat();
        $this->bayar($t, 'dp', 1000000);
        $this->expectValidasi(fn () => $this->svc->batal($t, '2026-10-10', 'x'), 'alasan');
    }

    public function test_harga_naik_setelah_3_ppjb_dan_harga_lama_terkunci(): void
    {
        $harga = app(HargaService::class);
        $trx = collect(['TR-A01', 'TR-A02', 'TR-A03'])->map(fn ($k) => $this->buat($k));
        $lain = $this->buat('TR-A04');

        foreach ($trx as $t) {
            $t->checklist->update(['ppjb_status' => 'selesai', 'ppjb_tanggal' => '2026-10-05']);
            $this->svc->sinkronKavling($t->kavling);
        }

        $this->assertSame(3, $harga->jumlahTerjual());
        $this->assertSame(550000, $harga->hargaAktif());
        $this->assertEquals(53900000, Kavling::firstWhere('kode_kavling', 'TR-A05')->harga_jual);
        $this->assertEquals(49000000, $lain->fresh()->nilai_jual, 'Harga transaksi lama tidak ikut naik');
    }

    private function expectValidasi(callable $fn, string $field): void
    {
        try {
            $fn();
            $this->fail("Seharusnya gagal validasi pada {$field}");
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($field, $e->errors(), json_encode($e->errors()));
        }
    }
}
