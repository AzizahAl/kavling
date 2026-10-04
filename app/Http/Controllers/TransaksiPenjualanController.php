<?php

namespace App\Http\Controllers;

use App\Models\Agen;
use App\Models\Kavling;
use App\Models\Konsumen;
use App\Models\Pembayaran;
use App\Models\TransaksiPenjualan;
use App\Services\AngsuranService;
use App\Services\HargaService;
use App\Services\Penomoran;
use App\Services\Pengaturan;
use App\Services\TransaksiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TransaksiPenjualanController extends Controller
{
    public function __construct(private TransaksiService $svc) {}

    public function index(Request $request)
    {
        $filter = fn ($q) => $q
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($w) => $w
                ->where('kode_transaksi', 'like', "%{$request->cari}%")
                ->orWhereHas('konsumen', fn ($k) => $k->where('nama_lengkap', 'like', "%{$request->cari}%"))
                ->orWhereHas('kavling', fn ($k) => $k->where('kode_kavling', 'like', "%{$request->cari}%"))))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis_pembayaran', $request->jenis))
            ->when($request->filled('agen'), fn ($q) => $q->where('agen_id', $request->agen))
            ->when($request->filled('bulan'), fn ($q) => $q->whereYear('tanggal', substr($request->bulan, 0, 4))->whereMonth('tanggal', substr($request->bulan, 5, 2)));

        // Daftar utama = transaksi yang sudah menghasilkan penerimaan (+ batal). "Menunggu Pembayaran" tampil terpisah,
        // kecuali tab Menunggu dipilih.
        $transaksis = $filter(TransaksiPenjualan::with(['konsumen', 'kavling', 'agen'])->denganRingkasan())
            ->when($request->status !== 'menunggu', fn ($q) => $q->where('status', '!=', 'menunggu'))
            ->latest('tanggal')->latest('id')
            ->paginate(15)->withQueryString();

        $menunggu = $request->filled('status') ? collect() : TransaksiPenjualan::menunggu()->with(['konsumen', 'kavling', 'agen'])->orderBy('batas_tahan')->get();

        // Angka penjualan hanya dari transaksi yang sudah menerima uang (bukan menunggu, bukan batal)
        $berjalan = TransaksiPenjualan::berjalan();
        $totalPokok = (float) DB::table('pembayarans')->join('transaksi_penjualans as t', 't.id', '=', 'pembayarans.transaksi_id')
            ->whereNotIn('t.status', ['batal', 'menunggu'])->whereIn('pembayarans.jenis', TransaksiPenjualan::JENIS_POKOK)->sum('pembayarans.nominal');
        $totalNilai = (float) (clone $berjalan)->sum('nilai_jual');

        $stats = [
            'aktif'       => (clone $berjalan)->count(),
            'per_status'  => TransaksiPenjualan::selectRaw('status, COUNT(*) n')->groupBy('status')->pluck('n', 'status'),
            'nilai_jual'  => $totalNilai,
            'terbayar'    => $totalPokok,
            'piutang'     => $totalNilai - $totalPokok,
        ];

        return view('transaksi-penjualan.index', [
            'transaksis' => $transaksis,
            'menunggu'   => $menunggu,
            'stats'      => $stats,
            'agens'      => Agen::orderBy('nama_agen')->pluck('nama_agen', 'id'),
        ]);
    }

    public function create(Request $request, HargaService $harga)
    {
        return view('transaksi-penjualan.form', $this->dataForm($harga) + [
            'transaksi' => null,
            'pilihKavling' => $request->integer('kavling') ?: null,
            'pilihKonsumen' => $request->filled('konsumen') ? Konsumen::find($request->konsumen) : null,
        ]);
    }

    public function store(Request $request)
    {
        $baru = $request->input('konsumen_mode') === 'baru';

        $aturan = $this->aturan() + [
            'konsumen_mode'  => ['required', 'in:lama,baru'],
            'kavling_id'     => ['required', 'exists:kavlings,id'],
            'konsumen_id'    => [Rule::requiredIf(! $baru), 'nullable', 'exists:konsumens,id'],
            'bayar_jenis'    => ['nullable', Rule::in(array_keys(Pembayaran::JENIS))],
            'bayar_nominal'  => ['nullable', 'numeric', 'min:0'],
            'bayar_metode'   => ['nullable', Rule::in(array_keys(Pembayaran::METODE_KONSUMEN))],
            'bayar_nama_penyetor'     => ['nullable', 'string', 'max:100'],
            'bayar_bank_penyetor'     => ['nullable', 'string', 'max:60'],
            'bayar_rekening_penyetor' => ['nullable', 'string', 'max:40'],
            'bayar_no_bukti' => ['nullable', 'string', 'max:100'],
        ];
        if ($baru) {
            $aturan += KonsumenController::aturan(null, 'konsumen_');
        }

        $data = $request->validate($aturan, [
            'konsumen_id.required' => 'Pilih konsumen, atau isi data konsumen baru.',
            'konsumen_nik.unique'  => 'NIK ini sudah terdaftar. Pilih dari konsumen lama.',
        ], $this->atribut());

        $t = DB::transaction(function () use ($data, $baru) {
            if ($baru) {
                $k = Konsumen::create(collect($data)->filter(fn ($v, $key) => str_starts_with($key, 'konsumen_') && $key !== 'konsumen_mode' && $key !== 'konsumen_id')
                    ->mapWithKeys(fn ($v, $key) => [substr($key, 9) => $v])->all() + [
                        'id_konsumen' => Penomoran::berikut('konsumens', 'id_konsumen', Pengaturan::get('prefix_konsumen', 'CUS')),
                    ]);
                $data['konsumen_id'] = $k->id;
            }

            $bayar = ! empty($data['bayar_nominal']) ? [
                'jenis'    => $data['bayar_jenis'] ?? 'reservasi',
                'nominal'  => $data['bayar_nominal'],
                'metode'   => $data['bayar_metode'] ?? 'transfer',
                'no_bukti' => $data['bayar_no_bukti'] ?? null,
                'nama_penyetor'     => $data['bayar_nama_penyetor'] ?? null,
                'bank_penyetor'     => $data['bayar_bank_penyetor'] ?? null,
                'rekening_penyetor' => $data['bayar_rekening_penyetor'] ?? null,
            ] : null;

            return $this->svc->buat($data, $bayar, auth()->id());
        });

        return redirect()->route('transaksi-penjualan.show', $t)
            ->with('success', "Transaksi {$t->kode_transaksi} untuk kavling {$t->kavling->kode_kavling} berhasil dibuat.");
    }

    public function show(TransaksiPenjualan $transaksi, AngsuranService $angsuran)
    {
        $this->pastikanMilik($transaksi->agen_id);
        $transaksi->load(['konsumen', 'kavling', 'agen', 'tahap', 'checklist', 'pembuat', 'kasRefunds', 'pembatalan.pembuat', 'pembatalan.kas',
            'riwayats.user', 'pembayarans' => fn ($q) => $q->with('kas')]);

        return view('transaksi-penjualan.show', [
            't'        => $transaksi,
            'angsuran' => $angsuran->ringkasan($transaksi),
            'cicilan'  => $angsuran->cicilanPerBulan($transaksi),
            'refund'   => $transaksi->isBatal() ? null : $this->svc->rincianPembatalan($transaksi),
            'potonganBooking' => (float) Pengaturan::get('potongan_booking', 0),
        ]);
    }

    public function edit(TransaksiPenjualan $transaksi, HargaService $harga)
    {
        if ($transaksi->isBatal()) {
            return redirect()->route('transaksi-penjualan.show', $transaksi)->with('error', 'Transaksi yang sudah dibatalkan tidak bisa diubah.');
        }
        $transaksi->load('kavling', 'konsumen');

        return view('transaksi-penjualan.form', $this->dataForm($harga) + [
            'transaksi' => $transaksi, 'pilihKavling' => $transaksi->kavling_id, 'pilihKonsumen' => $transaksi->konsumen,
        ]);
    }

    public function update(Request $request, TransaksiPenjualan $transaksi)
    {
        $data = $request->validate($this->aturan() + ['konsumen_id' => ['required', 'exists:konsumens,id']], [], $this->atribut());
        $this->svc->ubah($transaksi, $data);

        return redirect()->route('transaksi-penjualan.show', $transaksi)->with('success', 'Transaksi berhasil diperbarui. Jadwal angsuran disusun ulang.');
    }

    public function batal(Request $request, TransaksiPenjualan $transaksi)
    {
        $data = $request->validate([
            'tanggal_batal'   => ['required', 'date', 'after_or_equal:' . $transaksi->tanggal->toDateString(), 'before_or_equal:today'],
            'alasan'          => ['required', 'string', 'max:1000'],
            'pokok_potongan'  => ['nullable', 'numeric', 'min:0'],
            'dasar_ketentuan' => ['nullable', 'string', 'max:1000'],
        ], [], ['pokok_potongan' => 'potongan DP & angsuran', 'dasar_ketentuan' => 'dasar ketentuan']);

        $t = $this->svc->batal($transaksi, [
            'tanggal'         => $data['tanggal_batal'],
            'alasan'          => $data['alasan'],
            'pokok_potongan'  => (float) ($data['pokok_potongan'] ?? 0),
            'dasar_ketentuan' => $data['dasar_ketentuan'] ?? null,
        ]);
        $total = (float) $t->pembatalan()->value('total_refund');

        return redirect()->route('transaksi-penjualan.show', $transaksi)
            ->with('success', "Transaksi {$transaksi->kode_transaksi} dibatalkan. Kavling tersedia lagi" . ($total > 0 ? '; pengembalian ' . rupiah($total) . ' tercatat sebagai kas keluar.' : '.'));
    }

    // ------------------------------------------------------------------

    private function aturan(): array
    {
        return [
            'tanggal'          => ['required', 'date', 'before_or_equal:today'],
            'agen_id'          => ['nullable', 'exists:agens,id'],
            'jenis_pembayaran' => ['required', 'in:cash,angsuran'],
            'tenor'            => ['nullable', 'required_if:jenis_pembayaran,angsuran', 'integer', 'min:1', 'max:' . Pengaturan::get('tenor_maksimal', 18)],
            'nominal_dp'       => ['nullable', 'numeric', 'min:0'],
            'catatan'          => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function atribut(): array
    {
        return [
            'konsumen_nama_lengkap' => 'nama lengkap', 'konsumen_nik' => 'NIK', 'konsumen_no_hp' => 'nomor HP',
            'konsumen_alamat' => 'alamat', 'konsumen_email' => 'email', 'bayar_nominal' => 'nominal pembayaran awal',
            'tanggal' => 'tanggal transaksi',
        ];
    }

    private function dataForm(HargaService $harga): array
    {
        $hargaM2 = $harga->hargaAktif();

        return [
            'kavlings' => Kavling::where('status', 'tersedia')->orderBy('blok')->orderByRaw('CAST(SUBSTRING(`no`, 2) AS UNSIGNED)')->get()
                ->map(fn ($k) => [
                    'id' => $k->id, 'kode' => $k->kode_kavling, 'tipe' => $k->tipe, 'ukuran' => $k->ukuran,
                    'luas' => (float) $k->luas, 'harga_m2' => $hargaM2, 'harga' => $k->luas ? round($k->luas * $hargaM2) : null,
                ]),
            'agens'   => Agen::orderBy('nama_agen')->pluck('nama_agen', 'id'),
            'aturan'  => [
                'biaya_reservasi' => Pengaturan::get('biaya_reservasi', 0),
                'biaya_booking'   => Pengaturan::get('biaya_booking', 0),
                'dp_min'          => Pengaturan::get('dp_minimal_persen', 0),
                'dp_anjuran'      => Pengaturan::get('dp_anjuran_persen', 0),
                'tenor_maks'      => Pengaturan::get('tenor_maksimal', 18),
            ],
            'tahapAktif' => $harga->tahapAktif(),
        ];
    }
}
