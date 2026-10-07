{{-- Form Lead / Calon Konsumen — kolom sama dengan form Input Lead di sistem. --}}
@include('formulir.halaman._toolkit-gaya')

<p class="tk-judul">FORM LEAD / CALON KONSUMEN</p>
<table class="data tk-form">
    @foreach (['Tanggal Lead', 'Nama', 'No. HP', 'Domisili', 'Agen'] as $label)
        <tr><td class="lbl">{{ $label }}</td><td><x-isian penuh/></td></tr>
    @endforeach
    <tr>
        <td class="lbl">Sumber</td>
        <td>@foreach (\App\Models\Lead::SUMBER as $l)<span class="kotak"></span>{{ $l }} &nbsp; @endforeach</td>
    </tr>
    <tr><td class="lbl">Kavling Diminati</td><td><x-isian penuh/></td></tr>
</table>

<p class="tk-sub" style="margin-top:16pt">Catatan</p>
@for ($i = 0; $i < 6; $i++)<div class="tk-baris-tulis"></div>@endfor
