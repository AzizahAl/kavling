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

    private function bayar(TransaksiPenjualan $t, string $jenis, float $nominal): void
    {
        app(TransaksiService::class)->catatPembayaran($t, ['tanggal' => '2026-06-01', 'jenis' => $jenis, 'nominal' => $nominal, 'metode' => 'transfer']);
    }

    public function test_spk_hanya_setelah_booking_terbayar(): void
    {
        $t = $this->transaksi();
        $form = ['spk_status' => 'selesai', 'spk_tanggal' => '2026-06-02', 'ppjb_status' => 'belum', 'ajb_status' => 'belum'];

        $this->assertNotNull($t->alasanSpkBelumBisa());
        $this->put(route('legal.update', $t->checklist), $form)->assertSessionHasErrors('spk_status');
        $this->get(route('dokumen.lihat', [$t, 'spk']))->assertRedirect(route('transaksi-penjualan.show', $t));
        $this->get(route('transaksi-penjualan.show', $t))->assertOk()->assertSee('SPK dibuat setelah booking fee terbayar.');

        $this->bayar($t, 'reservasi', 500000);
        $this->put(route('legal.update', $t->checklist), $form)->assertSessionHasErrors('spk_status');

        $this->bayar($t, 'booking', 2000000);
        $this->assertNull($t->fresh()->alasanSpkBelumBisa());
        $this->put(route('legal.update', $t->checklist), $form)->assertSessionHasNoErrors();
        $this->get(route('dokumen.lihat', [$t, 'spk']))->assertOk();
        $this->assertTrue(\App\Models\StatusRiwayat::where('transaksi_id', $t->id)->where('jenis', 'dokumen')->where('item', 'spk')->where('ke', 'selesai')->exists());
    }

    public function test_ppjb_selesai_membuat_kavling_terjual_lewat_checklist(): void
    {
        $t = $this->transaksi();
        $this->bayar($t, 'reservasi', 500000);
        $this->bayar($t, 'booking', 2000000);
        $form = ['spk_status' => 'selesai', 'spk_tanggal' => '2026-06-02', 'ppjb_status' => 'selesai', 'ppjb_tanggal' => null, 'ajb_status' => 'belum'];
        $this->put(route('legal.update', $t->checklist), $form)->assertSessionHasErrors('ppjb_tanggal');

        $this->put(route('legal.update', $t->checklist), ['ppjb_tanggal' => '2026-06-10'] + $form)->assertSessionHasNoErrors();

        $this->assertSame('terjual', $t->kavling->fresh()->status, 'Penentu terjual bawaan = PPJB');
        $this->assertSame(1, app(HargaService::class)->jumlahTerjual());

        // Penentu diganti ke "lunas": PPJB tanpa lunas → kembali Booking
        Pengaturan::simpan(['terjual_saat' => 'lunas']);
        app(TransaksiService::class)->sinkronSemuaKavling();
        $this->assertSame('booking', $t->kavling->fresh()->status);
    }

    public function test_dokumen_spk_ppjb_dan_halaman_terbuka(): void
    {
        $t = $this->transaksi();
        $this->bayar($t, 'booking', 2000000);
        $this->get(route('dokumen.lihat', [$t, 'spk']))->assertOk()->assertSee('SURAT PEMESANAN KAVLING (SPK)')->assertSee('TR/SPK/2026/0001')->assertSee('49.000.000');
        $this->get(route('dokumen.lihat', [$t, 'ppjb']))->assertOk()->assertSee('TR/PPJB/2026/0001')->assertSee('JADWAL ANGSURAN')
            ->assertSee('LAMPIRAN A')->assertSee('LAMPIRAN D')->assertDontSee('Form Pemeriksaan Dokumen Pembeli');
        $this->get(route('dokumen.unduh', [$t, 'ppjb']))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('dokumen.lihat', [$t, 'lain']))->assertNotFound();
        $this->get(route('dokumen.unduh', [$t, 'reservasi']))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('legal.index'))->assertOk();
        $this->get(route('angsuran.index'))->assertOk()->assertSee($t->konsumen->nama_lengkap);
        $this->get(route('angsuran.index', ['status' => 'terlambat']))->assertOk()->assertSee($t->konsumen->nama_lengkap); // jatuh tempo Juli 2026 sudah lewat
    }

    public function test_form_reservasi_dan_booking_terisi_dari_transaksi(): void
    {
        $t = $this->transaksi();
        app(TransaksiService::class)->catatPembayaran($t, [
            'tanggal' => '2026-06-01', 'jenis' => 'reservasi', 'nominal' => 500000, 'metode' => 'qris',
            'nama_penyetor' => 'Penyetor Uji', 'bank_penyetor' => 'BRI', 'rekening_penyetor' => '1234567890',
        ]);

        // Berlaku s/d = tanggal bayar reservasi + 14 hari
        $this->get(route('dokumen.lihat', [$t, 'reservasi']))->assertOk()
            ->assertSee('Form Reservasi / Pemesanan')->assertSee('TR/RSV/2026/0001')
            ->assertSee('15 Juni 2026')->assertSee('Lima Ratus Ribu Rupiah')
            ->assertSee('Penyetor Uji')->assertSee('1234567890')->assertSee('Syarat Dan Ketentuan');

        $this->get(route('dokumen.lihat', [$t, 'booking']))->assertOk()
            ->assertSee('FORM BOOKING')->assertSee('TR/BKG/2026/0001')->assertSee('Rp2.000.000')->assertSee('/bulan');
    }

    public function test_formulir_kosong_dan_toolkit_terbuka(): void
    {
        $this->get(route('formulir.index'))->assertOk()->assertSee('Marketing Toolkit');
        foreach (array_keys(\App\Support\Formulir::DAFTAR) as $jenis) {
            $this->get(route('formulir.lihat', $jenis))->assertOk();
        }
        $this->get(route('formulir.lihat', 'reservasi'))->assertSee('TR/RSV/20…/……');
        $this->get(route('formulir.lihat', 'toolkit'))->assertSee('SALES CLOSING SHEET')->assertDontSee('>FORM RESERVASI<', false);
        $this->get(route('formulir.unduh', 'ppjb'))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('formulir.lihat', 'tidak-ada'))->assertNotFound();
    }
}
