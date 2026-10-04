{{-- MARKETING TOOLKIT — Form Booking --}}
@include('formulir.halaman._toolkit-gaya')

<p class="tk-judul">FORM BOOKING</p>
<table class="data tk-form tk-form-lbl-pendek">
    <tr><td class="lbl">Nomor Booking</td><td><x-isian penuh :nilai="$d['nomor'] ?? null"/></td></tr>
    <tr><td class="lbl">Tanggal</td><td><x-isian penuh :nilai="$d['tanggal_booking'] ?? null"/></td></tr>
    <tr><td class="lbl">Nama konsumen</td><td><x-isian penuh :nilai="$d['nama'] ?? null"/></td></tr>
    <tr><td class="lbl">No. WhatsApp</td><td><x-isian penuh :nilai="$d['hp'] ?? null"/></td></tr>
    <tr><td class="lbl">Unit</td><td><x-isian penuh :nilai="$d['unit'] ?? null"/></td></tr>
    <tr><td class="lbl">Harga final</td><td><x-isian penuh :nilai="$d['harga_rp'] ?? null"/></td></tr>
    <tr><td class="lbl">Booking fee</td><td><x-isian penuh :nilai="$d['booking_rp'] ?? null"/></td></tr>
    <tr><td class="lbl">Jatuh tempo tindak lanjut</td><td><x-isian penuh/></td></tr>
</table>
<p class="j" style="margin-top:8pt">Ketentuan booking: booking fee Rp2.000.000. Ketentuan pembatalan mengikuti kebijakan proyek yang berlaku dan harus dijelaskan sebelum pembayaran.</p>

<p class="tk-sub">Skema Pembayaran</p>
<table class="grid">
    <thead><tr><th style="width:30%">Komponen</th><th style="width:35%">Nominal</th><th>Tanggal/Jadwal</th></tr></thead>
    <tbody>
        @foreach (['booking' => 'Booking fee', 'dp' => 'DP', 'angsuran' => 'Angsuran', 'lainnya' => 'Lainnya'] as $kunci => $label)
            <tr>
                <td style="height:18pt">{{ $label }}</td>
                <td>Rp {{ $d['skema'][$kunci]['nominal'] ?? '' }}</td>
                <td>{{ $d['skema'][$kunci]['jadwal'] ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@include('formulir.halaman._toolkit-ttd')
