{{-- MARKETING TOOLKIT — profil, product knowledge, pricelist --}}
@include('formulir.halaman._toolkit-gaya')

<p class="c b" style="font-size:22pt;margin:0">TECTONA RESIDEN</p>
<p class="c b" style="font-size:15pt;margin:0 0 4pt">MARKETING TOOLKIT</p>
<p class="tk-judul" style="margin-top:10pt">PROFIL SINGKAT TECTONA RESIDEN</p>

<p class="tk-sub">Tentang Proyek</p>
<p class="j">Tectona Residen adalah proyek kavling siap bangun yang dipasarkan untuk konsumen yang ingin berinvestasi di property (tanah) dan keluarga yang sedang mencari pilihan lahan dengan ukuran terstruktur, akses jalan internal yang layak, serta proses administrasi yang disiapkan secara bertahap.</p>

<p class="tk-sub">Lokasi</p>
<p>Jl. Panjaitan, Kp. Rancabuaya, Desa Purbayani, Kecamatan Caringin Kabupaten Garut Selatan.</p>

<p class="tk-sub">Data Master Proyek</p>
<table class="grid">
    <thead><tr><th style="width:45%">Item</th><th>Data</th></tr></thead>
    <tbody>
        @foreach ([
            ['Luas tanah master sementara', '17,34 are'],
            ['Total kavling', '14 unit'],
            ['Block A', 'A1–A7 / Prima'],
            ['Block B', 'B1–B7 / Standard'],
            ['Jalan gerbang', '±4 m'],
            ['Jalan internal', '±5,3 m'],
            ['Panjang jalan internal', '±54 m'],
            ['Area pendukung', 'Taman, area putar balik, gerbang, drainase'],
        ] as [$item, $data])
            <tr><td>{{ $item }}</td><td>{{ $data }}</td></tr>
        @endforeach
    </tbody>
</table>

<p class="tk-sub">Status Legalitas</p>
<p class="j">Status tanah: girik ; proses administrasi menuju AJB dan terkait (masuk) program TORA (Tanah Objek Reforma Agraria). Untuk progres status tanah akan di informasikan seara berkala oleh tim administrasi.</p>

<p class="tk-judul pisah" style="margin-top:0">PRODUCT KNOWLEDGE &amp; DAFTAR KAVLING</p>
@foreach ([
    ['Block A — PRIMA', collect(range(1, 7))->map(fn ($n) => ["A$n", 'Prima', '7 × 14 m', '98 m²', 'Rp49.000.000*'])],
    ['Block B — STANDARD', collect(range(1, 6))->map(fn ($n) => ["B$n", 'Standard', '7 × 10 m', '70 m²', 'Rp35.000.000*'])->push(['B7', 'Standard / Hook', 'Bentuk khusus', 'Tidak dibulatkan di toolkit', 'Hubungi admin'])],
] as [$blok, $baris])
    <p class="tk-sub">{{ $blok }}</p>
    <table class="grid tk-kavling">
        <thead><tr><th style="width:7%">Unit</th><th style="width:14%">Tipe</th><th style="width:13%">Ukuran</th><th style="width:15%">Luas</th><th style="width:16%">Harga awal*</th><th>Status</th></tr></thead>
        <tbody>
            @foreach ($baris as $b)
                <tr>@foreach ($b as $sel)<td class="c">{{ $sel }}</td>@endforeach<td class="c" style="white-space:nowrap">Available / Reserved / Booked / Sold</td></tr>
            @endforeach
        </tbody>
    </table>
@endforeach
<p class="tk-catatan">*Perhitungan harga A1–A7 dan B1–B6 menggunakan Rp500.000/m². Harga final wajib mengikuti pricelist/status unit terbaru dari admin.</p>

<p class="tk-sub">Kode Status Unit</p>
<table class="grid">
    <thead><tr><th style="width:30%">Status</th><th>Makna</th></tr></thead>
    <tbody>
        <tr><td>AVAILABLE</td><td>Unit masih dapat ditawarkan.</td></tr>
        <tr><td>RESERVED</td><td>Unit sedang ditahan melalui reservasi sesuai ketentuan.</td></tr>
        <tr><td>BOOKED</td><td>Unit telah masuk tahap booking.</td></tr>
        <tr><td>SOLD</td><td>Unit sudah terjual/terikat sesuai administrasi proyek.</td></tr>
    </tbody>
</table>

<p class="tk-judul pisah" style="margin-top:0">PRICELIST &amp; SKEMA KENAIKAN HARGA</p>
<p class="tk-sub">Pricelist Awal</p>
<table class="grid">
    <thead><tr><th>Tipe</th><th>Ukuran</th><th>Luas</th><th>Harga/m²</th><th>Harga awal</th></tr></thead>
    <tbody>
        <tr><td class="c">Prima</td><td class="c">7×14 m</td><td class="c">98 m²</td><td class="c">Rp500.000</td><td class="c">Rp49.000.000</td></tr>
        <tr><td class="c">Standard</td><td class="c">7×10 m</td><td class="c">70 m²</td><td class="c">Rp500.000</td><td class="c">Rp35.000.000</td></tr>
        <tr><td class="c">Standard B7</td><td class="c">Hook/bentuk khusus</td><td class="c">Cek admin</td><td class="c">Rp500.000*</td><td class="c">Cek admin</td></tr>
    </tbody>
</table>
<p class="tk-catatan">*B7 wajib dihitung berdasarkan luas final yang disepakati. Jangan menjanjikan nominal sebelum luas dan harga final dikonfirmasi.</p>

<p class="tk-sub">Tangga Kenaikan Harga</p>
<table class="grid">
    <thead><tr><th>Tahap</th><th>Harga/m²</th><th>Trigger</th></tr></thead>
    <tbody>
        <tr><td class="c">Harga awal</td><td class="c">Rp500.000</td><td class="c">Peluncuran</td></tr>
        <tr><td class="c">Tahap 2</td><td class="c">Rp550.000</td><td class="c">Setelah 3 unit terjual</td></tr>
        <tr><td class="c">Tahap 3</td><td class="c">Rp600.000</td><td class="c">Setelah 6 unit terjual</td></tr>
        <tr><td class="c">Tahap berikutnya</td><td class="c">Naik Rp50.000/m²</td><td class="c">Setiap tambahan 3 unit terjual</td></tr>
    </tbody>
</table>
