<?php

namespace App\Http\Controllers;

use App\Models\Agen;
use App\Models\KomisiPembayaran;
use App\Services\KomisiService;
use App\Services\LeadService;
use App\Services\Penomoran;
use App\Services\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AgenController extends Controller
{
    public function __construct(private KomisiService $komisi, private LeadService $lead) {}

    public function index(Request $request)
    {
        $total = $this->lead->totalPerAgen();

        $agens = Agen::query()
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($w) => $w
                ->where('nama_agen', 'like', "%{$request->cari}%")->orWhere('kode_agen', 'like', "%{$request->cari}%")))
            ->when($request->status === 'aktif', fn ($q) => $q->where('aktif', true))
            ->when($request->status === 'nonaktif', fn ($q) => $q->where('aktif', false))
            ->orderBy('kode_agen')->get()
            ->map(function (Agen $a) use ($total) {
                $a->angka = $this->komisi->ringkasan($a) + [
                    'lead'    => (int) ($total[$a->id]->lead ?? 0),
                    'prospek' => (int) ($total[$a->id]->prospek ?? 0),
                    'closing' => (int) ($total[$a->id]->closing ?? 0),
                ];

                return $a;
            });

        $agens = match ($request->urut) {
            'closing'   => $agens->sortByDesc(fn ($a) => $a->angka['closing']),
            'penjualan' => $agens->sortByDesc(fn ($a) => $a->angka['nilai_penjualan']),
            'sisa'      => $agens->sortByDesc(fn ($a) => $a->angka['sisa'] ?? 0),
            default     => $agens,
        };

        $stats = [
            'agen'      => $agens->count(),
            'lead'      => $agens->sum(fn ($a) => $a->angka['lead']),
            'prospek'   => $agens->sum(fn ($a) => $a->angka['prospek']),
            'closing'   => $agens->sum(fn ($a) => $a->angka['closing']),
            'penjualan' => $agens->sum(fn ($a) => $a->angka['nilai_penjualan']),
            'hak'       => $agens->sum(fn ($a) => $a->angka['komisi_hak'] ?? 0),
            'sisa'      => $agens->sum(fn ($a) => $a->angka['sisa'] ?? 0),
        ];

        return view('agen.index', compact('agens', 'stats'));
    }

    public function show(Agen $agen)
    {
        $this->pastikanMilik($agen->id);
        $rincian = $this->komisi->rincian($agen);
        $t = $this->lead->totalPerAgen()[$agen->id] ?? null;

        return view('agen.show', [
            'agen'     => $agen->load(['komisiPembayarans.transaksi.kavling', 'komisiPembayarans.kas']),
            'rincian'  => $rincian,
            'angka'    => $this->komisi->ringkasan($agen, $rincian) + [
                'lead' => (int) ($t->lead ?? 0), 'prospek' => (int) ($t->prospek ?? 0), 'closing' => (int) ($t->closing ?? 0),
            ],
            'leads'    => $agen->leads()->latest('tanggal_lead')->latest('id')->limit(10)->get(),
            'bulanan'  => $this->lead->rekapPerAgen(now()->startOfMonth(), now()->endOfMonth(), $agen->id)->first(),
            'hakSaat'  => 'Menjadi hak saat ' . mb_strtolower(Pengaturan::PILIHAN['komisi_hak_saat'][Pengaturan::get('komisi_hak_saat', 'booking')] ?? 'booking terbayar') . '.',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $agen = DB::transaction(fn () => Agen::create($data + ['kode_agen' => $this->kodeBerikut()]));

        return redirect()->route('agen.show', $agen)->with('success', "Agen {$agen->nama_agen} ({$agen->kode_agen}) berhasil ditambahkan.");
    }

    public function update(Request $request, Agen $agen)
    {
        $agen->update($this->validasi($request, $agen));

        return back()->with('success', 'Data agen berhasil diperbarui.');
    }

    public function destroy(Agen $agen)
    {
        if ($agen->leads()->exists() || $agen->transaksis()->exists() || $agen->komisiPembayarans()->exists()) {
            return back()->with('error', "{$agen->nama_agen} sudah memiliki lead/transaksi/komisi. Nonaktifkan saja agar riwayatnya tetap tersimpan.");
        }
        $agen->delete();

        return redirect()->route('agen.index')->with('success', 'Agen berhasil dihapus.');
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

    private function validasi(Request $request, ?Agen $agen = null): array
    {
        $data = $request->validate([
            'nama_agen'     => ['required', 'string', 'max:255'],
            'no_hp'         => ['nullable', 'string', 'max:20'],
            'email'         => ['nullable', 'email', 'max:255'],
            'komisi_nominal' => ['nullable', 'numeric', 'min:0'],
            'aktif'         => ['nullable', 'boolean'],
            'catatan'       => ['nullable', 'string', 'max:1000'],
        ], [], ['nama_agen' => 'nama agen', 'komisi_nominal' => 'komisi per transaksi']);
        $data['aktif'] = $request->boolean('aktif', $agen?->aktif ?? true);

        return $data;
    }

    private function kodeBerikut(): string
    {
        $maks = Agen::lockForUpdate()->pluck('kode_agen')->map(fn ($k) => (int) substr($k, 3))->max() ?? 0;

        return 'AG-' . str_pad((string) ($maks + 1), 3, '0', STR_PAD_LEFT);
    }
}
