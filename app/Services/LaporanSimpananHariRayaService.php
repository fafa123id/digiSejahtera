<?php

namespace App\Services;

use App\Models\Anggota;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class LaporanSimpananHariRayaService
{

    public function buatData(int $tahun, int $bulan): array
    {
        $awalTahun = CarbonImmutable::create($tahun, 1, 1)->startOfDay();
        $tanggalIdulFitri = CarbonImmutable::create($tahun, $bulan, 1)->startOfMonth();
        $akhirIslam = $tanggalIdulFitri->endOfMonth();
        $akhirNonIslam = CarbonImmutable::create($tahun, 12, 31)->endOfDay();

        return [
            'tanggal_idul_fitri' => $tanggalIdulFitri,
            'islam' => $this->ambilAnggota(Anggota::AGAMA_ISLAM, $awalTahun, $akhirIslam),
            'nonislam' => $this->ambilAnggota(Anggota::AGAMA_NONISLAM, $awalTahun, $akhirNonIslam),
        ];
    }

    private function ambilAnggota(string $agama, CarbonImmutable $awalPeriode, CarbonImmutable $akhirPeriode): Collection
    {
        return Anggota::query()
            ->where('agama', $agama)
            ->whereDate('tanggal_masuk', '<=', $akhirPeriode->toDateString())
            ->where(function ($query) use ($awalPeriode): void {
                $query->whereNull('tanggal_keluar')
                    ->orWhereDate('tanggal_keluar', '>=', $awalPeriode->toDateString());
            })
            ->withSum([
                'simpanans as total_simpanan_hari_raya' => function ($query) use ($awalPeriode, $akhirPeriode): void {
                    $query->whereBetween('periode', [
                        $awalPeriode->toDateString(),
                        $akhirPeriode->toDateString(),
                    ]);
                },
            ], 'simpanan_hari_raya')
            ->orderByRaw('CAST(nomor_anggota AS INTEGER)')
            ->get()
            ->map(fn (Anggota $anggota): array => [
                'id' => $anggota->id,
                'nomor_anggota' => (int) $anggota->nomor_anggota,
                'nama' => $anggota->nama,
                'jumlah' => (float) ($anggota->total_simpanan_hari_raya ?? 0),
            ])
            ->values();
    }
}