<?php

namespace Tests\Feature;

use App\Models\Agen;
use App\Models\Kavling;
use App\Models\Konsumen;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use App\Services\Pengaturan;
use App\Services\TransaksiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AksesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;
    protected bool $masukSebagaiAdmin = false;

    private Agen $sari;
    private Agen $dodi;
    private User $akunSari;

    protected function setUp(): void
    {
        parent::setUp();
        Pengaturan::lupakan();
        $this->withoutVite();
        $this->sari = Agen::create(['kode_agen' => 'AG-001', 'nama_agen' => 'Sari']);
        $this->dodi = Agen::create(['kode_agen' => 'AG-002', 'nama_agen' => 'Dodi']);
        $this->akunSari = User::create(['name' => 'Sari', 'email' => 'sari@tectona.test', 'password' => 'rahasia123', 'role' => 'agen', 'agen_id' => $this->sari->id]);
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk()->assertSee('Masuk');
    }

    public function test_masuk_dan_keluar(): void
    {
        $this->post(route('login.proses'), ['email' => 'admin@tectona.test', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('login.proses'), ['email' => 'admin@tectona.test', 'password' => 'tectona2026'])->assertRedirect(route('beranda'));
        $this->assertAuthenticated();
        $this->get(route('beranda'))->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))->assertOk();

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        $this->akunSari->update(['aktif' => false]);
        $this->post(route('login.proses'), ['email' => 'sari@tectona.test', 'password' => 'rahasia123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_agen_hanya_melihat_dan_mengelola_miliknya(): void
    {
        $ls = app(LeadService::class);
        $milikDodi = $ls->buat(['nama' => 'Punya Dodi', 'sumber' => 'flyer', 'tanggal_lead' => today()->toDateString(), 'agen_id' => $this->dodi->id], null);

        $this->actingAs($this->akunSari);
        $this->get(route('beranda'))->assertRedirect(route('agen.show', $this->sari));
        $this->get(route('agen.show', $this->sari))->assertOk();
        $this->get(route('agen.show', $this->dodi))->assertForbidden();

        // Halaman admin tertutup
        foreach ([route('dashboard'), route('kavling.index'), route('transaksi-penjualan.index'), route('kas-proyek.index'), route('proyek.index'), route('pengguna.index'), route('agen.index')] as $url) {
            $this->get($url)->assertForbidden();
        }

        // Lead: hanya miliknya yang tampil; input selalu atas namanya meski mengirim agen lain
        $this->get(route('lead.index'))->assertOk()->assertDontSee('Punya Dodi');
        $this->post(route('lead.store'), ['nama' => 'Calon Sari', 'sumber' => 'kenalan', 'tanggal_lead' => today()->toDateString(), 'agen_id' => $this->dodi->id])->assertSessionHasNoErrors();
        $baru = Lead::firstWhere('nama', 'Calon Sari');
        $this->assertSame($this->sari->id, $baru->agen_id);
        $this->assertSame($this->akunSari->id, $baru->diinput_oleh);

        $this->post(route('lead.prospek', $milikDodi), ['tanggal' => today()->toDateString()])->assertForbidden();
        $this->delete(route('lead.destroy', $milikDodi))->assertForbidden();
        $this->post(route('lead.prospek', $baru), ['tanggal' => today()->toDateString()])->assertSessionHasNoErrors();

        // Transaksi: hanya transaksi atas namanya
        $k = Konsumen::create(['id_konsumen' => 'CUS-1', 'nama_lengkap' => 'K', 'nik' => '3205000000000001', 'no_hp' => '0812', 'alamat' => 'Garut']);
        $svc = app(TransaksiService::class);
        $tSari = $svc->buat(['konsumen_id' => $k->id, 'kavling_id' => Kavling::firstWhere('kode_kavling', 'TR-A01')->id, 'agen_id' => $this->sari->id, 'tanggal' => '2026-09-01', 'jenis_pembayaran' => 'cash', 'nominal_dp' => 0]);
        $tDodi = $svc->buat(['konsumen_id' => $k->id, 'kavling_id' => Kavling::firstWhere('kode_kavling', 'TR-A02')->id, 'agen_id' => $this->dodi->id, 'tanggal' => '2026-09-01', 'jenis_pembayaran' => 'cash', 'nominal_dp' => 0]);
        $this->get(route('transaksi-penjualan.show', $tSari))->assertOk()->assertDontSee('Catat Pembayaran');
        $this->get(route('transaksi-penjualan.show', $tDodi))->assertForbidden();
        $this->post(route('pembayaran.store', $tSari), ['tanggal' => today()->toDateString(), 'jenis' => 'booking', 'nominal' => 1, 'metode' => 'tunai'])->assertForbidden();

        // Closing hanya dengan transaksi miliknya
        $this->post(route('lead.closing', $baru), ['tanggal' => today()->toDateString(), 'transaksi_id' => $tDodi->id])->assertForbidden();
        $this->post(route('lead.closing', $baru), ['tanggal' => today()->toDateString(), 'transaksi_id' => $tSari->id])->assertSessionHasErrors('transaksi_id'); // masih menunggu pembayaran
        $svc->catatPembayaran($tSari, ['tanggal' => '2026-09-01', 'jenis' => 'reservasi', 'nominal' => 500000, 'metode' => 'transfer']);
        $this->post(route('lead.closing', $baru), ['tanggal' => today()->toDateString(), 'transaksi_id' => $tSari->id])->assertSessionHasNoErrors();
        $this->assertSame('closing', $baru->fresh()->tahap);
    }

    public function test_admin_kelola_akun_agen(): void
    {
        $this->actingAs(User::where('role', 'admin')->first());
        $this->get(route('pengguna.index'))->assertOk();
        $this->post(route('pengguna.store'), ['name' => 'Dodi', 'email' => 'dodi@tectona.test', 'role' => 'agen', 'password' => 'pendek'])->assertSessionHasErrors(['agen_id', 'password']);
        $this->post(route('pengguna.store'), ['name' => 'Dodi', 'email' => 'dodi@tectona.test', 'role' => 'agen', 'agen_id' => $this->sari->id, 'password' => 'rahasia123', 'aktif' => 1])
            ->assertSessionHasErrors('agen_id'); // Sari sudah punya akun
        $this->post(route('pengguna.store'), ['name' => 'Dodi', 'email' => 'dodi@tectona.test', 'role' => 'agen', 'agen_id' => $this->dodi->id, 'password' => 'rahasia123', 'aktif' => 1])
            ->assertSessionHasNoErrors();

        $admin = User::where('role', 'admin')->first();
        $this->put(route('pengguna.update', $admin), ['name' => 'A', 'email' => $admin->email, 'role' => 'admin'])->assertSessionHas('error'); // menonaktifkan diri sendiri
        $this->delete(route('pengguna.destroy', $admin))->assertSessionHas('error');
    }

    public function test_ganti_kata_sandi_di_profil(): void
    {
        $this->actingAs($this->akunSari);
        $this->put(route('profil.update'), ['name' => 'Sari', 'email' => 'sari@tectona.test', 'password_lama' => 'salah', 'password' => 'barubaru1', 'password_confirmation' => 'barubaru1'])
            ->assertSessionHasErrors('password_lama');
        $this->put(route('profil.update'), ['name' => 'Sari W', 'email' => 'sari@tectona.test', 'password_lama' => 'rahasia123', 'password' => 'barubaru1', 'password_confirmation' => 'barubaru1'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('barubaru1', $this->akunSari->fresh()->password));
    }
}
