<?php

namespace App\Http\Controllers;

use App\Models\Agen;
use App\Models\KomisiPembayaran;
use App\Services\AgenService;
use App\Services\KomisiService;
use App\Services\LeadService;
use App\Services\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Data Agen & Marketing: data agen, akun login berperan agen, kinerja, dan komisi. */
class AgenController extends Controller
{
    public function __construct(private KomisiService $komisi, private LeadService $lead, private AgenService $agenSvc) {}

    public function index(Request $request)
    {
        $totalLead = $this->lead->totalPerAgen();

        $agens = Agen::with('user')->withCount(['leads', 'transaksis', 'komisiPembayarans'])
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($w) => $w
                ->where('nama_agen', 'like', "%{$request->cari}%")->orWhere('kode_agen', 'like', "%{$request->cari}%")))
            ->when($request->akun === 'aktif', fn ($q) => $q->whereHas('user', fn ($u) => $u->where('aktif', true)))
            ->when($request->akun === 'nonaktif', fn ($q) => $q->whereHas('user', fn ($u) => $u->where('aktif', false)))
            ->when($request->akun === 'tanpa', fn ($q) => $q->doesntHave('user'))
            ->orderBy('kode_agen')->get()
            ->each(fn (Agen $a) => $a->angka = $this->agenSvc->angka($a, $totalLead))
            ->when($request->sisa === 'ada', fn ($c) => $c->filter(fn ($a) => $a->angka['sisa'] > 0))
            ->when($request->sisa === 'lunas', fn ($c) => $c->filter(fn ($a) => $a->angka['sisa'] <= 0))
            ->values();

        // Kartu = jumlah kolom tabel yang sedang tampil
        $stats = [
            'agen'      => $agens->count(),
            'lead'      => $agens->sum(fn ($a) => $a->angka['lead']),
            'prospek'   => $agens->sum(fn ($a) => $a->angka['prospek']),
            'closing'   => $agens->sum(fn ($a) => $a->angka['closing']),
            'penjualan' => $agens->sum(fn ($a) => $a->angka['nilai_penjualan']),
            'komisi'    => $agens->sum(fn ($a) => $a->angka['komisi_hak']),
        ];

        return view('agen.index', [
            'agens'    => $agens,
            'stats'    => $stats,
            'semua'    => Agen::count(),
            'kodeBaru' => $this->kodeBerikut(),
        ]);
    }

    public function show(Agen $agen)
    {
        $this->pastikanMilik($agen->id);
        $rincian = $this->komisi->rincian($agen);

        return view('agen.show', [
            'agen'     => $agen->load(['user', 'komisiPembayarans.transaksi.kavling', 'komisiPembayarans.kas']),
            'rincian'  => $rincian,
            'angka'    => $this->agenSvc->angka($agen, null, $rincian),
            'leads'    => $agen->leads()->latest('tanggal_lead')->latest('id')->limit(10)->get(),
            'bulanan'  => $this->lead->rekapPerAgen(now()->startOfMonth(), now()->endOfMonth(), $agen->id)->first(),
            'hakSaat'  => 'Menjadi hak saat ' . mb_strtolower(Pengaturan::PILIHAN['komisi_hak_saat'][Pengaturan::get('komisi_hak_saat', 'booking')] ?? 'booking terbayar') . '.',
        ]);
    }

    /** Data agen dan akun login berperan agen dibuat sekaligus. */
    public function store(Request $request)
    {
        [$dataAgen, $dataAkun] = $this->validasi($request);

        $agen = DB::transaction(function () use ($dataAgen, $dataAkun) {
            $agen = Agen::create($dataAgen + ['kode_agen' => $this->kodeBerikut()]);
            $agen->user()->create($dataAkun + ['name' => $agen->nama_agen, 'role' => 'agen']);

            return $agen;
        });

        return redirect()->route('agen.index')->with('success', "Agen {$agen->nama_agen} ({$agen->kode_agen}) dan akunnya berhasil dibuat.");
    }

    /** Kata sandi hanya berubah bila diisi; agen tanpa akun dibuatkan akun bila isian akun diisi. */
    public function update(Request $request, Agen $agen)
    {
        [$dataAgen, $dataAkun] = $this->validasi($request, $agen);

        DB::transaction(function () use ($agen, $dataAgen, $dataAkun) {
            $agen->update($dataAgen);
            if ($dataAkun === null) {
                return;
            }
            if (empty($dataAkun['password'])) {
                unset($dataAkun['password']);
            }
            $agen->user
                ? $agen->user->update($dataAkun + ['name' => $agen->nama_agen])
                : $agen->user()->create($dataAkun + ['name' => $agen->nama_agen, 'role' => 'agen']);
        });

        return back()->with('success', "Data agen {$agen->nama_agen} berhasil diperbarui.");
    }

    public function destroy(Agen $agen)
    {
        if ($this->punyaRiwayat($agen)) {
            return back()->with('error', "{$agen->nama_agen} sudah memiliki lead atau transaksi sehingga tidak bisa dihapus. Nonaktifkan akunnya saja.");
        }

        DB::transaction(function () use ($agen) {
            $agen->user()->delete();
            $agen->delete();
        });

        return redirect()->route('agen.index')->with('success', "Agen {$agen->nama_agen} beserta akunnya berhasil dihapus.");
    }

    /** Pengganti hapus untuk agen yang punya riwayat: akun tidak bisa login, agen tidak muncul di pilihan lead. */
    public function nonaktifkan(Agen $agen)
    {
        DB::transaction(function () use ($agen) {
            $agen->update(['aktif' => false]);
            $agen->user?->update(['aktif' => false]);
        });

        return back()->with('success', "Akun {$agen->nama_agen} dinonaktifkan. Riwayat lead & transaksinya tetap tersimpan.");
    }

    public function nextKode()
    {
        return response()->json(['kode' => $this->kodeBerikut()]);
    }

    public function bayarKomisi(Request $request, Agen $agen)
    {
        $data = $request->validate([
            'tanggal'      => ['required', 'date', 'before_or_equal:today'],
            'nominal'      => ['required', 'numeric', 'min:1'],
            'metode'       => ['required', 'in:tunai,transfer'],
            'transaksi_id' => ['nullable', Rule::exists('transaksi_penjualans', 'id')->where('agen_id', $agen->id)],
            'catatan'      => ['nullable', 'string', 'max:500'],
        ], [], ['transaksi_id' => 'transaksi']);

        $p = $this->komisi->bayar($agen, $data, auth()->id());

        return back()->with('success', 'Pembayaran komisi ' . rupiah($p->nominal) . ' tercatat dan masuk Kas Proyek sebagai pengeluaran.');
    }

    public function hapusBayarKomisi(Agen $agen, KomisiPembayaran $pembayaran)
    {
        abort_unless($pembayaran->agen_id === $agen->id, 404);
        $this->komisi->hapusBayar($pembayaran);

        return back()->with('success', 'Pembayaran komisi dihapus beserta catatan kasnya.');
    }

    /** @return array{0: array, 1: ?array} [data agen, data akun (null = agen lama tanpa akun & isian akun kosong)] */
    private function validasi(Request $request, ?Agen $agen = null): array
    {
        $akun = $agen?->user;
        $request->merge(['login' => trim((string) $request->input('login'))]);
        // Akun wajib saat tambah atau bila agen sudah punya akun; agen lama tanpa akun boleh dibiarkan kosong
        $wajibAkun = ! $agen || $akun || $request->filled('login') || $request->filled('password');

        $data = $request->validate([
            'nama_agen'      => ['required', 'string', 'max:255'],
            'no_hp'          => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'komisi_nominal' => ['nullable', 'numeric', 'min:0'],
            'login'          => [$wajibAkun ? 'required' : 'nullable', 'string', 'max:255',
                function ($attr, $nilai, $gagal) {
                    $sah = str_contains($nilai, '@') ? filter_var($nilai, FILTER_VALIDATE_EMAIL) : preg_match('/^[A-Za-z0-9._-]{3,}$/', $nilai);
                    if (! $sah) {
                        $gagal('Isi email yang valid, atau nama pengguna minimal 3 karakter (huruf, angka, titik, garis bawah, tanda hubung).');
                    }
                },
                Rule::unique('users', 'email')->ignore($akun?->id)],
            'password'       => [$wajibAkun && ! $akun ? 'required' : 'nullable', 'confirmed', Password::min(8)],
            'aktif'          => ['nullable', 'boolean'],
        ], [
            'no_hp.regex'        => 'Nomor HP hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
            'login.unique'       => 'Email atau nama pengguna ini sudah dipakai akun lain.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'komisi_nominal.min' => 'Komisi tidak boleh negatif.',
        ], [
            'nama_agen' => 'nama agen', 'no_hp' => 'nomor HP', 'komisi_nominal' => 'komisi per transaksi',
            'login' => 'email atau nama pengguna', 'password' => 'kata sandi',
        ]);

        // Satu saklar status: akun bisa login & agen muncul di pilihan lead
        $aktif = $request->boolean('aktif');
        $dataAgen = ['nama_agen' => $data['nama_agen'], 'no_hp' => $data['no_hp'], 'komisi_nominal' => $data['komisi_nominal'] ?? null, 'aktif' => $aktif];
        $dataAkun = $wajibAkun ? ['email' => $data['login'], 'password' => $data['password'] ?? null, 'aktif' => $aktif] : null;

        return [$dataAgen, $dataAkun];
    }

    private function punyaRiwayat(Agen $agen): bool
    {
        return $agen->leads()->exists() || $agen->transaksis()->exists() || $agen->komisiPembayarans()->exists();
    }

    private function kodeBerikut(): string
    {
        $maks = Agen::lockForUpdate()->pluck('kode_agen')->map(fn ($k) => (int) substr($k, 3))->max() ?? 0;

        return 'AG-' . str_pad((string) ($maks + 1), 3, '0', STR_PAD_LEFT);
    }
}
