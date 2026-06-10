<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class LaporanTagihanBulananService
{
    public function __construct(private readonly KartuRekeningGridService $gridService)
    {
    }

    public function buatData(int $tahun): array
    {
        $anggota = collect($this->gridService->buatSemuaData($tahun));

        return collect(range(1, 12))->mapWithKeys(function (int $bulan) use ($anggota, $tahun): array {
            $awalPeriode = CarbonImmutable::create($tahun, $bulan, 1)->startOfMonth();
            $akhirPeriode = $awalPeriode->endOfMonth();
            $periode = $awalPeriode->format('Y-m');

            $data = $anggota
                ->filter(fn (array $item): bool => $this->aktifPadaPeriode($item, $awalPeriode, $akhirPeriode))
                ->sortBy(fn (array $item): int => (int) $item['nomor_anggota'])
                ->values()
                ->map(function (array $item) use ($periode): array {
                    $row = collect($item['rows'])->firstWhere('periode', $periode);

                    return [
                        'nomor_anggota' => (int) $item['nomor_anggota'],
                        'nama' => $item['nama'],
                        'jumlah_tagihan' => (float) ($row['jumlah_tagihan'] ?? 0),
                    ];
                })
                ->values();

            return [$bulan => $data];
        })->all();
    }

    private function aktifPadaPeriode(array $anggota, CarbonImmutable $awalPeriode, CarbonImmutable $akhirPeriode): bool
    {
        $tanggalMasuk = !empty($anggota['tanggal_masuk'])
            ? CarbonImmutable::parse($anggota['tanggal_masuk'])
            : null;

        $tanggalKeluar = !empty($anggota['tanggal_keluar'])
            ? CarbonImmutable::parse($anggota['tanggal_keluar'])
            : null;

        return (!$tanggalMasuk || $tanggalMasuk->lte($akhirPeriode))
            && (!$tanggalKeluar || $tanggalKeluar->gte($awalPeriode));
    }
}