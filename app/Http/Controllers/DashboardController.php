<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // =====================================================
        // GANTI SEMUA DATA DI BAWAH INI DENGAN QUERY DARI MODEL/DB ASLI
        // (Kavling, Transaksi, RAB, KasProyek, Agen, dst)
        // =====================================================

        $stats = [
            'total_kavling'          => 14,
            'tersedia'               => 14,
            'tersedia_percent'       => 100,
            'reservasi'              => 0,
            'booking'                => 0,
            'dp'                     => 0,
            'terjual'                => 0,

            'harga_aktif'            => 500000,
            'total_penjualan'        => 0,
            'pembayaran_masuk'       => 0,

            'piutang'                => 0,
            'saldo_kas'              => -500000,
            'realisasi_rab'          => 500000,

            'total_pemasukan'        => 0,
            'total_pengeluaran'      => 500000,
            'target_rab'             => 500000,
            'realisasi_rab_percent'  => 100,
        ];

        $statusKavling = [
            ['label' => 'Tersedia',  'value' => 14, 'percent' => 100, 'color' => '#16a34a'],
            ['label' => 'Reservasi', 'value' => 0,  'percent' => 0,   'color' => '#eab308'],
            ['label' => 'Booking',   'value' => 0,  'percent' => 0,   'color' => '#64748b'],
            ['label' => 'DP',       'value' => 0,  'percent' => 0,   'color' => '#334155'],
            ['label' => 'Terjual',   'value' => 0,  'percent' => 0,   'color' => '#0f172a'],
        ];

        $baseline = [
            ['label' => 'Luas Lahan',         'value' => '17,34 are'],
            ['label' => 'Jalan Dalam',        'value' => '5,3 m'],
            ['label' => 'Prima (A1–A7)',      'value' => '7 x 14 m'],
            ['label' => 'Standard (B1–B6)',   'value' => '7 x 10 m'],
            ['label' => 'Tipe B7 (Hook)',     'value' => 'TBD'],
            ['label' => 'Legal Lahan',        'value' => 'Girik (Proses AJB)', 'badge' => true],
        ];

        $transaksiTerbaru = [
            ['kode' => 'TRX-2026-0005', 'nama' => 'Budi Santoso', 'kavling' => 'A1', 'nominal' => 49000000, 'status' => 'DP 10%'],
            ['kode' => 'TRX-2026-0004', 'nama' => 'Siti Aminah',  'kavling' => 'B2', 'nominal' => 10000000, 'status' => 'Booking'],
            ['kode' => 'TRX-2026-0003', 'nama' => 'Ahmad R.',     'kavling' => 'A3', 'nominal' => 2000000,  'status' => 'Reservasi'],
        ];

        $inOutChart = ['in' => 0, 'out' => 500000];

        $agenTop = [
            ['id' => 'AG-004', 'nama' => 'Sara Haque',   'nilai_closing' => 'Rp 360M', 'funnel' => '12 / 8 / 4', 'top' => true],
            ['id' => 'AG-001', 'nama' => 'Ayesha Rahman', 'nilai_closing' => 'Rp 315M', 'funnel' => '15 / 5 / 3'],
            ['id' => 'AG-005', 'nama' => 'Tariq Alam',    'nilai_closing' => 'Rp 270M', 'funnel' => '10 / 4 / 2'],
            ['id' => 'AG-002', 'nama' => 'James Karim',   'nilai_closing' => 'Rp 225M', 'funnel' => '8 / 3 / 2'],
            ['id' => 'AG-003', 'nama' => 'Nadia Hossain', 'nilai_closing' => 'Rp 180M', 'funnel' => '5 / 2 / 1'],
        ];

        $penjualanChart = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            'values' => [150, 220, 175, 310, 270, 360],
        ];

        $hargaChart = [
            'labels' => ['Tahap 1', 'Tahap 2', 'Tahap 3', 'Tahap 4', 'Tahap 5'],
            'values' => [500000, 550000, 600000, 650000, 700000],
        ];

        $alokasiKas = [
            ['label' => 'Tanah',        'percent' => 50, 'color' => '#0f172a'],
            ['label' => 'Legal/Infra',  'percent' => 25, 'color' => '#16a34a'],
            ['label' => 'Marketing',    'percent' => 10, 'color' => '#eab308'],
            ['label' => 'Cadangan',     'percent' => 10, 'color' => '#94a3b8'],
            ['label' => 'Ops',          'percent' => 5,  'color' => '#475569'],
        ];

        $profitSharing = ['pengelola' => 80, 'mitra' => 20];

        return view('dashboard', compact(
            'stats',
            'statusKavling',
            'baseline',
            'transaksiTerbaru',
            'inOutChart',
            'agenTop',
            'penjualanChart',
            'hargaChart',
            'alokasiKas',
            'profitSharing'
        ));
    }
}