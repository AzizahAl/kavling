{{-- Daftar pengeluaran yang membentuk realisasi item RAB (lipat/buka). Butuh $r dan state Alpine "rinci". --}}
@if ($r->kasKeluar->isNotEmpty())
    <button type="button" class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-brand-700 hover:underline" x-on:click="rinci = !rinci" :aria-expanded="rinci">
        <x-icon name="chevron-down" class="size-3.5 transition-transform" x-bind:class="rinci && 'rotate-180'"/>
        <span x-text="rinci ? 'Sembunyikan' : '{{ $r->kasKeluar->count() }} pengeluaran'"></span>
    </button>
    <ul x-show="rinci" x-collapse class="mt-1 space-y-0.5 text-xs text-slate-500">
        @foreach ($r->kasKeluar as $k)<li>{{ tanggal($k->tanggal) }} · {{ $k->kode }} · {{ rupiah($k->nominal) }}</li>@endforeach
    </ul>
@endif
