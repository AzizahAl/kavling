<table class="ttd tk-ttd">
    <tr>
        <td><b>KONSUMEN/PEMESAN</b><div class="ruang"></div>(@include('formulir.halaman._nama-ttd', ['nama' => $d['ttd_pemesan'] ?? null]))</td>
        <td><b>MARKETING/SELES</b><div class="ruang"></div>(@include('formulir.halaman._nama-ttd', ['nama' => $d['ttd_marketing'] ?? null]))</td>
        <td><b>ADMIN/PENGELOLA</b><div class="ruang"></div>(@include('formulir.halaman._nama-ttd', ['nama' => $d['ttd_pengelola'] ?? null]))</td>
    </tr>
</table>
