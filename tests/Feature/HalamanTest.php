<?php

namespace Tests\Feature;

use App\Models\KasTransaksi;
use App\Models\Kavling;
use App\Models\Konsumen;
use App\Models\TransaksiPenjualan;
use App\Services\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HalamanTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        Pengaturan::lupakan();
        $this->withoutVite();
    }

    private function transaksiBaru(): TransaksiPenjualan
    {
        $this->post(route('transaksi-penjualan.store'), [
            'konsumen_mode' => 'baru',
            'konsumen_nama_lengkap' => 'Azizah', 'konsumen_nik' => '3205366111050001',
            'konsumen_no_hp' => '082127440877', 'konsumen_alamat' => 'Garut',
            'kavling_id' => Kavling::firstWhere('kode_kavling', 'TR-A01')->id,
            'tanggal' => now()->toDateString(), 'jenis_pembayaran' => 'angsuran', 'tenor' => 12, 'nominal_dp' => 7350000,
            'bayar_jenis' => 'reservasi', 'bayar_nominal' => 500000, 'bayar_metode' => 'transfer',
        ])->assertSessionHasNoErrors()->assertRedirect();

        return TransaksiPenjualan::firstOrFail();
    }

    public function test_semua_halaman_terbuka(): void
    {
        $t = $this->transaksiBaru();
        $p = $t->pembayarans()->first();

        foreach ([
            route('dashboard'), route('proyek.index'), route('kavling.index'), route('kavling.show', $t->kavling_id),
            route('skema-harga.index'), route('konsumen.index'), route('konsumen.show', $t->konsumen_id),
            route('transaksi-penjualan.index'), route('transaksi-penjualan.create'), route('transaksi-penjualan.show', $t),
            route('transaksi-penjualan.edit', $t), route('pembayaran.kwitansi', $p),
            route('kas-proyek.index'), route('rab.index'), route('agen.index'), route('kewajiban-tanah.index'),
            route('cashflow.index'), route('legal.index'), route('transaksi-penjualan.index', ['status' => 'menunggu']),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        // Transaksi menunggu pembayaran: tampil terpisah dengan sisa waktu tahan
        $tunggu = app(\App\Services\TransaksiService::class)->buat([
            'konsumen_id' => $t->konsumen_id, 'kavling_id' => \App\Models\Kavling::firstWhere('kode_kavling', 'TR-A05')->id,
            'tanggal' => now()->toDateString(), 'jenis_pembayaran' => 'cash', 'nominal_dp' => 0,
        ]);
        $this->get(route('transaksi-penjualan.index'))->assertOk()->assertSee('Menunggu Pembayaran Reservasi')->assertSee($tunggu->kode_transaksi);
        $this->get(route('transaksi-penjualan.show', $tunggu))->assertOk()->assertSee('kavling ditahan sampai')->assertSee('hitungMundur');
        $this->get(route('dashboard'))->assertOk()->assertSee($tunggu->kode_transaksi);

        $this->get(route('pembayaran.kwitansi.unduh', $p))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->getJson(route('konsumen.cari', ['q' => 'Aziz']))->assertOk()->assertJsonCount(1);
    }

    public function test_alur_form_transaksi(): void
    {
        $t = $this->transaksiBaru();
        $this->assertSame('reservasi', $t->status);
        $this->assertSame('reservasi', $t->kavling->status);
        $this->assertSame(1, KasTransaksi::where('asal', 'pembayaran')->count());

        $this->post(route('pembayaran.store', $t), ['tanggal' => now()->toDateString(), 'jenis' => 'dp', 'nominal' => 7350000, 'metode' => 'tunai'])
            ->assertSessionHasNoErrors();
        $this->assertSame('dp', $t->fresh()->status);

        // Pembayaran berlebih ditolak dengan pesan
        $this->post(route('pembayaran.store', $t), ['tanggal' => now()->toDateString(), 'jenis' => 'pelunasan', 'nominal' => 99000000, 'metode' => 'tunai'])
            ->assertSessionHasErrors('nominal');

        // Ubah tenor → jadwal disusun ulang
        $this->put(route('transaksi-penjualan.update', $t), [
            'konsumen_id' => $t->konsumen_id, 'tanggal' => $t->tanggal->toDateString(),
            'jenis_pembayaran' => 'angsuran', 'tenor' => 6, 'nominal_dp' => 7350000,
        ])->assertSessionHasNoErrors();
        $this->assertSame(6, $t->jadwalAngsurans()->count());

        // Baris kas otomatis tidak bisa dihapus manual
        $kas = KasTransaksi::where('asal', 'pembayaran')->first();
        $this->delete(route('kas-proyek.destroy', $kas))->assertSessionHas('error');
        $this->assertModelExists($kas);

        // Pembatalan: form menampilkan rincian; potongan melebihi DP+angsuran ditolak
        $this->get(route('transaksi-penjualan.show', $t))->assertOk()->assertSee('Potongan DP &amp; angsuran', false)->assertSee('Rp7.350.000');
        $batal = ['tanggal_batal' => now()->toDateString(), 'alasan' => 'Mundur', 'dasar_ketentuan' => 'PPJB Pasal 4 ayat 2'];
        $this->post(route('transaksi-penjualan.batal', $t), $batal + ['pokok_potongan' => 8000000])->assertSessionHasErrors('pokok_potongan');
        $this->post(route('transaksi-penjualan.batal', $t), $batal + ['pokok_potongan' => 1000000])->assertSessionHasNoErrors();
        $this->assertSame('tersedia', $t->kavling->fresh()->status);
        $this->assertEquals(500000 + 6350000, KasTransaksi::where('asal', 'refund')->sum('nominal'), 'Reservasi penuh + DP − potongan');
        $this->get(route('transaksi-penjualan.show', $t))->assertOk()->assertSee('PPJB Pasal 4 ayat 2')->assertSee('Riwayat Status');
    }

    public function test_pengaturan_menolak_alokasi_bukan_100_persen(): void
    {
        $this->put(route('proyek.update'), array_merge($this->pengaturanForm(), ['alokasi_tanah' => 60]))
            ->assertSessionHasErrors('alokasi_tanah');
    }

    public function test_pengaturan_menerima_koma_desimal(): void
    {
        // Sebelumnya kolom bertipe number membuang koma: 17,34 tersimpan 1734
        $this->put(route('proyek.update'), array_merge($this->pengaturanForm(), ['luas_lahan_are' => '17,34', 'komisi_nominal' => '1500000', 'terjual_saat' => 'lunas']))
            ->assertSessionHasNoErrors();
        Pengaturan::lupakan();
        $this->assertEquals(17.34, Pengaturan::get('luas_lahan_are'));
        $this->assertEquals(1500000, Pengaturan::get('komisi_nominal'));
        $this->assertSame('lunas', Pengaturan::get('terjual_saat'));

        $this->put(route('proyek.update'), array_merge($this->pengaturanForm(), ['terjual_saat' => 'spk']))->assertSessionHasErrors('terjual_saat');
        $this->put(route('proyek.update'), array_merge($this->pengaturanForm(), ['potongan_booking' => 3000000]))->assertSessionHasErrors('potongan_booking');
        $this->get(route('proyek.index'))->assertOk();
    }

    public function test_konsumen_dengan_transaksi_tidak_bisa_dihapus(): void
    {
        $t = $this->transaksiBaru();
        $this->delete(route('konsumen.destroy', $t->konsumen_id))->assertSessionHas('error');
        $this->assertSame(1, Konsumen::count());
    }

    private function pengaturanForm(): array
    {
        return collect(Pengaturan::semua())->map(fn ($v) => is_array($v) ? implode(', ', $v) : $v)->all();
    }
}
