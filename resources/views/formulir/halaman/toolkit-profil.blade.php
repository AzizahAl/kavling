{{-- MARKETING TOOLKIT — profil, daftar kavling, pricelist. Semua angka dibaca dari sistem (Pengaturan, Master Kavling, Skema Harga). --}}
@include('formulir.halaman._toolkit-gaya')
@php
    $p = \App\Services\Pengaturan::semua();
    $harga = app(\App\Services\HargaService::class);
    $hargaM2 = $harga->hargaAktif();
    $tahapAktif = $harga->tahapAktif();
    $kavlings = \App\Models\Kavling::orderBy('blok')->orderByRaw('CAST(SUBSTRING(`no`, 2) AS UNSIGNED)')->get();
    $tahap = $harga->daftarTahap();
    $nama = mb_strtoupper($p['nama_proyek'] ?? 'Proyek');
    $isi = fn ($v, $akhiran = '') => $v === null || $v === '' ? '—' : $v . $akhiran;
@endphp

<p class="c b" style="font-size:22pt;margin:0">{{ $nama }}</p>
<p class="c b" style="font-size:15pt;margin:0 0 4pt">MARKETING TOOLKIT</p>
<p class="tk-judul" style="margin-top:10pt">PROFIL SINGKAT {{ $nama }}</p>

<p class="tk-sub">Lokasi</p>
<p>{{ $isi($p['alamat_proyek']) }}</p>

<p class="tk-sub">Data Proyek</p>
<table class="grid">
    <thead><tr><th style="width:45%">Item</th><th>Data</th></tr></thead>
    <tbody>
        @foreach ([
            ['Luas lahan', $isi($p['luas_lahan_are'] !== null ? angka($p['luas_lahan_are'], 2) : null, ' are')],
            ['Jumlah kavling', $isi($p['jumlah_kavling'], ' unit')],
            ['Lebar jalan dalam', $isi($p['lebar_jalan_m'] !== null ? angka($p['lebar_jalan_m'], 1) : null, ' m')],
            ['Status legal lahan', $isi($p['status_legal_lahan'])],
        ] as [$item, $data])
            <tr><td>{{ $item }}</td><td>{{ $data }}</td></tr>
        @endforeach
    </tbody>
</table>

<p class="tk-judul pisah" style="margin-top:0">DAFTAR KAVLING</p>
<table class="grid tk-kavling">
    <thead><tr><th style="width:12%">Kode</th><th style="width:16%">Tipe</th><th style="width:20%">Ukuran</th><th style="width:12%">Luas</th><th style="width:20%">Harga Jual</th><th>Status</th></tr></thead>
    <tbody>
        @foreach ($kavlings as $k)
            <tr>
                <td class="c">{{ $k->kode_kavling }}</td>
                <td class="c">{{ $k->tipe }}</td>
                <td class="c">{{ $k->ukuran ?: '—' }}</td>
                <td class="c">{{ $k->luas ? angka($k->luas) . ' m²' : 'Belum final' }}</td>
                <td class="c">{{ $k->status === 'tersedia' ? ($k->harga_jual ? rupiah($k->harga_jual) : 'Hubungi admin') : '—' }}</td>
                <td class="c">{{ $k->label_status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<p class="tk-catatan">Harga jual kavling tersedia mengikuti {{ $tahapAktif?->nama_tahap ?? 'tahap aktif' }} ({{ rupiah($hargaM2) }}/m²) per {{ tanggal(now()) }}. Harga final mengikuti status unit saat transaksi dibuat.</p>

<p class="tk-judul pisah" style="margin-top:0">PRICELIST &amp; SKEMA KENAIKAN HARGA</p>
<p class="tk-sub">Pricelist per Tipe ({{ $tahapAktif?->nama_tahap ?? 'Tahap aktif' }})</p>
<table class="grid">
    <thead><tr><th>Tipe</th><th>Ukuran</th><th>Luas</th><th>Harga/m²</th><th>Harga</th></tr></thead>
    <tbody>
        @foreach (\App\Models\Kavling::TIPE as $tipe => $t)
            <tr>
                <td class="c">{{ $tipe }}</td>
                <td class="c">{{ $t['ukuran'] }}</td>
                <td class="c">{{ $t['luas'] ? $t['luas'] . ' m²' : 'Sesuai luas final' }}</td>
                <td class="c">{{ rupiah($hargaM2) }}</td>
                <td class="c">{{ $t['luas'] ? rupiah($t['luas'] * $hargaM2) : 'Luas × harga/m²' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p class="tk-sub">Tahap Kenaikan Harga</p>
<table class="grid">
    <thead><tr><th>Tahap</th><th>Harga/m²</th><th>Berlaku Saat</th></tr></thead>
    <tbody>
        @forelse ($tahap as $t)
            <tr>
                <td class="c">{{ $t->nama_tahap }}{{ $tahapAktif?->is($t) ? ' (aktif)' : '' }}</td>
                <td class="c">{{ rupiah($t->harga_per_m2) }}</td>
                <td class="c">{{ $t->unit_mulai }}–{{ $t->unit_sampai }} kavling sudah bertransaksi</td>
            </tr>
        @empty
            <tr><td class="c" colspan="3">Belum ada tahap harga.</td></tr>
        @endforelse
    </tbody>
</table>
