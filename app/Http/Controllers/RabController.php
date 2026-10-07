<?php

namespace App\Http\Controllers;

use App\Models\KasTransaksi;
use App\Models\Rab;
use App\Services\KasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RabController extends Controller
{
    public function index(Request $request)
    {
        // Periode = bulan (YYYY-MM); kosong = bulan ini, "semua" = seluruh periode
        $periode = $request->periode === 'semua' ? null : (preg_match('/^\d{4}-\d{2}$/', (string) $request->periode) ? $request->periode : now()->format('Y-m'));
        $semua = Rab::with('pencentang')->when($periode, fn ($q) => $q->where('periode', $periode))
            ->orderBy('tanggal_pengeluaran')->orderBy('id')->get();
        $cari = mb_strtolower((string) $request->cari);

        $rabs = $semua
            ->when($request->filled('kategori'), fn ($c) => $c->where('kategori', $request->kategori))
            ->when($request->filled('status'), fn ($c) => $c->filter(fn ($r) => $r->status === $request->status))
            ->when($cari !== '', fn ($c) => $c->filter(fn ($r) => str_contains(mb_strtolower("{$r->kategori} {$r->uraian} {$r->catatan}"), $cari)))
            ->values();

        return view('rab.index', [
            'rabs'        => $rabs,
            'jumlahSemua' => $semua->count(),
            'periode'     => $periode,
            'daftarPeriode' => Rab::whereNotNull('periode')->distinct()->orderByDesc('periode')->pluck('periode'),
            // Baris yang siap dicatat sekaligus: belum masuk kas, realisasi & tanggal pengeluaran terisi
            'siapKas'     => $semua->filter(fn ($r) => $this->siapKas($r))->values(),
        ]);
    }

    public function store(Request $request)
    {
        $rab = Rab::create($this->validasi($request));

        return redirect()->route('rab.index', ['periode' => $rab->periode])->with('success', "Item RAB \"{$rab->uraian}\" ditambahkan.");
    }

    public function update(Request $request, Rab $rab)
    {
        if ($rab->isDicentang()) {
            return back()->with('error', "\"{$rab->uraian}\" sudah masuk kas. Batalkan centang dulu untuk mengubahnya.");
        }
        $rab->update($this->validasi($request, $rab));

        return redirect()->route('rab.index', ['periode' => $rab->periode])->with('success', "Item RAB \"{$rab->uraian}\" diperbarui.");
    }

    public function destroy(Rab $rab)
    {
        if ($rab->isDicentang() || $rab->kasKeluar()->exists()) {
            return back()->with('error', "\"{$rab->uraian}\" sudah masuk kas. Batalkan centang dulu sebelum menghapus.");
        }
        $rab->delete();

        return back()->with('success', 'Item RAB dihapus.');
    }

    /** Baris final (anggaran & realisasi terisi): tanggal pengeluaran dikonfirmasi di popup, lalu dicatat sebagai pengeluaran Kas Proyek & baris dikunci. */
    public function centang(Request $request, Rab $rab, KasService $kas)
    {
        $data = $request->validate([
            'tanggal_pengeluaran' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'tanggal_pengeluaran.required'        => 'Isi tanggal pengeluaran.',
            'tanggal_pengeluaran.before_or_equal' => 'Tanggal pengeluaran tidak boleh melewati hari ini.',
        ]);

        DB::transaction(function () use ($request, $rab, $kas, $data) {
            $rab = Rab::lockForUpdate()->findOrFail($rab->id);
            if ($rab->isDicentang()) {
                abort(back()->with('error', "\"{$rab->uraian}\" sudah masuk kas."));
            }
            if (! $this->lengkap($rab)) {
                abort(back()->with('error', "Isi anggaran dan realisasi \"{$rab->uraian}\" dulu sebelum dicentang."));
            }
            $rab->fill($data);
            $this->catatKeKas($rab, $kas, $request->user()->id);
        });

        return back()->with('success', "Realisasi \"{$rab->uraian}\" tercatat di Kas Proyek sebagai pengeluaran.");
    }

    /** Semua baris periode ini yang sudah lengkap (realisasi & tanggal) dicatat ke Kas Proyek sekaligus. */
    public function centangSemua(Request $request, KasService $kas)
    {
        $data = $request->validate(['periode' => ['required', 'regex:/^\d{4}-\d{2}$/']]);

        $jumlah = DB::transaction(function () use ($request, $kas, $data) {
            $rabs = Rab::lockForUpdate()->where('periode', $data['periode'])->whereNull('kas_transaksi_id')->orderBy('tanggal_pengeluaran')->orderBy('id')->get()
                ->filter(fn ($r) => $this->siapKas($r));
            $rabs->each(fn ($r) => $this->catatKeKas($r, $kas, $request->user()->id));

            return $rabs->count();
        });

        return back()->with($jumlah ? 'success' : 'warning', $jumlah
            ? "{$jumlah} baris RAB tercatat di Kas Proyek sebagai pengeluaran."
            : 'Tidak ada baris yang siap dicatat. Isi anggaran, realisasi, dan tanggal pengeluaran dulu.');
    }

    /** Batalkan centang: catatan kas dari baris ini dihapus, baris bisa diubah lagi. */
    public function batalCentang(Rab $rab)
    {
        DB::transaction(function () use ($rab) {
            $rab = Rab::lockForUpdate()->findOrFail($rab->id);
            // Semua kas keluar dari RAB ini (termasuk data lama sebelum ada centang) ikut dihapus
            KasTransaksi::where('rab_id', $rab->id)->where('jenis', 'keluar')->where('asal', 'rab')->get()->each->delete();
            $rab->update(['kas_transaksi_id' => null, 'dicentang_oleh' => null, 'dicentang_pada' => null]);
        });

        return back()->with('success', "Centang \"{$rab->uraian}\" dibatalkan dan catatan kasnya dihapus.");
    }

    /** Bisa dicentang: anggaran sudah diisi DAN realisasi lebih dari nol. */
    private function lengkap(Rab $r): bool
    {
        return $r->anggaran !== null && $r->realisasi > 0;
    }

    private function siapKas(Rab $r): bool
    {
        return ! $r->isDicentang() && $this->lengkap($r) && $r->tanggal_pengeluaran && $r->tanggal_pengeluaran->lte(today());
    }

    /** Buat catatan kas keluar dari baris RAB (dipanggil di dalam transaksi database). */
    private function catatKeKas(Rab $rab, KasService $kas, int $userId): void
    {
        $catatan = KasTransaksi::create([
            'tanggal'  => $rab->tanggal_pengeluaran->toDateString(),
            'kode'     => $kas->kodeBerikut('keluar', $rab->tanggal_pengeluaran),
            'kategori' => $rab->kategori,
            'pos'      => $rab->pos ?? Rab::KATEGORI[$rab->kategori] ?? null,
            'rab_id'   => $rab->id,
            'jenis'    => 'keluar',
            'asal'     => 'rab',
            'uraian'   => $rab->uraian,
            'nominal'  => $rab->realisasi,
            'catatan'  => 'Dari RAB & Realisasi periode ' . tanggal($rab->periode . '-01', 'F Y') . ($rab->catatan ? ': ' . $rab->catatan : ''),
        ]);

        $rab->fill(['kas_transaksi_id' => $catatan->id, 'dicentang_oleh' => $userId, 'dicentang_pada' => now()])->save();
    }

    /** Periode tidak diisi di form: mengikuti bulan tanggal pengeluaran, atau periode yang sedang dibuka. */
    private function validasi(Request $request, ?Rab $rab = null): array
    {
        $data = $request->validate([
            'tanggal_pengeluaran' => ['nullable', 'date'],
            'kategori'  => ['required', Rule::in(array_keys(Rab::KATEGORI))],
            'uraian'    => ['required', 'string', 'max:255'],
            'anggaran'  => ['nullable', 'numeric', 'min:0'],
            'realisasi' => ['nullable', 'numeric', 'min:0'],
            'catatan'   => ['nullable', 'string', 'max:1000'],
        ], [
            'kategori.in'   => 'Pilih kategori dari daftar.',
            'anggaran.min'  => 'Anggaran tidak boleh negatif.',
            'realisasi.min' => 'Realisasi tidak boleh negatif.',
        ], ['tanggal_pengeluaran' => 'tanggal pengeluaran']);

        $periodeHalaman = preg_match('/^\d{4}-\d{2}$/', (string) $request->input('_periode')) ? $request->input('_periode') : null;
        $data['periode'] = ! empty($data['tanggal_pengeluaran'])
            ? substr($data['tanggal_pengeluaran'], 0, 7)
            : ($rab?->periode ?? $periodeHalaman ?? now()->format('Y-m'));

        // Pos alokasi kas mengikuti kategori
        return $data + ['pos' => Rab::KATEGORI[$data['kategori']]];
    }
}
