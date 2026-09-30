<?php

namespace Tests\Feature;

use App\Models\Kavling;
use App\Models\Konsumen;
use App\Models\TransaksiPenjualan;
use App\Services\HargaService;
use App\Services\Pengaturan;
use App\Services\TransaksiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DokumenTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        Pengaturan::lupakan();
        $this->withoutVite();
    }

    private function transaksi(string $kode = 'TR-A01', string $tgl = '2026-06-01'): TransaksiPenjualan
    {
        $k = Konsumen::create(['id_konsumen' => 'CUS-' . $kode, 'nama_lengkap' => 'Konsumen ' . $kode, 'nik' => str_pad((string) crc32($kode), 16, '0'), 'no_hp' => '0812', 'alamat' => 'Garut']);

        return app(TransaksiService::class)->buat([
            'konsumen_id' => $k->id, 'kavling_id' => Kavling::firstWhere('kode_kavling', $kode)->id,
            'tanggal' => $tgl, 'jenis_pembayaran' => 'angsuran', 'tenor' => 6, 'nominal_dp' => 7350000,
        ]);
    }

    public function test_ppjb_selesai_membuat_kavling_terjual_lewat_checklist(): void
    {
        $t = $this->transaksi();
        $this->put(route('legal.update', $t->checklist), [
            'reservasi_status' => 'selesai', 'reservasi_tanggal' => '2026-06-01',
            'spk_status' => 'selesai', 'spk_tanggal' => '2026-06-02',
            'ppjb_status' => 'selesai', 'ppjb_tanggal' => null,
            'ajb_status' => 'belum',
        ])->assertSessionHasErrors('ppjb_tanggal');

        $this->put(route('legal.update', $t->checklist), [
            'reservasi_status' => 'selesai', 'reservasi_tanggal' => '2026-06-01',
            'spk_status' => 'selesai', 'spk_tanggal' => '2026-06-02',
            'ppjb_status' => 'selesai', 'ppjb_tanggal' => '2026-06-10',
            'ajb_status' => 'belum',
        ])->assertSessionHasNoErrors();

        $this->assertSame('terjual', $t->kavling->fresh()->status);
        $this->assertSame(1, app(HargaService::class)->jumlahTerjual());
    }

    public function test_dokumen_spk_ppjb_dan_halaman_terbuka(): void
    {
        $t = $this->transaksi();
        $this->get(route('dokumen.lihat', [$t, 'spk']))->assertOk()->assertSee('SURAT PEMESANAN KAVLING')->assertSee('TR/SPK/2026/0001')->assertSee('Rp49.000.000');
        $this->get(route('dokumen.lihat', [$t, 'ppjb']))->assertOk()->assertSee('TR/PPJB/2026/0001')->assertSee('Jadwal angsuran');
        $this->get(route('dokumen.unduh', [$t, 'ppjb']))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('dokumen.lihat', [$t, 'lain']))->assertNotFound();
        $this->get(route('legal.index'))->assertOk();
        $this->get(route('angsuran.index'))->assertOk()->assertSee($t->konsumen->nama_lengkap);
        $this->get(route('angsuran.index', ['status' => 'terlambat']))->assertOk()->assertSee($t->konsumen->nama_lengkap); // jatuh tempo Juli 2026 sudah lewat
    }
}
