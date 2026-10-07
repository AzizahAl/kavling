<?php

namespace App\Http\Controllers;

use App\Models\Agen;
use App\Models\KasTransaksi;
use App\Models\Kavling;
use App\Models\Pembayaran;
use App\Models\Rab;
use App\Models\TransaksiPenjualan;
use App\Services\AlokasiService;
use App\Services\AngsuranService;
use App\Services\HargaService;
use App\Services\KomisiService;
use App\Services\LeadService;
use App\Services\Pengaturan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** Semua angka dashboard diambil dari database (padanan sheet DASHBOARD di Excel). */
class DashboardController extends Controller
{
    public function index(HargaService $harga, AlokasiService $alokasi, LeadService $lead, KomisiService $komisi, AngsuranService $angsuran)
    {
        $jumlahStatus = Kavling::selectRaw('status, COUNT(*) n')->groupBy('status')->pluck('n', 'status');
        $totalKavling = (int) $jumlahStatus->sum();

        // Angka penjualan & pendapatan hanya dari transaksi yang sudah menerima uang
        $aktif = TransaksiPenjualan::berjalan();
        $nilaiJual = (float) (clone $aktif)->sum('nilai_jual');
        $pokok = (float) DB::table('pembayarans')->join('transaksi_penjualans as t', 't.id', '=', 'pembayarans.transaksi_id')
            ->whereNotIn('t.status', ['batal', 'menunggu'])->whereIn('pembayarans.jenis', TransaksiPenjualan::JENIS_POKOK)->sum('pembayarans.nominal');
        $kasMasuk = (float) KasTransaksi::where('jenis', 'masuk')->sum('nominal');
        $kasKeluar = (float) KasTransaksi::where('jenis', 'keluar')->sum('nominal');
        $anggaranRab = (float) Rab::sum('anggaran');
        $realisasiRab = (float) KasTransaksi::where('jenis', 'keluar')->whereNotNull('rab_id')->sum('nominal');

        // Deret 12 bulan terakhir
        $mulai = now()->startOfMonth()->subMonths(11);
        $bulan = collect(range(0, 11))->map(fn ($i) => $mulai->copy()->addMonths($i)->format('Y-m'));
        $perBulan = fn ($q, string $kolomTgl, string $kolomNilai) => $q->where($kolomTgl, '>=', $mulai)
            ->selectRaw("DATE_FORMAT({$kolomTgl}, '%Y-%m') b, SUM({$kolomNilai}) n")->groupBy('b')->pluck('n', 'b');
        $jualBulan = $perBulan(TransaksiPenjualan::berjalan(), 'tanggal', 'nilai_jual');
        $unitBulan = TransaksiPenjualan::berjalan()->where('tanggal', '>=', $mulai)->selectRaw("DATE_FORMAT(tanggal, '%Y-%m') b, COUNT(*) n")->groupBy('b')->pluck('n', 'b');
        $masukBulan = $perBulan(KasTransaksi::where('jenis', 'masuk'), 'tanggal', 'nominal');
        $keluarBulan = $perBulan(KasTransaksi::where('jenis', 'keluar'), 'tanggal', 'nominal');

        // Agen teratas: closing (lead) & nilai penjualan
        $totalLead = $lead->totalPerAgen();
        $agenTop = Agen::where('aktif', true)->get()
            ->map(fn ($a) => (object) [
                'agen'      => $a,
                'lead'      => (int) ($totalLead[$a->id]->lead ?? 0),
                'prospek'   => (int) ($totalLead[$a->id]->prospek ?? 0),
                'closing'   => (int) ($totalLead[$a->id]->closing ?? 0),
                'penjualan' => $komisi->ringkasan($a)['nilai_penjualan'],
            ])
            ->sortByDesc(fn ($x) => [$x->closing, $x->penjualan])->take(5)->values();

        $funnel = $lead->rekapPerAgen(now()->startOfMonth(), now()->endOfMonth());

        // Piutang terlambat
        $terlambat = TransaksiPenjualan::berjalan()->where('jenis_pembayaran', 'angsuran')->where('status', '!=', 'lunas')
            ->with(['konsumen', 'kavling', 'pembayarans', 'jadwalAngsurans'])->get()
            ->map(fn ($t) => (object) ['t' => $t])
            ->map(function ($x) use ($angsuran) {
                $r = $angsuran->ringkasan($x->t);
                $x->tunggakan = $r['tunggakan'];
                $x->hari = $r['hari_telat_maks'];

                return $x;
            })
            ->where('tunggakan', '>', 0)->sortByDesc('hari')->take(5)->values();

        $pos = $alokasi->posisiPos();

        return view('dashboard', [
            'status'      => $jumlahStatus,
            'totalKavling' => $totalKavling,
            'stats'       => [
                'harga_aktif'  => $harga->hargaAktif(),
                'tahap'        => $harga->nomorTahapAktif(),
                'jumlah_tahap' => $harga->daftarTahap()->count(),
                'menuju_naik'  => $harga->menujuNaik(),
                'nilai_jual'   => $nilaiJual,
                'transaksi'    => (clone $aktif)->count(),
                'uang_masuk'   => (float) Pembayaran::whereHas('transaksi', fn ($q) => $q->berjalan())->sum('nominal'),
                'pokok'        => $pokok,
                'piutang'      => $nilaiJual - $pokok,
                'kas_masuk'    => $kasMasuk,
                'kas_keluar'   => $kasKeluar,
                'saldo'        => $kasMasuk - $kasKeluar,
                'anggaran_rab' => $anggaranRab,
                'realisasi_rab' => $realisasiRab,
            ],
            'grafik'      => [
                'label'  => $bulan->map(fn ($b) => tanggal(Carbon::createFromFormat('Y-m', $b)->startOfMonth(), 'M y'))->all(),
                'jual'   => $bulan->map(fn ($b) => (float) ($jualBulan[$b] ?? 0))->all(),
                'unit'   => $bulan->map(fn ($b) => (int) ($unitBulan[$b] ?? 0))->all(),
                'masuk'  => $bulan->map(fn ($b) => (float) ($masukBulan[$b] ?? 0))->all(),
                'keluar' => $bulan->map(fn ($b) => (float) ($keluarBulan[$b] ?? 0))->all(),
            ],
            'transaksiTerbaru' => TransaksiPenjualan::where('status', '!=', 'menunggu')->with(['konsumen', 'kavling'])->denganRingkasan()->latest('tanggal')->latest('id')->limit(6)->get(),
            'agenTop'     => $agenTop,
            'funnel'      => ['lead' => $funnel->sum('lead'), 'prospek' => $funnel->sum('prospek'), 'closing' => $funnel->sum('closing')],
            'terlambat'   => $terlambat,
            'pos'         => $pos,
            'laba'        => $alokasi->kelayakanLaba($pos),
            'tanah'       => app(\App\Services\KewajibanTanahService::class)->ringkasan(),
            'menunggu'    => TransaksiPenjualan::menunggu()->with(['konsumen', 'kavling'])->orderBy('batas_tahan')->get(),
            'baseline'    => [
                'Luas Lahan'  => angka(Pengaturan::get('luas_lahan_are'), 2) . ' are',
                'Jalan Dalam' => angka(Pengaturan::get('lebar_jalan_m'), 1) . ' m',
                'Prima'       => Kavling::where('tipe', 'Prima')->count() . ' kavling',
                'Standard'    => Kavling::where('tipe', 'like', 'Standard%')->count() . ' kavling',
                'Legal Lahan' => Pengaturan::get('status_legal_lahan') ?? '—',
            ],
        ]);
    }
}
