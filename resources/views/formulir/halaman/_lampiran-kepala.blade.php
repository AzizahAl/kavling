<div class="lamp-kepala">
    <p class="judul">{{ $judulLampiran }}</p>
    <p class="lamp">{{ $lampiran }}</p>
    <p>Perjanjian Pengikatan Jual Beli (PPJB)</p>
    <p>Nomor : @if ($isi && filled($d['nomor'] ?? null)){{ $d['nomor'] }}@else TR/PPJB/20__/<x-isian lebar="18mm"/>@endif</p>
</div>
