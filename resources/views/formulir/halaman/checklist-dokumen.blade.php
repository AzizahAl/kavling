{{-- Checklist Dokumen Konsumen — sesuai yang dicatat sistem: identitas, kwitansi per jenis pembayaran, formulir, dan status dokumen (SPK, PPJB, AJB). --}}
@include('formulir.halaman._toolkit-gaya')

<p class="tk-judul">CHECKLIST DOKUMEN KONSUMEN</p>
<table class="data tk-form tk-form-lbl-pendek">
    @foreach (['ID Konsumen', 'Nama Lengkap (KTP)', 'Kode Kavling', 'ID Transaksi'] as $label)
        <tr><td class="lbl">{{ $label }}</td><td><x-isian penuh/></td></tr>
    @endforeach
</table>

<table class="grid" style="margin-top:12pt">
    <thead><tr><th style="width:36%">Dokumen</th><th style="width:30%">Status</th><th style="width:16%">Tanggal</th><th>Keterangan</th></tr></thead>
    <tbody>
        @php
            $dok = ['KTP (sesuai NIK)', 'Form Data Konsumen', 'Form Reservasi', 'Form Booking'];
            $kwitansi = collect(\App\Models\Pembayaran::JENIS)->map(fn ($l) => 'Kwitansi ' . $l)->values()->all();
            $legal = array_values(\App\Models\ChecklistLegal::ITEM);
        @endphp
        @foreach ([...$dok, ...$kwitansi] as $d)
            <tr><td style="height:16pt">{{ $d }}</td><td><span class="kotak"></span>Ada &nbsp; <span class="kotak"></span>Belum</td><td></td><td></td></tr>
        @endforeach
        @foreach ($legal as $d)
            <tr><td style="height:16pt">{{ $d }}</td><td><span class="kotak"></span>Belum <span class="kotak"></span>Proses <span class="kotak"></span>Selesai</td><td></td><td></td></tr>
        @endforeach
    </tbody>
</table>
<p class="tk-catatan">Status SPK, PPJB, dan AJB dicatat admin di Data Konsumen › Dokumen.</p>
