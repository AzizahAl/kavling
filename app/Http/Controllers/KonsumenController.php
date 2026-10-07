<?php

namespace App\Http\Controllers;

use App\Models\Agen;
use App\Models\ChecklistLegal;
use App\Models\Konsumen;
use App\Models\TransaksiPenjualan;
use App\Services\Penomoran;
use App\Services\RiwayatService;
use App\Services\Pengaturan;
use App\Services\TransaksiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KonsumenController extends Controller
{
    public function index(Request $request, TransaksiService $transaksi)
    {
        $tidakBatal = fn ($q) => $q->where('status', '!=', 'batal');
        [$dokItem, $dokStatus] = array_pad(explode(':', (string) $request->dokumen, 2), 2, null);
        $dokumenSah = array_key_exists((string) $dokItem, ChecklistLegal::ITEM) && in_array($dokStatus, ['belum', 'proses', 'selesai'], true);

        $konsumens = Konsumen::query()
            ->with(['transaksis' => fn ($q) => $q->with(['kavling', 'agen', 'checklist', 'pembayarans'])->latest('id')])
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($w) => $w
                ->where('nama_lengkap', 'like', "%{$request->cari}%")
                ->orWhere('id_konsumen', 'like', "%{$request->cari}%")
                ->orWhereHas('transaksis.kavling', fn ($k) => $k->where('kode_kavling', 'like', "%{$request->cari}%"))))
            // Status mengikuti transaksi yang tampil: transaksi tidak batal terbaru; hanya batal → Batal; tanpa transaksi → Belum Transaksi
            ->when($request->status === 'belum', fn ($q) => $q->doesntHave('transaksis'))
            ->when($request->status === 'batal', fn ($q) => $q->whereHas('transaksis', fn ($t) => $t->where('status', 'batal'))
                ->whereDoesntHave('transaksis', $tidakBatal))
            ->when(in_array($request->status, array_diff(TransaksiPenjualan::STATUS, ['batal']), true),
                fn ($q) => $q->whereHas('transaksis', fn ($t) => $t->where('status', $request->status)))
            ->when($request->filled('agen'), fn ($q) => $q->whereHas('transaksis', fn ($t) => $tidakBatal($t)->where('agen_id', $request->agen)))
            ->when($dokumenSah, fn ($q) => $q->whereHas('transaksis', fn ($t) => $tidakBatal($t)->whereHas('checklist', fn ($c) => $dokStatus === 'belum'
                ? $c->where(fn ($w) => $w->where("{$dokItem}_status", 'belum')->orWhereNull("{$dokItem}_status"))
                : $c->where("{$dokItem}_status", $dokStatus))))
            ->latest('id')
            ->paginate(15)->withQueryString();

        // Transaksi yang ditampilkan per konsumen: aktif terbaru, atau transaksi batal terbaru bila semuanya batal
        $konsumens->getCollection()->each(function (Konsumen $k) {
            $aktif = $k->transaksis->reject->isBatal()->sortByDesc(fn ($t) => [$t->tanggal, $t->id])->values();
            $k->tampil = $aktif->first() ?? $k->transaksis->first();
            $k->kavling_lain = max(0, $aktif->count() - 1);
        });

        // Angka transaksi memakai rumus yang sama dengan halaman Transaksi Penjualan (tanpa menunggu & batal)
        $r = $transaksi->ringkasan(TransaksiPenjualan::berjalan());
        $stats = [
            'total'      => Konsumen::count(),
            'reservasi'  => $r['per_status']['reservasi'] ?? 0,
            'booking'    => $r['per_status']['booking'] ?? 0,
            'dp'         => $r['per_status']['dp'] ?? 0,
            'angsuran'   => $r['per_status']['angsuran'] ?? 0,
            'lunas'      => $r['per_status']['lunas'] ?? 0,
            'nilai_jual' => $r['nilai_jual'],
            'terbayar'   => $r['terbayar'],
        ];

        return view('konsumen.index', [
            'konsumens' => $konsumens,
            'stats'     => $stats,
            'agens'     => Agen::orderBy('nama_agen')->pluck('nama_agen', 'id'),
        ]);
    }

    public function show(Konsumen $konsumen)
    {
        $konsumen->load(['transaksis' => fn ($q) => $q->with(['kavling', 'agen', 'pembayarans', 'checklist'])]);

        return view('konsumen.show', compact('konsumen'));
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $konsumen = DB::transaction(fn () => Konsumen::create($data + [
            'id_konsumen' => Penomoran::berikut('konsumens', 'id_konsumen', Pengaturan::get('prefix_konsumen', 'CUS')),
        ]));

        return redirect()->route('konsumen.show', $konsumen)->with('success', "Konsumen {$konsumen->nama_lengkap} ({$konsumen->id_konsumen}) berhasil ditambahkan.");
    }

    public function update(Request $request, Konsumen $konsumen)
    {
        $konsumen->update($this->validasi($request, $konsumen));

        return back()->with('success', 'Data konsumen berhasil diperbarui.');
    }

    public function destroy(Konsumen $konsumen)
    {
        if ($konsumen->transaksis()->exists()) {
            return back()->with('error', "{$konsumen->nama_lengkap} memiliki riwayat transaksi sehingga tidak bisa dihapus.");
        }
        $konsumen->delete();

        return redirect()->route('konsumen.index')->with('success', 'Konsumen berhasil dihapus.');
    }

    /** Status dokumen (SPK, PPJB, AJB) per transaksi. Satu-satunya tempat mengubahnya. */
    public function updateDokumen(Request $request, ChecklistLegal $checklist, TransaksiService $svc, RiwayatService $riwayat)
    {
        $aturan = ['catatan' => ['nullable', 'string', 'max:1000']];
        foreach (array_keys(ChecklistLegal::ITEM) as $item) {
            $aturan["{$item}_status"] = ['required', Rule::in(['belum', 'proses', 'selesai'])];
            $aturan["{$item}_tanggal"] = ['nullable', 'date', 'before_or_equal:today', "required_if:{$item}_status,selesai"];
        }
        $data = $request->validate($aturan, ['*.required_if' => 'Tanggal wajib diisi bila status Selesai.']);

        $t = $checklist->transaksi;
        if ($t->isBatal()) {
            return back()->with('error', 'Transaksi sudah dibatalkan; status dokumen tidak bisa diubah.');
        }
        // SPK dibuat setelah booking terbayar
        if ($data['spk_status'] !== 'belum' && $checklist->spk_status === 'belum' && ($alasan = $t->alasanSpkBelumBisa())) {
            throw \Illuminate\Validation\ValidationException::withMessages(['spk_status' => $alasan]);
        }

        DB::transaction(function () use ($checklist, $data, $svc, $t, $riwayat) {
            foreach (array_keys(ChecklistLegal::ITEM) as $item) {
                $lama = $checklist->{$item . '_status'};
                $baru = $data[$item . '_status'];
                $tgl = $data[$item . '_tanggal'] ?? null;
                $riwayat->catat('dokumen', $t->id, $t->kavling_id, $lama, $baru, $tgl ? 'Tanggal ' . tanggal($tgl) : null, $item);
            }
            $checklist->update($data);
            // PPJB selesai = kavling terjual → status kavling & tahap harga diperbarui
            $svc->sinkronKavling($t->kavling);
        });

        return back()->with('success', "Dokumen {$t->kavling->kode_kavling} diperbarui. Status kavling: {$t->kavling->fresh()->label_status}.");
    }

    /** Pencarian untuk form transaksi (JSON). */
    public function cari(Request $request)
    {
        $q = trim((string) $request->get('q'));

        return Konsumen::query()
            ->when($this->agenLogin(), fn ($w, $id) => $w->whereHas('transaksis', fn ($t) => $t->where('agen_id', $id)))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x
                ->where('nama_lengkap', 'like', "%{$q}%")->orWhere('id_konsumen', 'like', "%{$q}%")
                ->orWhere('nik', 'like', "%{$q}%")->orWhere('no_hp', 'like', "%{$q}%")))
            ->orderBy('nama_lengkap')->limit(8)
            ->get(['id', 'id_konsumen', 'nama_lengkap', 'nik', 'no_hp']);
    }

    public static function aturan(?Konsumen $konsumen = null, string $prefix = ''): array
    {
        return [
            $prefix . 'nama_lengkap' => ['required', 'string', 'max:255'],
            $prefix . 'nik'          => ['required', 'digits:16', Rule::unique('konsumens', 'nik')->ignore($konsumen?->id)],
            $prefix . 'no_hp'        => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
            $prefix . 'email'        => ['nullable', 'email', 'max:255'],
            $prefix . 'alamat'       => ['required', 'string', 'max:1000'],
            $prefix . 'pekerjaan'    => ['nullable', 'string', 'max:100'],
            $prefix . 'label'        => ['nullable', 'string', 'max:50'],
            $prefix . 'catatan'      => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function validasi(Request $request, ?Konsumen $konsumen = null): array
    {
        return $request->validate(self::aturan($konsumen), [
            'nik.digits'   => 'NIK harus 16 digit angka.',
            'nik.unique'   => 'NIK ini sudah terdaftar sebagai konsumen.',
            'no_hp.regex'  => 'Nomor HP hanya boleh angka, spasi, +, atau -.',
        ]);
    }
}
