<?php

namespace Tests\Feature;

use App\Models\Agen;
use App\Models\KasTransaksi;
use App\Models\Kavling;
use App\Models\Konsumen;
use App\Models\Lead;
use App\Models\TransaksiPenjualan;
use App\Services\KomisiService;
use App\Services\LeadService;
use App\Services\Pengaturan;
use App\Services\TransaksiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LeadKomisiTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private Agen $agen;
    private LeadService $lead;

    protected function setUp(): void
    {
        parent::setUp();
        Pengaturan::lupakan();
        $this->withoutVite();
        $this->agen = Agen::create(['kode_agen' => 'AG-001', 'nama_agen' => 'Sari', 'komisi_persen' => 2.5]);
        $this->lead = app(LeadService::class);
    }

    private function transaksi(?int $agenId = null, string $kode = 'TR-A01'): TransaksiPenjualan
    {
        $k = Konsumen::create(['id_konsumen' => 'CUS-' . $kode, 'nama_lengkap' => 'K ' . $kode, 'nik' => str_pad((string) crc32($kode), 16, '0'), 'no_hp' => '0812', 'alamat' => 'Garut']);

        return app(TransaksiService::class)->buat([
            'konsumen_id' => $k->id, 'kavling_id' => Kavling::firstWhere('kode_kavling', $kode)->id, 'agen_id' => $agenId,
            'tanggal' => '2026-09-10', 'jenis_pembayaran' => 'cash', 'nominal_dp' => 0,
        ]);
    }

    private function leadBaru(string $tgl = '2026-09-01'): Lead
    {
        return $this->lead->buat(['nama' => 'Budi', 'sumber' => 'flyer', 'tanggal_lead' => $tgl, 'agen_id' => $this->agen->id], null);
    }

    public function test_alur_lead_prospek_closing_dan_rekap(): void
    {
        $l = $this->leadBaru();
        $this->assertSame('LD-2026-0001', $l->kode);

        // Closing tidak boleh langsung dari lead
        $t = $this->transaksi();
        $this->gagal(fn () => $this->lead->keClosing($l, $t->id, '2026-09-10', null, null), 'tahap');

        $this->gagal(fn () => $this->lead->keProspek($l, '2026-08-30', null, null), 'tanggal');
        $this->lead->keProspek($l, '2026-09-05', null, null);
        $this->lead->keClosing($l->fresh(), $t->id, '2026-09-10', null, null);

        $l->refresh();
        $this->assertSame('closing', $l->tahap);
        $this->assertSame($this->agen->id, $t->fresh()->agen_id, 'Transaksi tanpa agen diisi agen lead');
        $this->assertSame(3, $l->riwayats()->count());

        // Rekap: masing-masing tahap dihitung di tanggalnya
        $r = fn ($a, $b) => $this->lead->rekapPerAgen(Carbon::parse($a), Carbon::parse($b))->first();
        $this->assertSame([1, 0, 0], array_values(array_slice($r('2026-09-01', '2026-09-01'), 1)));
        $this->assertSame([0, 1, 0], array_values(array_slice($r('2026-09-05', '2026-09-05'), 1)));
        $this->assertSame([1, 1, 1], array_values(array_slice($r('2026-09-01', '2026-09-30'), 1)));

        // Transaksi yang sudah dipakai tidak bisa dipakai lead lain
        $l2 = $this->leadBaru();
        $this->lead->keProspek($l2, '2026-09-02', null, null);
        $this->gagal(fn () => $this->lead->keClosing($l2, $t->id, '2026-09-10', null, null), 'transaksi_id');

        // Mundur melepas tautan transaksi
        $this->lead->mundur($l, null);
        $this->assertNull($l->fresh()->transaksi_id);
        $this->assertSame('prospek', $l->fresh()->tahap);
    }

    public function test_closing_ditolak_bila_transaksi_milik_agen_lain(): void
    {
        $lain = Agen::create(['kode_agen' => 'AG-002', 'nama_agen' => 'Dodi']);
        $t = $this->transaksi($lain->id);
        $l = $this->leadBaru();
        $this->lead->keProspek($l, '2026-09-02', null, null);
        $this->gagal(fn () => $this->lead->keClosing($l, $t->id, '2026-09-10', null, null), 'transaksi_id');
    }

    public function test_komisi_jadi_hak_setelah_ppjb_dan_pembayaran_masuk_kas(): void
    {
        $svc = app(KomisiService::class);
        $t = $this->transaksi($this->agen->id);

        $r = $svc->ringkasan($this->agen);
        $this->assertEquals(49000000, $r['nilai_penjualan']);
        $this->assertEquals(1225000, $r['komisi_potensi']);   // 2,5% × 49 jt
        $this->assertEquals(0, $r['komisi_hak']);
        $this->gagal(fn () => $svc->bayar($this->agen, ['tanggal' => '2026-09-20', 'nominal' => 1000, 'metode' => 'tunai'], null), 'nominal');

        $t->checklist->update(['ppjb_status' => 'selesai']);
        app(TransaksiService::class)->sinkronKavling($t->kavling);
        $this->assertEquals(1225000, $svc->ringkasan($this->agen)['komisi_hak']);

        $svc->bayar($this->agen, ['tanggal' => '2026-09-20', 'nominal' => 500000, 'metode' => 'transfer'], null);
        $r = $svc->ringkasan($this->agen);
        $this->assertEquals(725000, $r['sisa']);
        $this->assertEquals(500000, KasTransaksi::where('asal', 'komisi')->sum('nominal'));
        $this->gagal(fn () => $svc->bayar($this->agen, ['tanggal' => '2026-09-21', 'nominal' => 800000, 'metode' => 'tunai'], null), 'nominal');

        $svc->hapusBayar($this->agen->komisiPembayarans()->first());
        $this->assertSame(0, KasTransaksi::where('asal', 'komisi')->count());
    }

    public function test_komisi_bawaan_dari_pengaturan(): void
    {
        $a = Agen::create(['kode_agen' => 'AG-009', 'nama_agen' => 'Tanpa Persen']);
        $this->assertNull($a->persenKomisi());
        Pengaturan::simpan(['komisi_default_persen' => 3]);
        $this->assertEquals(3, $a->persenKomisi());
    }

    public function test_halaman_lead_dan_agen_terbuka(): void
    {
        $l = $this->leadBaru(now()->toDateString());
        foreach ([route('lead.index'), route('lead.rekap'), route('lead.rekap', ['mode' => 'harian']), route('lead.rekap', ['mode' => 'bulanan']),
                  route('agen.index'), route('agen.show', $this->agen)] as $url) {
            $this->get($url)->assertOk();
        }
        $this->post(route('lead.prospek', $l), ['tanggal' => now()->toDateString()])->assertSessionHasNoErrors();
        $this->post(route('agen.store'), ['nama_agen' => 'Baru', 'komisi_persen' => ''])->assertSessionHasNoErrors();
        $this->assertSame('AG-002', Agen::latest('id')->value('kode_agen'));
    }

    private function gagal(callable $fn, string $field): void
    {
        try {
            $fn();
            $this->fail("Seharusnya gagal pada {$field}");
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($field, $e->errors(), json_encode($e->errors()));
        }
    }
}
