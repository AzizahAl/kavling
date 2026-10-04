{{-- LAMPIRAN B — Jadwal Pembayaran --}}
@include('formulir.halaman._ppjb-gaya')
@include('formulir.halaman._lampiran-kepala', ['judulLampiran' => 'JADWAL PEMBAYARAN', 'lampiran' => 'LAMPIRAN B'])

<p class="lamp-sub">RINCIAN PEMBAYARAN</p>
<table class="data ppjb-data" style="width:auto">
    <tr><td class="lbl" style="width:45mm">Harga Kavling</td><td class="sep">:</td><td>Rp <x-isian lebar="55mm" :nilai="$d['harga'] ?? null"/></td></tr>
    <tr><td class="lbl">Reservasi</td><td class="sep">:</td><td>Rp500.000</td></tr>
    <tr><td class="lbl">Booking Fee</td><td class="sep">:</td><td>Rp2.000.000</td></tr>
    <tr><td class="lbl">DP</td><td class="sep">:</td><td>Rp <x-isian lebar="55mm" :nilai="$d['dp'] ?? null"/></td></tr>
    <tr><td class="lbl">Sisa Pembayaran</td><td class="sep">:</td><td>Rp <x-isian lebar="55mm" :nilai="$d['sisa_bayar'] ?? null"/></td></tr>
</table>

<p class="lamp-sub">JADWAL ANGSURAN</p>
<table class="grid">
    <thead>
        <tr><th style="width:18%">Angsuran</th><th style="width:32%">Tanggal</th><th style="width:32%">Nominal</th><th style="width:18%">Paraf</th></tr>
    </thead>
    <tbody>
        @for ($ke = 1; $ke <= 18; $ke++)
            @php $j = $d['jadwal'][$ke] ?? null; @endphp
            <tr>
                <td class="c" style="height:15pt">{{ $ke }}</td>
                <td class="c">{{ $j['tanggal'] ?? '' }}</td>
                <td class="r">{{ $j['nominal'] ?? '' }}</td>
                <td></td>
            </tr>
        @endfor
    </tbody>
</table>
