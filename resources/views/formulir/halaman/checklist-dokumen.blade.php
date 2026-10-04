{{-- MARKETING TOOLKIT — Checklist Dokumen Konsumen --}}
@include('formulir.halaman._toolkit-gaya')

<p class="tk-judul">CHECKLIST DOKUMEN KONSUMEN</p>
<table class="grid">
    <thead><tr><th style="width:40%">Dokumen</th><th style="width:15%">Diterima</th><th>Keterangan</th></tr></thead>
    <tbody>
        @foreach (['KTP', 'KK', 'NPWP (bila diperlukan)', 'Bukti pembayaran', 'Form data konsumen', 'Form reservasi', 'Form booking', 'SPK', 'PPJBS/PPJB', 'Dokumen pendukung lain'] as $dok)
            <tr><td style="height:16pt">{{ $dok }}</td><td class="c"><span class="kotak" style="margin:0"></span></td><td></td></tr>
        @endforeach
    </tbody>
</table>

<p class="tk-sub" style="margin-top:16pt">Arsip</p>
<table class="data tk-form tk-form-lbl-pendek">
    <tr><td class="lbl">Map/folder konsumen</td><td><x-isian penuh/></td></tr>
    <tr><td class="lbl">Nomor arsip</td><td><x-isian penuh/></td></tr>
    <tr><td class="lbl">Tanggal lengkap</td><td><x-isian penuh/></td></tr>
</table>
