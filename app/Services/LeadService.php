<?php

namespace App\Services;

use App\Models\Agen;
use App\Models\Lead;
use App\Models\TransaksiPenjualan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Alur lead marketing: Lead → Prospek → Closing (tidak boleh melompat).
 * Closing selalu terhubung ke satu transaksi penjualan.
 */
class LeadService
{
    public function buat(array $data, ?int $userId): Lead
    {
        return DB::transaction(function () use ($data, $userId) {
            $lead = Lead::create(collect($data)->only(['nama', 'no_hp', 'domisili', 'sumber', 'tanggal_lead', 'agen_id', 'kavling_minat_id', 'catatan'])->all() + [
                'kode'         => Penomoran::berikut('leads', 'kode', 'LD', Carbon::parse($data['tanggal_lead'])->year),
                'tahap'        => 'lead',
                'diinput_oleh' => $userId,
            ]);
            $this->catatRiwayat($lead, null, 'lead', $data['tanggal_lead'], null, $userId);

            return $lead;
        });
    }

    public function ubah(Lead $lead, array $data): Lead
    {
        if ($lead->tanggal_prospek && Carbon::parse($data['tanggal_lead'])->gt($lead->tanggal_prospek)) {
            $this->gagal('tanggal_lead', 'Tanggal lead tidak boleh setelah tanggal prospek (' . tanggal($lead->tanggal_prospek) . ').');
        }
        if ($lead->tahap === 'closing' && (int) $data['agen_id'] !== $lead->agen_id) {
            $this->gagal('agen_id', 'Agen lead yang sudah closing tidak bisa diganti. Kembalikan dulu ke tahap prospek.');
        }
        $lead->update(collect($data)->only(['nama', 'no_hp', 'domisili', 'sumber', 'tanggal_lead', 'agen_id', 'kavling_minat_id', 'catatan'])->all());

        return $lead;
    }

    public function keProspek(Lead $lead, string $tgl, ?string $catatan, ?int $userId): Lead
    {
        return DB::transaction(function () use ($lead, $tgl, $catatan, $userId) {
            $lead = Lead::lockForUpdate()->findOrFail($lead->id);
            if ($lead->tahap !== 'lead') {
                $this->gagal('tahap', 'Hanya data berstatus Lead yang bisa dijadikan Prospek.');
            }
            if (Carbon::parse($tgl)->lt($lead->tanggal_lead)) {
                $this->gagal('tanggal', 'Tanggal prospek tidak boleh sebelum tanggal lead (' . tanggal($lead->tanggal_lead) . ').');
            }
            $lead->update(['tahap' => 'prospek', 'tanggal_prospek' => $tgl]);
            $this->catatRiwayat($lead, 'lead', 'prospek', $tgl, $catatan, $userId);

            return $lead;
        });
    }

    public function keClosing(Lead $lead, int $transaksiId, string $tgl, ?string $catatan, ?int $userId): Lead
    {
        return DB::transaction(function () use ($lead, $transaksiId, $tgl, $catatan, $userId) {
            $lead = Lead::lockForUpdate()->findOrFail($lead->id);
            $t = TransaksiPenjualan::lockForUpdate()->findOrFail($transaksiId);

            if ($lead->tahap !== 'prospek') {
                $this->gagal('tahap', 'Hanya Prospek yang bisa menjadi Closing.');
            }
            if (Carbon::parse($tgl)->lt($lead->tanggal_prospek)) {
                $this->gagal('tanggal', 'Tanggal closing tidak boleh sebelum tanggal prospek (' . tanggal($lead->tanggal_prospek) . ').');
            }
            if ($t->isBatal()) {
                $this->gagal('transaksi_id', "Transaksi {$t->kode_transaksi} sudah dibatalkan.");
            }
            if (Lead::where('transaksi_id', $t->id)->exists()) {
                $this->gagal('transaksi_id', "Transaksi {$t->kode_transaksi} sudah menjadi closing lead lain.");
            }
            if ($t->agen_id && $t->agen_id !== $lead->agen_id) {
                $this->gagal('transaksi_id', "Transaksi {$t->kode_transaksi} tercatat atas agen lain. Samakan agennya dulu.");
            }

            // Transaksi tanpa agen otomatis dicatat atas agen penanggung jawab lead
            if (! $t->agen_id) {
                $t->update(['agen_id' => $lead->agen_id]);
            }

            $lead->update(['tahap' => 'closing', 'tanggal_closing' => $tgl, 'transaksi_id' => $t->id]);
            $this->catatRiwayat($lead, 'prospek', 'closing', $tgl, $catatan, $userId);

            return $lead;
        });
    }

    /** Koreksi salah input: mundur satu tahap. */
    public function mundur(Lead $lead, ?int $userId): Lead
    {
        return DB::transaction(function () use ($lead, $userId) {
            $lead = Lead::lockForUpdate()->findOrFail($lead->id);
            $dari = $lead->tahap;
            $ke = match ($dari) {
                'closing' => 'prospek',
                'prospek' => 'lead',
                default   => $this->gagal('tahap', 'Lead sudah di tahap awal.'),
            };
            $lead->update($ke === 'prospek'
                ? ['tahap' => 'prospek', 'tanggal_closing' => null, 'transaksi_id' => null]
                : ['tahap' => 'lead', 'tanggal_prospek' => null]);
            $this->catatRiwayat($lead, $dari, $ke, today()->toDateString(), 'Koreksi: kembali ke tahap sebelumnya', $userId);

            return $lead;
        });
    }

    /**
     * Rekap jumlah Lead / Prospek / Closing per agen dalam rentang tanggal.
     * Setiap tahap dihitung pada tanggal tahap itu terjadi.
     * @return Collection<int, array{agen: Agen, lead: int, prospek: int, closing: int}>
     */
    public function rekapPerAgen(Carbon $dari, Carbon $sampai, ?int $agenId = null): Collection
    {
        $hitung = fn (string $kolom) => Lead::whereBetween($kolom, [$dari->toDateString(), $sampai->toDateString()])
            ->when($agenId, fn ($q) => $q->where('agen_id', $agenId))
            ->selectRaw('agen_id, COUNT(*) n')->groupBy('agen_id')->pluck('n', 'agen_id');

        [$l, $p, $c] = [$hitung('tanggal_lead'), $hitung('tanggal_prospek'), $hitung('tanggal_closing')];

        return Agen::query()->when($agenId, fn ($q) => $q->whereKey($agenId))
            ->where(fn ($q) => $q->where('aktif', true)->orWhereIn('id', $l->keys()->merge($p->keys())->merge($c->keys())))
            ->orderBy('nama_agen')->get()
            ->map(fn (Agen $a) => [
                'agen'    => $a,
                'lead'    => (int) ($l[$a->id] ?? 0),
                'prospek' => (int) ($p[$a->id] ?? 0),
                'closing' => (int) ($c[$a->id] ?? 0),
            ]);
    }

    /** Deret harian untuk grafik: ['2026-10-01' => [lead, prospek, closing], ...] */
    public function deretHarian(Carbon $dari, Carbon $sampai, ?int $agenId = null): array
    {
        $hitung = fn (string $kolom) => Lead::whereBetween($kolom, [$dari->toDateString(), $sampai->toDateString()])
            ->when($agenId, fn ($q) => $q->where('agen_id', $agenId))
            ->selectRaw("DATE({$kolom}) d, COUNT(*) n")->groupBy('d')->pluck('n', 'd');

        [$l, $p, $c] = [$hitung('tanggal_lead'), $hitung('tanggal_prospek'), $hitung('tanggal_closing')];

        $hasil = [];
        for ($d = $dari->copy(); $d->lte($sampai); $d->addDay()) {
            $k = $d->toDateString();
            $hasil[$k] = [(int) ($l[$k] ?? 0), (int) ($p[$k] ?? 0), (int) ($c[$k] ?? 0)];
        }

        return $hasil;
    }

    /** Jumlah total per tahap sepanjang waktu, per agen: [agen_id => [lead, prospek, closing]] (lead = semua data masuk). */
    public function totalPerAgen(): Collection
    {
        return Lead::selectRaw("agen_id, COUNT(*) lead, SUM(tanggal_prospek IS NOT NULL) prospek, SUM(tahap = 'closing') closing")
            ->groupBy('agen_id')->get()->keyBy('agen_id');
    }

    private function catatRiwayat(Lead $lead, ?string $dari, string $ke, $tgl, ?string $catatan, ?int $userId): void
    {
        $lead->riwayats()->create(['dari' => $dari, 'ke' => $ke, 'tanggal' => $tgl, 'catatan' => $catatan, 'user_id' => $userId]);
    }

    private function gagal(string $field, string $pesan): never
    {
        throw ValidationException::withMessages([$field => $pesan]);
    }
}
