<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Pinjaman;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class LaporanJasaPinjamanService
{
    public function buatData(int $tahun): array
    {
        $awalTahun = CarbonImmutable::create($tahun, 1, 1)->startOfDay();
        $akhirTahun = CarbonImmutable::create($tahun, 12, 31)->endOfDay();

        $rows = Anggota::query()
            ->whereDate('tanggal_masuk', '<=', $akhirTahun->toDateString())
            ->where(function ($query) use ($awalTahun): void {
                $query->whereNull('tanggal_keluar')
                    ->orWhereDate('tanggal_keluar', '>=', $awalTahun->toDateString());
            })
            ->with([
                'pinjamans' => function ($query) use ($tahun): void {
                    $query->with([
                        'angsurans' => function ($query) use ($tahun): void {
                            $query->whereYear('periode', $tahun)
                                ->orderBy('periode')
                                ->orderBy('id');
                        },
                    ]);
                },
            ])
            ->orderByRaw('CAST(nomor_anggota AS INTEGER)')
            ->get()
            ->map(function (Anggota $anggota): array {
                $jasaReguler = $this->jumlahJasa($anggota, Pinjaman::JENIS_REGULER);
                $jasaSebrak = $this->jumlahJasa($anggota, Pinjaman::JENIS_SEBRAK);

                return [
                    'id' => $anggota->id,
                    'nomor_anggota' => (int) $anggota->nomor_anggota,
                    'nama' => $anggota->nama,
                    'jasa_reguler' => $jasaReguler,
                    'jasa_sebrak' => $jasaSebrak,
                    'jumlah_jasa' => $jasaReguler + $jasaSebrak,
                ];
            })
            ->values();

        return [
            'rows' => $rows,
            'totals' => [
                'jasa_reguler' => (float) $rows->sum('jasa_reguler'),
                'jasa_sebrak' => (float) $rows->sum('jasa_sebrak'),
                'jumlah_jasa' => (float) $rows->sum('jumlah_jasa'),
            ],
        ];
    }

    private function jumlahJasa(Anggota $anggota, string $jenisPinjaman): float
    {
        return (float) $anggota->pinjamans
            ->where('jenis_pinjaman', $jenisPinjaman)
            ->sum(function ($pinjaman): float {
                return (float) $pinjaman->angsurans->sum('jasa_pinjaman');
            });
    }
}