<?php

namespace App\Http\Controllers;

use App\Models\Agen;
use App\Models\Kavling;
use App\Models\Lead;
use App\Models\TransaksiPenjualan;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function __construct(private LeadService $svc) {}

    public function index(Request $request)
    {
        $milik = $this->agenLogin();
        $leads = Lead::with(['agen', 'penginput', 'transaksi.kavling', 'kavlingMinat'])
            ->when($milik, fn ($q) => $q->where('agen_id', $milik))
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($w) => $w
                ->where('nama', 'like', "%{$request->cari}%")->orWhere('no_hp', 'like', "%{$request->cari}%")->orWhere('kode', 'like', "%{$request->cari}%")))
            ->when($request->filled('tahap'), fn ($q) => $q->where('tahap', $request->tahap))
            ->when($request->filled('agen'), fn ($q) => $q->where('agen_id', $request->agen))
            ->when($request->filled('sumber'), fn ($q) => $q->where('sumber', $request->sumber))
            ->when($request->filled('tanggal'), fn ($q) => $q->where(fn ($w) => $w
                ->whereDate('tanggal_lead', $request->tanggal)->orWhereDate('tanggal_prospek', $request->tanggal)->orWhereDate('tanggal_closing', $request->tanggal)))
            ->latest('tanggal_lead')->latest('id')
            ->paginate(20)->withQueryString();

        $hariIni = $this->svc->rekapPerAgen(today(), today(), $milik);
        $bulanIni = $this->svc->rekapPerAgen(now()->startOfMonth(), now()->endOfMonth(), $milik);

        return view('lead.index', [
            'leads'    => $leads,
            'hariIni'  => ['lead' => $hariIni->sum('lead'), 'prospek' => $hariIni->sum('prospek'), 'closing' => $hariIni->sum('closing')],
            'bulanIni' => ['lead' => $bulanIni->sum('lead'), 'prospek' => $bulanIni->sum('prospek'), 'closing' => $bulanIni->sum('closing')],
            'perTahap' => Lead::when($milik, fn ($q) => $q->where('agen_id', $milik))->selectRaw('tahap, COUNT(*) n')->groupBy('tahap')->pluck('n', 'tahap'),
        ] + $this->pilihan());
    }

    public function rekap(Request $request)
    {
        $mode = in_array($request->mode, ['harian', 'mingguan', 'bulanan']) ? $request->mode : 'mingguan';
        $acuan = $request->filled('tanggal') ? Carbon::parse($request->tanggal) : today();

        [$dari, $sampai, $judul] = match ($mode) {
            'harian'   => [$acuan->copy(), $acuan->copy(), tanggal($acuan, 'l, j F Y')],
            'mingguan' => [$acuan->copy()->startOfWeek(), $acuan->copy()->endOfWeek(), tanggal($acuan->copy()->startOfWeek(), 'j M') . ' – ' . tanggal($acuan->copy()->endOfWeek(), 'j M Y')],
            'bulanan'  => [$acuan->copy()->startOfMonth(), $acuan->copy()->endOfMonth(), tanggal($acuan, 'F Y')],
        };
        $geser = ['harian' => 'addDay', 'mingguan' => 'addWeek', 'bulanan' => 'addMonthNoOverflow'][$mode];
        $mundur = ['harian' => 'subDay', 'mingguan' => 'subWeek', 'bulanan' => 'subMonthNoOverflow'][$mode];

        $agenId = $this->agenLogin() ?? ($request->integer('agen') ?: null);
        $rekap = $this->svc->rekapPerAgen($dari, $sampai, $agenId);

        return view('lead.rekap', [
            'mode'     => $mode,
            'judul'    => $judul,
            'acuan'    => $acuan,
            'sebelum'  => $acuan->copy()->{$mundur}()->toDateString(),
            'sesudah'  => $acuan->copy()->{$geser}()->toDateString(),
            'rekap'    => $rekap,
            'deret'    => $mode === 'harian' ? [] : $this->svc->deretHarian($dari, $sampai, $agenId),
            'agens'    => Agen::when($this->agenLogin(), fn ($q, $id) => $q->whereKey($id))->orderBy('nama_agen')->pluck('nama_agen', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        $lead = $this->svc->buat($this->validasi($request), auth()->id());

        return back()->with('success', "Lead {$lead->nama} ({$lead->kode}) tercatat.");
    }

    public function update(Request $request, Lead $lead)
    {
        $this->pastikanMilik($lead->agen_id);
        $this->svc->ubah($lead, $this->validasi($request));

        return back()->with('success', "Data lead {$lead->kode} diperbarui.");
    }

    public function destroy(Lead $lead)
    {
        $this->pastikanMilik($lead->agen_id);
        if ($lead->tahap === 'closing') {
            return back()->with('error', 'Lead yang sudah closing tidak bisa dihapus. Kembalikan dulu ke prospek bila salah input.');
        }
        $lead->delete();

        return back()->with('success', "Lead {$lead->kode} dihapus.");
    }

    public function prospek(Request $request, Lead $lead)
    {
        $this->pastikanMilik($lead->agen_id);
        $d = $request->validate(['tanggal' => ['required', 'date', 'before_or_equal:today'], 'catatan' => ['nullable', 'string', 'max:500']]);
        $this->svc->keProspek($lead, $d['tanggal'], $d['catatan'] ?? null, auth()->id());

        return back()->with('success', "{$lead->nama} sekarang berstatus Prospek.");
    }

    public function closing(Request $request, Lead $lead)
    {
        $this->pastikanMilik($lead->agen_id);
        $d = $request->validate([
            'tanggal'      => ['required', 'date', 'before_or_equal:today'],
            'transaksi_id' => ['required', Rule::exists('transaksi_penjualans', 'id')],
            'catatan'      => ['nullable', 'string', 'max:500'],
        ], ['transaksi_id.required' => 'Pilih transaksi penjualan yang menjadi closing lead ini.']);
        if ($milik = $this->agenLogin()) {
            abort_unless(TransaksiPenjualan::whereKey($d['transaksi_id'])->where('agen_id', $milik)->exists(), 403, 'Transaksi ini bukan milik Anda.');
        }
        $this->svc->keClosing($lead, (int) $d['transaksi_id'], $d['tanggal'], $d['catatan'] ?? null, auth()->id());

        return back()->with('success', "{$lead->nama} tercatat Closing.");
    }

    public function mundur(Lead $lead)
    {
        $this->pastikanMilik($lead->agen_id);
        $this->svc->mundur($lead, auth()->id());

        return back()->with('success', "Tahap {$lead->kode} dikembalikan satu langkah.");
    }

    private function validasi(Request $request): array
    {
        // Agen selalu menjadi penanggung jawab lead yang diinputnya sendiri
        if ($milik = $this->agenLogin()) {
            $request->merge(['agen_id' => $milik]);
        }

        return $request->validate([
            'nama'             => ['required', 'string', 'max:255'],
            'no_hp'            => ['nullable', 'string', 'max:20'],
            'domisili'         => ['nullable', 'string', 'max:255'],
            'sumber'           => ['required', Rule::in(array_keys(Lead::SUMBER))],
            'tanggal_lead'     => ['required', 'date', 'before_or_equal:today'],
            'agen_id'          => ['required', Rule::exists('agens', 'id')],
            'kavling_minat_id' => ['nullable', Rule::exists('kavlings', 'id')],
            'catatan'          => ['nullable', 'string', 'max:1000'],
        ], [], ['nama' => 'nama calon konsumen', 'tanggal_lead' => 'tanggal lead', 'agen_id' => 'agen penanggung jawab']);
    }

    private function pilihan(): array
    {
        return [
            'agens'    => Agen::where('aktif', true)->when($this->agenLogin(), fn ($q, $id) => $q->whereKey($id))->orderBy('nama_agen')->pluck('nama_agen', 'id'),
            'kavlings' => Kavling::orderBy('kode_kavling')->pluck('kode_kavling', 'id'),
            // Transaksi aktif yang belum menjadi closing lead mana pun
            'transaksiBebas' => TransaksiPenjualan::berjalan()->whereDoesntHave('lead')
                ->when($this->agenLogin(), fn ($q, $id) => $q->where('agen_id', $id))->with(['konsumen', 'kavling'])->latest('tanggal')->get()
                ->map(fn ($t) => ['id' => $t->id, 'agen_id' => $t->agen_id, 'label' => "{$t->kode_transaksi} · {$t->kavling->kode_kavling} · {$t->konsumen->nama_lengkap}"]),
        ];
    }
}
