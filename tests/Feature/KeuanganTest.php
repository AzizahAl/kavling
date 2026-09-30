<?php

namespace Tests\Feature;

use App\Models\AlokasiKas;
use App\Models\KasTransaksi;
use App\Models\Kavling;
use App\Models\Konsumen;
use App\Models\Rab;
use App\Models\TransaksiPenjualan;
use App\Services\AlokasiService;
use App\Services\Pengaturan;
use App\Services\TransaksiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeuanganTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        Pengaturan::lupakan();
        $this->withoutVite();
    }

    private function transaksi(): TransaksiPenjualan
    {
        $k = Konsumen::create(['id_konsumen' => 'CUS-1', 'nama_lengkap' => 'A', 'nik' => '3205000000000001', 'no_hp' => '0812', 'alamat' => 'Garut']);

        return app(TransaksiService::class)->buat([
            'konsumen_id' => $k->id, 'kavling_id' => Kavling::firstWhere('kode_kavling', 'TR-A01')->id,
            'tanggal' => '2026-09-01', 'jenis_pembayaran' => 'cash', 'nominal_dp' => 0,
        ]);
    }

    public function test_setiap_uang_masuk_dialokasikan_sesuai_persen(): void
    {
        $t = $this->transaksi();
        $svc = app(TransaksiService::class);
        $p = $svc->catatPembayaran($t, ['tanggal' => '2026-09-02', 'jenis' => 'booking', 'nominal' => 2000000, 'metode' => 'tunai']);

        $a = AlokasiKas::pluck('nominal', 'pos')->map(fn ($n) => (int) $n)->all();
        $this->assertSame(['cadangan' => 200000, 'legal_infra' => 500000, 'marketing' => 200000, 'operasional' => 100000, 'tanah' => 1000000], collect($a)->sortKeys()->all());

        // Persen di Pengaturan berubah → alokasi pembayaran lama tidak ikut berubah, pembayaran baru ikut
        Pengaturan::simpan(['alokasi_tanah' => 40, 'alokasi_cadangan' => 20]);
        $svc->ubahPembayaran($p, ['tanggal' => '2026-09-02', 'jenis' => 'booking', 'nominal' => 2000000, 'metode' => 'transfer']);
        $this->assertEquals(1000000, AlokasiKas::where('pos', 'tanah')->sum('nominal'));

        $svc->catatPembayaran($t, ['tanggal' => '2026-09-03', 'jenis' => 'pelunasan', 'nominal' => 1000001, 'metode' => 'tunai']);
        $kas = KasTransaksi::where('asal', 'pembayaran')->latest('id')->first();
        $this->assertEquals(1000001, $kas->alokasis()->sum('nominal'), 'Pembulatan tetap menjumlah persis');
        $this->assertEquals(400000, $kas->alokasis()->where('pos', 'tanah')->value('nominal'));
    }

    public function test_refund_mengurangi_alokasi_seimbang(): void
    {
        Pengaturan::simpan(['refund_dp_persen' => 100, 'refund_angsuran_persen' => 100]);
        $t = $this->transaksi();
        $svc = app(TransaksiService::class);
        $svc->catatPembayaran($t, ['tanggal' => '2026-09-02', 'jenis' => 'reservasi', 'nominal' => 500000, 'metode' => 'tunai']);
        $svc->batal($t, '2026-09-05', 'Batal');

        $this->assertEquals(0, AlokasiKas::sum('nominal'));
        $this->assertEquals(0, AlokasiKas::where('pos', 'tanah')->sum('nominal'));
    }

    public function test_kelayakan_laba(): void
    {
        $svc = app(AlokasiService::class);
        $t = $this->transaksi();
        app(TransaksiService::class)->catatPembayaran($t, ['tanggal' => '2026-09-02', 'jenis' => 'pelunasan', 'nominal' => 40000000, 'metode' => 'transfer']);

        $l = $svc->kelayakanLaba();
        $this->assertFalse($l['layak']);
        $this->assertNotNull($l['tanah']['catatan']);       // target tanah belum diisi
        $this->assertEquals(40000000 - 500000, $l['laba']); // dikurangi kas keluar banner dari Excel

        Pengaturan::simpan(['target_kewajiban_tanah' => 20000000]);
        Rab::where('kategori', 'Legalitas')->update(['anggaran' => 10000000]);
        $l = $svc->kelayakanLaba();
        $this->assertTrue($l['tanah']['terpenuhi']);
        $this->assertTrue($l['legal']['terpenuhi']);
        $this->assertTrue($l['layak']);
        $this->assertEquals(round(39500000 * 0.8), $l['bagian_pengelola']);
        $this->assertEquals(round(39500000 * 0.2), $l['bagian_pemilik']);
    }

    public function test_realisasi_rab_dari_kas_keluar(): void
    {
        $rab = Rab::firstWhere('kategori', 'Marketing');
        $this->assertEquals(500000, Rab::denganRealisasi()->find($rab->id)->realisasi_nilai);
        $this->assertSame('sesuai', Rab::denganRealisasi()->find($rab->id)->status);

        $this->post(route('kas-proyek.store'), [
            'tanggal' => now()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Marketing', 'rab_id' => $rab->id,
            'uraian' => 'Brosur', 'nominal' => 100000,
        ])->assertSessionHasNoErrors();

        $k = KasTransaksi::latest('id')->first();
        $this->assertSame('marketing', $k->pos, 'Pos mengikuti item RAB');
        $this->assertSame('melebihi', Rab::denganRealisasi()->find($rab->id)->status);

        $this->post(route('kas-proyek.store'), ['tanggal' => now()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Lain', 'uraian' => 'x', 'nominal' => 1])
            ->assertSessionHasErrors('pos');

        $this->delete(route('rab.destroy', $rab))->assertSessionHas('error');
    }

    public function test_halaman_keuangan_terbuka(): void
    {
        $t = $this->transaksi();
        app(TransaksiService::class)->catatPembayaran($t, ['tanggal' => '2026-09-02', 'jenis' => 'dp', 'nominal' => 5000000, 'metode' => 'transfer']);
        foreach ([route('kas-proyek.index'), route('kas-proyek.index', ['jenis' => 'keluar', 'pos' => 'marketing']), route('rab.index'), route('cashflow.index')] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
