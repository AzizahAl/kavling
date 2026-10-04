{{-- FORM RESERVASI.docx --}}
@php $metode = $d['metode'] ?? null; @endphp
<style>
    .rsv-kop td { vertical-align: middle; }
    .rsv-kop .nama { font-family: 'Calibri Light', Calibri, Carlito, Helvetica, sans-serif; font-size: 18pt; font-weight: bold; color: #00aa48; letter-spacing: .5pt; }
    .rsv-kop img { width: 19mm; height: auto; }
    .rsv-garis { border-top: 1.5px solid #000; margin: 4pt 0 10pt; }
    .rsv-judul td { border: 1px solid #000; padding: 5pt; text-align: center; font-size: 16pt; font-weight: bold; }
    .rsv-data { margin-top: 8pt; }
    .rsv-data td { padding: 3pt 0; }
    .rsv-data .lbl { width: 34mm; }
    .rsv-data .lbl2 { width: 36mm; white-space: nowrap; padding-left: 5mm; }
    .rsv-data .val2 { width: 38mm; }
    .rsv-pilih span { padding: 0 2pt; }
    .rsv-pilih .dipilih { border: 1px solid #000; border-radius: 8pt; padding: 0 4pt; font-weight: bold; }
    .rsv-sk-judul { margin: 10pt 0 5pt; font-weight: bold; text-decoration: underline; }
    .rsv-sk td { border: 1px solid #000; padding: 5pt 6pt; font-size: 10pt; text-align: left; }
    .rsv-sebaris td { padding: 0; }
    .rsv-sebaris .teks { width: 1%; white-space: nowrap; padding: 0 3pt; }
    .rsv-sk ol { margin: 0; padding-left: 14pt; }
    .rsv-sk li { margin-bottom: 2pt; }
    .rsv-sk .ttd-sel { text-align: center; width: 33.33%; padding-top: 6pt; font-size: 11pt; }
</style>

<table class="rsv-kop">
    <tr>
        <td class="nama">TECTONA RESIDEN</td>
        <td class="r"><img src="{{ $gambar('logo.png') }}" alt="Tectona Residen"></td>
    </tr>
</table>
<div class="rsv-garis"></div>

<table class="rsv-judul"><tr><td>Form Reservasi / Pemesanan</td></tr></table>

<table class="data rsv-data">
    <tr>
        <td class="lbl">Nama</td><td class="sep">:</td><td><x-isian penuh :nilai="$d['nama'] ?? null"/></td>
        <td class="lbl2">No Reservasi</td><td class="sep">:</td><td class="val2">@if ($isi)<x-isian penuh :nilai="$d['nomor'] ?? null"/>@else TR/RSV/20…/……@endif</td>
    </tr>
    <tr>
        <td class="lbl">NIK</td><td class="sep">:</td><td><x-isian penuh :nilai="$d['nik'] ?? null"/></td>
        <td class="lbl2">Tanggal</td><td class="sep">:</td><td><x-isian penuh :nilai="$d['tanggal'] ?? null"/></td>
    </tr>
    <tr>
        <td class="lbl">Alamat</td><td class="sep">:</td><td><x-isian penuh :nilai="$d['alamat'] ?? null"/></td>
        <td class="lbl2">Berlaku s/d</td><td class="sep">:</td><td><x-isian penuh :nilai="$d['berlaku'] ?? null"/></td>
    </tr>
    <tr>
        <td></td><td></td><td colspan="4"><x-isian penuh/></td>
    </tr>
    <tr>
        <td class="lbl">No HP</td><td class="sep">:</td><td colspan="4"><x-isian penuh :nilai="$d['hp'] ?? null"/></td>
    </tr>
    <tr>
        <td class="lbl">Blok</td><td class="sep">:</td>
        <td colspan="4">
            <table class="rsv-sebaris"><tr>
                <td style="width:30%"><x-isian penuh :nilai="$d['blok'] ?? null"/></td>
                <td class="teks">, Nomor Kavling :</td>
                <td style="width:20%"><x-isian penuh :nilai="$d['nomor_kavling'] ?? null"/></td>
                <td class="teks">, Luas :</td>
                <td><x-isian penuh :nilai="filled($d['luas'] ?? null) ? $d['luas'] . ' m²' : null"/></td>
            </tr></table>
        </td>
    </tr>
    <tr>
        <td class="lbl">Harga Kavling</td><td class="sep">:</td><td colspan="4"><x-isian penuh :nilai="$d['harga'] ?? null"/></td>
    </tr>
    <tr>
        <td class="lbl">Uang Sejumlah</td><td class="sep">:</td>
        <td colspan="4"><table><tr><td style="width:1%;padding:0 2pt 0 0">Rp</td><td style="padding:0"><x-isian penuh :nilai="$d['uang'] ?? null"/></td></tr></table></td>
    </tr>
    <tr>
        <td class="lbl">Terbilang</td><td class="sep">:</td><td colspan="4"><x-isian penuh :nilai="$d['terbilang'] ?? null"/></td>
    </tr>
    <tr>
        <td class="lbl">Metode Pembayaran</td><td class="sep">:</td>
        <td class="rsv-pilih" style="white-space:nowrap">
            @foreach (['tunai' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'Qris'] as $kunci => $label)
                <span @class(['dipilih' => $metode === $kunci])>{{ $label }}</span>{{ $loop->last ? '' : ' / ' }}
            @endforeach
        </td>
        <td class="lbl2">A/N Penyetor/Tranfer</td><td class="sep">:</td><td><x-isian penuh :nilai="$d['penyetor'] ?? null"/></td>
    </tr>
    <tr>
        <td colspan="3"></td>
        <td class="lbl2">Nama Bank</td><td class="sep">:</td><td><x-isian penuh :nilai="$d['bank'] ?? null"/></td>
    </tr>
    <tr>
        <td colspan="3"></td>
        <td class="lbl2">No Rek. Penyetor</td><td class="sep">:</td><td><x-isian penuh :nilai="$d['rekening'] ?? null"/></td>
    </tr>
</table>

<p class="rsv-sk-judul">Syarat Dan Ketentuan</p>
<table class="rsv-sk">
    <tr>
        <td colspan="2" style="width:50%">
            <ol>
                <li>Uang reservasi merupakan tanda keseriusan calon pembeli untuk memesan kavling pada proyek <b>TECTONA RESIDEN</b>.</li>
                <li>Reservasi hanya berlaku setelah pembayaran diterima oleh pihak penjual dan diverifikasi.</li>
                <li>Selama masa reservasi, kavling yang dipesan tidak akan ditawarkan kepada calon pembeli lain sesuai dengan jangka waktu yang telah ditentukan.</li>
                <li>Masa berlaku reservasi adalah 14<b> (empat belas) hari kalender</b> sejak tanggal pembayaran reservasi, kecuali disepakati lain secara tertulis.</li>
                <li>Pembeli wajib melanjutkan proses menjadi <b>Booking/Pemesanan</b> sebelum masa reservasi berakhir.</li>
            </ol>
        </td>
        <td style="width:50%">
            <ol start="6">
                <li>Apabila sampai batas waktu reservasi pembeli tidak memberikan konfirmasi atau tidak melanjutkan proses pemesanan, maka pihak penjual berhak membatalkan reservasi dan menawarkan kembali kavling tersebut kepada pihak lain.</li>
                <li>Uang reservasi akan diperhitungkan sebagai bagian dari pembayaran apabila transaksi dilanjutkan.</li>
                <li>Pengembalian uang reservasi mengikuti kebijakan perusahaan dan kesepakatan para pihak.</li>
                <li>Dengan menandatangani formulir ini, calon pembeli dianggap telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan yang berlaku</li>
            </ol>
        </td>
    </tr>
</table>
<table class="rsv-sk">
    <tr>
        <td class="ttd-sel">Pemesan<div style="height:50pt"></div>(@include('formulir.halaman._nama-ttd', ['nama' => $d['ttd_pemesan'] ?? null]))</td>
        <td class="ttd-sel">Marketing<div style="height:50pt"></div>(@include('formulir.halaman._nama-ttd', ['nama' => $d['ttd_marketing'] ?? null]))</td>
        <td class="ttd-sel">Pengelola Property<div style="height:50pt"></div>(@include('formulir.halaman._nama-ttd', ['nama' => $d['ttd_pengelola'] ?? null]))</td>
    </tr>
</table>
