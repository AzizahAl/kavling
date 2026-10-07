{{-- Form Data Konsumen — kolom sama dengan form Tambah Konsumen di sistem. --}}
@include('formulir.halaman._toolkit-gaya')

<p class="tk-judul">FORM DATA KONSUMEN</p>
<table class="data tk-form">
    @foreach (['Nama Lengkap (KTP)', 'NIK (16 digit)', 'No. HP / WA', 'Email', 'Pekerjaan', 'Alamat (KTP)'] as $label)
        <tr><td class="lbl">{{ $label }}</td><td><x-isian penuh/></td></tr>
    @endforeach
    <tr><td class="lbl"></td><td><x-isian penuh/></td></tr>
</table>

<p class="tk-sub" style="margin-top:16pt">Catatan</p>
@for ($i = 0; $i < 4; $i++)<div class="tk-baris-tulis"></div>@endfor

<p class="tk-catatan" style="margin-top:12pt">Diisi admin: ID Konsumen <x-isian lebar="45mm"/></p>
