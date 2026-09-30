<?php

namespace App\Http\Controllers;

use App\Models\Agen;
use App\Models\Kavling;
use App\Models\Konsumen;
use App\Models\RiwayatPembayaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KonsumenController extends Controller
{
    public function index(Request $request)
    {
        // withSum dipakai untuk menentukan status DP di tabel (total pembayaran masuk per konsumen)
        $query = Konsumen::with(['kavling', 'agen'])->withSum('riwayatPembayarans', 'nominal');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('id_konsumen', 'like', "%{$search}%")
                  ->orWhereHas('kavling', fn ($k) => $k->where('kode_kavling', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status_transaksi', $status);
        }

        if ($metode = $request->input('metode')) {
            $query->where('skema_bayar', $metode);
        }

        if ($agenId = $request->input('agen')) {
            $query->where('agen_id', $agenId);
        }

        if ($dokumen = $request->input('dokumen')) {
            // format: status_ppjb:proses / status_ajb:selesai dst.
            [$field, $value] = array_pad(explode(':', $dokumen), 2, null);
            if (in_array($field, ['status_ppjb', 'status_ajb', 'status_reservasi', 'status_booking']) && $value) {
                $query->where($field, $value);
            }
        }

        $konsumens = $query->orderByDesc('id')->get();

        $stats = [
            'total_konsumen'         => Konsumen::count(),
            'reservasi'              => Konsumen::where('status_transaksi', 'reservasi')->count(),
            'booking'                => Konsumen::where('status_transaksi', 'booking')->count(),
            'dp'                     => Konsumen::where('status_transaksi', 'dp')->count(),
            // Cash/Lunas & Angsuran sekarang dihitung dari Metode Pembayaran
            'cash_lunas'             => Konsumen::where('skema_bayar', 'cash_lunas')->count(),
            'angsuran'               => Konsumen::where('skema_bayar', 'angsuran')->count(),
            'total_nilai_penjualan'  => Kavling::whereIn('id', Konsumen::pluck('kavling_id'))->sum('harga_jual'),
            'total_pembayaran_masuk' => RiwayatPembayaran::sum('nominal'),
        ];

        $kavlings = Kavling::orderBy('kode_kavling')->get();
        $agens    = Agen::orderBy('nama_agen')->get();

        return view('konsumen.index', compact('konsumens', 'stats', 'kavlings', 'agens'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap'       => 'required|string|max:255',
            'nik'                => 'required|digits:16|unique:konsumens,nik',
            'no_hp'              => 'required|string|max:20',
            'email'              => 'nullable|email|max:255',
            'alamat'             => 'required|string',
            'kavling_id'         => 'required|exists:kavlings,id',
            'status_transaksi'   => 'required|in:reservasi,booking,dp',
            'agen_id'            => 'required|exists:agens,id',
            'metode_pembayaran'  => 'required|in:cash_lunas,angsuran',
            'tenor'              => 'required_if:metode_pembayaran,angsuran|nullable|integer|min:18',
            'nominal_reservasi'  => 'nullable|numeric|min:0',
            'nominal_booking'    => 'nullable|numeric|min:0',
            'down_payment'       => 'nullable|numeric|min:0',
            'sudah_bayar'        => 'required|in:0,1',
            'tanggal_transaksi'  => 'required_if:sudah_bayar,1|nullable|date',
            'total_bayar'        => 'required_if:sudah_bayar,1|nullable|numeric|min:1',
            'catatan'            => 'nullable|string',
        ], [
            'tenor.required_if'             => 'Tenor wajib diisi untuk metode Angsuran.',
            'tenor.min'                     => 'Tenor minimal 18 bulan.',
            'tanggal_transaksi.required_if' => 'Tanggal transaksi wajib diisi jika sudah melakukan pembayaran.',
            'total_bayar.required_if'       => 'Total bayar wajib diisi jika sudah melakukan pembayaran.',
        ]);

        // Harga jual selalu diambil dari Master Kavling (bukan dari input user)
        $kavling   = Kavling::findOrFail($validated['kavling_id']);
        $hargaJual = (float) $kavling->harga_jual;

        $reservasi = (float) ($validated['nominal_reservasi'] ?? 0);
        $booking   = (float) ($validated['nominal_booking'] ?? 0);
        $dp        = (float) ($validated['down_payment'] ?? 0);

        // DP minimal 15% dari harga jual
        $minDp = ceil($hargaJual * 0.15);
        if ($dp < $minDp) {
            return back()
                ->withErrors(['down_payment' => 'DP minimal 15% dari harga jual (Rp' . number_format($minDp, 0, ',', '.') . ').'])
                ->withInput();
        }

        $sudahBayar = $validated['sudah_bayar'] === '1';
        $totalBayar = $sudahBayar ? (float) $validated['total_bayar'] : 0;

        if ($totalBayar > $hargaJual) {
            return back()
                ->withErrors(['total_bayar' => 'Total bayar tidak boleh melebihi harga jual kavling.'])
                ->withInput();
        }

        // Alokasi pembayaran berurutan: reservasi -> booking -> DP
        $terbayarReservasi = min($totalBayar, $reservasi);
        $terbayarBooking   = min(max($totalBayar - $reservasi, 0), $booking);

        $statusReservasi = $reservasi <= 0 ? 'belum' : ($terbayarReservasi >= $reservasi ? 'selesai' : 'proses');
        $statusBooking   = $booking <= 0   ? 'belum' : ($terbayarBooking >= $booking ? 'selesai' : 'proses');

        $data = collect($validated)
            ->except(['metode_pembayaran', 'tenor', 'sudah_bayar', 'total_bayar'])
            ->toArray();

        $data['id_konsumen']       = $this->generateIdKonsumen();
        $data['skema_bayar']       = $validated['metode_pembayaran'];
        $data['jumlah_angsuran']   = $validated['metode_pembayaran'] === 'angsuran' ? (int) $validated['tenor'] : 0;
        $data['nominal_reservasi'] = $reservasi;
        $data['nominal_booking']   = $booking;
        $data['down_payment']      = $dp;
        // Kalau belum bayar, tanggal transaksi = tanggal data dibuat
        $data['tanggal_transaksi'] = $sudahBayar ? $validated['tanggal_transaksi'] : now()->toDateString();
        $data['status_reservasi']  = $statusReservasi;
        $data['status_booking']    = $statusBooking;
        $data['status_ppjb']       = 'belum';
        $data['status_ajb']        = 'belum';

        $keterangan = [
            'reservasi' => 'Pembayaran Reservasi (NUP)',
            'booking'   => 'Pembayaran Booking Fee',
            'dp'        => 'Pembayaran Down Payment (DP)',
        ][$validated['status_transaksi']];

        DB::transaction(function () use ($data, $sudahBayar, $totalBayar, $keterangan) {
            $konsumen = Konsumen::create($data);

            if ($sudahBayar) {
                $konsumen->riwayatPembayarans()->create([
                    'tanggal'    => $data['tanggal_transaksi'],
                    'keterangan' => $keterangan,
                    'nominal'    => $totalBayar,
                ]);
            }
        });

        return redirect()->route('konsumen.index')->with('success', 'Konsumen baru berhasil ditambahkan.');
    }

    public function show(Konsumen $konsumen)
    {
        $konsumen->load(['kavling', 'agen', 'riwayatPembayarans' => function ($q) {
            $q->orderByDesc('tanggal');
        }]);

        $hargaJualNett = (float) ($konsumen->kavling->harga_jual ?? 0);

        $targetReservasi = (float) ($konsumen->nominal_reservasi ?? 0);
        $targetBooking   = (float) ($konsumen->nominal_booking ?? 0);
        $targetDp        = (float) ($konsumen->down_payment ?? 0);
        $targetDpTotal   = $targetReservasi + $targetBooking + $targetDp;

        $totalTerbayar = (float) $konsumen->riwayatPembayarans->sum('nominal');
        $sisaTagihan   = $hargaJualNett - $totalTerbayar;

        // Sisa setelah Reservasi + Booking + DP (dilunasi cash atau diangsur)
        $sisaSetelahDp    = max($hargaJualNett - $targetDpTotal, 0);
        $angsuranPerBulan = $konsumen->jumlah_angsuran > 0
            ? $sisaSetelahDp / $konsumen->jumlah_angsuran
            : 0;

        // ===== Progress per tahap (berurutan). Total semua target = Harga Jual Nett =====
        $definisiTahap = [
            ['key' => 'reservasi', 'nama' => 'Reservasi (NUP)', 'target' => $targetReservasi],
            ['key' => 'booking',   'nama' => 'Booking Fee',     'target' => $targetBooking],
            ['key' => 'dp',        'nama' => 'Down Payment (DP)', 'target' => $targetDp],
            [
                'key'    => 'pelunasan',
                'nama'   => $konsumen->skema_bayar === 'angsuran' ? 'Angsuran' : 'Pelunasan',
                'target' => $sisaSetelahDp,
            ],
        ];

        $stages     = [];
        $sisaDana   = $totalTerbayar; // dana yang belum dialokasikan
        $aktifKetemu = false;

        foreach ($definisiTahap as $tahap) {
            if ($tahap['target'] <= 0) {
                continue; // tahap tanpa target tidak ditampilkan
            }

            $terbayar = min(max($sisaDana, 0), $tahap['target']);
            $sisaDana -= $tahap['target'];

            if ($terbayar >= $tahap['target']) {
                $status = 'selesai';
            } elseif ($terbayar > 0) {
                $status = 'proses';
            } else {
                $status = 'belum';
            }

            // Tahap aktif = tahap pertama yang belum selesai
            $aktif = false;
            if ($status !== 'selesai' && !$aktifKetemu) {
                $aktif = true;
                $aktifKetemu = true;
            }

            $stages[] = [
                'key'      => $tahap['key'],
                'nama'     => $tahap['nama'],
                'target'   => $tahap['target'],
                'terbayar' => $terbayar,
                'sisa'     => $tahap['target'] - $terbayar,
                'persen'   => round(($terbayar / $tahap['target']) * 100, 1),
                'status'   => $status,
                'aktif'    => $aktif,
            ];
        }

        // Progress total terhadap Harga Jual Nett
        $progressPercent = $hargaJualNett > 0
            ? round(min($totalTerbayar / $hargaJualNett, 1) * 100, 1)
            : 0;

        $summary = [
            'harga_jual_nett'    => $hargaJualNett,
            'total_terbayar'     => $totalTerbayar,
            'sisa_tagihan'       => $sisaTagihan,
            'target_reservasi'   => $targetReservasi,
            'target_booking'     => $targetBooking,
            'target_dp'          => $targetDp,
            'target_dp_total'    => $targetDpTotal,
            'progress_percent'   => $progressPercent,
            'angsuran_per_bulan' => $angsuranPerBulan,
            'stages'             => $stages,
        ];

        return view('konsumen.show', compact('konsumen', 'summary'));
    }

    /**
     * Data kwitansi (dipakai bersama oleh halaman lihat & unduh PDF).
     * Otomatis menyesuaikan metode: CASH / LUNAS atau ANGSURAN.
     */
    private function siapkanKwitansi(Konsumen $konsumen, RiwayatPembayaran $riwayat): array
    {
        // Pastikan transaksi ini memang milik konsumen tersebut
        $riwayat = $konsumen->riwayatPembayarans()->whereKey($riwayat->id)->firstOrFail();
        $konsumen->load(['kavling', 'agen']);

        // ===== Pengaturan kop kwitansi (ubah di sini bila perlu) =====
        $namaProyek   = 'TECTONA RESIDENCE';
        $alamatProyek = 'Land Management'; // ganti dengan alamat proyek bila perlu
        $kodeKwitansi = 'TR';

        // ===== Perhitungan dari data sistem =====
        $hargaJual = (float) ($konsumen->kavling->harga_jual ?? 0);
        $nominal   = (float) $riwayat->nominal;

        $terbayarSampaiIni = (float) $konsumen->riwayatPembayarans()
            ->where('id', '<=', $riwayat->id)
            ->sum('nominal');
        $sisaTagihan = max($hargaJual - $terbayarSampaiIni, 0);
        $lunas       = $sisaTagihan <= 0;

        // Metode angsuran
        $tenor    = (int) $konsumen->jumlah_angsuran;
        $angsuran = $konsumen->skema_bayar === 'angsuran' && $tenor > 0;

        // Sisa yang diangsur = Harga Jual - (Reservasi + Booking + DP)
        $sisaDiangsur = max(
            $hargaJual - ((float) $konsumen->nominal_reservasi + (float) $konsumen->nominal_booking + (float) $konsumen->down_payment),
            0
        );
        $cicilan = $angsuran ? $sisaDiangsur / $tenor : 0;

        $tgl    = $riwayat->tanggal->copy()->locale('id');
        $romawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        $noKwitansi = sprintf('%03d/%s/%s/%s', $riwayat->id, $kodeKwitansi, $romawi[(int) $tgl->format('n')], $tgl->format('y'));

        $angka = fn ($n) => number_format($n, 0, ',', '.');

        // Baris ringkasan: [label, nilai, tebal?, kotak status?]
        $baris = [
            ['Total Tagihan',    $angka($hargaJual),         false, false],
            ['Total Pembayaran', $angka($terbayarSampaiIni), false, false],
            ['Sisa Tagihan',     $angka($sisaTagihan),       true,  false],
            ['Pembayaran',       $angsuran ? 'ANGSURAN' : 'CASH / LUNAS', false, false],
        ];
        if ($angsuran) {
            $baris[] = ['Tenor',              $tenor . ' BULAN',     false, false];
            $baris[] = ['Sisa yang Diangsur', $angka($sisaDiangsur), false, false];
            $baris[] = ['Angsuran per Bulan', $angka($cicilan),      false, false];
        }
        $baris[] = ['Status', $lunas ? 'LUNAS' : 'BELUM LUNAS', false, true];

        $terakhir = count($baris) - 1;

        // Nama petugas = akun yang sedang login; tanggal tanda tangan = hari ini (saat kwitansi dibuka/diunduh)
        $user = auth()->user();
        $petugas = $user
            ? ($user->name ?? $user->nama ?? $user->username ?? 'Admin')
            : 'Admin';
        $tanggalTtd = now()->locale('id')->translatedFormat('d F Y');

        return [
            'namaProyek'   => $namaProyek,
            'alamatProyek' => $alamatProyek,
            'tanggal'      => $tgl->translatedFormat('d F Y'),
            'noKwitansi'   => $noKwitansi,
            'nama'         => strtoupper($konsumen->nama_lengkap),
            'terbilang'    => ucwords($this->terbilang((int) round($nominal))),
            'keterangan'   => strtoupper($riwayat->keterangan) . ' KAVLING ' . ($konsumen->kavling->kode_kavling ?? '-'),
            'tglMasuk'     => $tgl->translatedFormat('d - M - y'),
            'nominal'      => 'Rp' . $angka($nominal),
            'baris'        => $baris,
            'kanan'        => [0 => $tanggalTtd, 1 => 'Keuangan', $terakhir => $petugas],
            'terakhir'     => $terakhir,
            'tinggiTengah' => count($baris) > 6 ? 460 : 640, // twips, ruang tanda tangan
            'lunas'        => $lunas,
            'angsuran'     => $angsuran,
        ];
    }

    /**
     * LIHAT kwitansi (preview di browser). Dari sini bisa Unduh PDF.
     */
    public function kwitansi(Konsumen $konsumen, RiwayatPembayaran $riwayat)
    {
        $k = $this->siapkanKwitansi($konsumen, $riwayat);

        return view('konsumen.kwitansi', compact('konsumen', 'riwayat', 'k'));
    }

    /**
     * Unduh kwitansi sebagai file PDF.
     */
    public function kwitansiUnduh(Konsumen $konsumen, RiwayatPembayaran $riwayat)
    {
        $k = $this->siapkanKwitansi($konsumen, $riwayat);

        $namaFile = 'Kwitansi-' . preg_replace('/[^A-Za-z0-9\-]+/', '-', $k['noKwitansi'] . '-' . $konsumen->nama_lengkap) . '.pdf';

        return Pdf::loadView('konsumen.kwitansi', [
                'konsumen' => $konsumen,
                'riwayat'  => $riwayat,
                'k'        => $k,
                'pdf'      => true, // mode PDF: toolbar disembunyikan, ukuran kertas disesuaikan
            ])
            ->setPaper('a4', 'portrait')
            ->download($namaFile);
    }

    /** Angka -> kalimat, contoh 250000 => "Dua ratus lima puluh ribu rupiah" */
    private function terbilang(int $n): string
    {
        if ($n === 0) {
            return 'Nol rupiah';
        }

        return ucfirst(trim($this->terbilangAngka($n))) . ' rupiah';
    }

    private function terbilangAngka(int $n): string
    {
        $dasar = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($n < 12)  return $dasar[$n];
        if ($n < 20)  return $this->terbilangAngka($n - 10) . ' belas';
        if ($n < 100) return $this->terbilangAngka(intdiv($n, 10)) . ' puluh' . ($n % 10 ? ' ' . $this->terbilangAngka($n % 10) : '');
        if ($n < 200) return 'seratus' . ($n - 100 ? ' ' . $this->terbilangAngka($n - 100) : '');
        if ($n < 1000) return $this->terbilangAngka(intdiv($n, 100)) . ' ratus' . ($n % 100 ? ' ' . $this->terbilangAngka($n % 100) : '');
        if ($n < 2000) return 'seribu' . ($n - 1000 ? ' ' . $this->terbilangAngka($n - 1000) : '');
        if ($n < 1000000) return $this->terbilangAngka(intdiv($n, 1000)) . ' ribu' . ($n % 1000 ? ' ' . $this->terbilangAngka($n % 1000) : '');
        if ($n < 1000000000) return $this->terbilangAngka(intdiv($n, 1000000)) . ' juta' . ($n % 1000000 ? ' ' . $this->terbilangAngka($n % 1000000) : '');
        if ($n < 1000000000000) return $this->terbilangAngka(intdiv($n, 1000000000)) . ' miliar' . ($n % 1000000000 ? ' ' . $this->terbilangAngka($n % 1000000000) : '');

        return $this->terbilangAngka(intdiv($n, 1000000000000)) . ' triliun' . ($n % 1000000000000 ? ' ' . $this->terbilangAngka($n % 1000000000000) : '');
    }

    public function buatSpk(Konsumen $konsumen)
    {
        // TODO: generate dokumen SPK
        abort(501, 'Fitur pembuatan SPK belum diimplementasikan.');
    }

    private function generateIdKonsumen(): string
    {
        $tahun = now()->year;
        $terakhir = Konsumen::where('id_konsumen', 'like', "CUS-{$tahun}-%")
            ->orderByDesc('id_konsumen')
            ->first();

        $urutan = 1;
        if ($terakhir) {
            $bagian = explode('-', $terakhir->id_konsumen);
            $urutan = (int) end($bagian) + 1;
        }

        return sprintf('CUS-%d-%04d', $tahun, $urutan);
    }
}