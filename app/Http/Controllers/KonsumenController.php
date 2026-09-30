<?php

namespace App\Http\Controllers;

use App\Models\Konsumen;
use App\Models\TransaksiPenjualan;
use App\Services\Penomoran;
use App\Services\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KonsumenController extends Controller
{
    public function index(Request $request)
    {
        $konsumens = Konsumen::query()
            ->withCount(['transaksis as transaksi_aktif' => fn ($q) => $q->aktif()])
            ->with(['transaksis' => fn ($q) => $q->aktif()->with('kavling')])
            ->withSum('pembayarans as total_bayar', 'pembayarans.nominal')
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($w) => $w
                ->where('nama_lengkap', 'like', "%{$request->cari}%")
                ->orWhere('id_konsumen', 'like', "%{$request->cari}%")
                ->orWhere('nik', 'like', "%{$request->cari}%")
                ->orWhere('no_hp', 'like', "%{$request->cari}%")))
            ->when($request->status === 'aktif', fn ($q) => $q->whereHas('transaksis', fn ($t) => $t->aktif()))
            ->when($request->status === 'tanpa', fn ($q) => $q->whereDoesntHave('transaksis', fn ($t) => $t->aktif()))
            ->latest('id')
            ->paginate(15)->withQueryString();

        $stats = [
            'total'      => Konsumen::count(),
            'aktif'      => Konsumen::whereHas('transaksis', fn ($t) => $t->aktif())->count(),
            'lunas'      => TransaksiPenjualan::where('status', 'lunas')->distinct('konsumen_id')->count('konsumen_id'),
            'total_bayar' => (float) DB::table('pembayarans')->join('transaksi_penjualans as t', 't.id', '=', 'pembayarans.transaksi_id')
                ->where('t.status', '!=', 'batal')->sum('pembayarans.nominal'),
        ];

        return view('konsumen.index', compact('konsumens', 'stats'));
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

    /** Pencarian untuk form transaksi (JSON). */
    public function cari(Request $request)
    {
        $q = trim((string) $request->get('q'));

        return Konsumen::query()
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
