<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\RekapSimpanan;
use App\Models\ShuAnggota;

class ShuService
{
    public function hitungSHUAnggota(
        Anggota $anggota
    ): ShuAnggota {
        $dataRekapSimpanan = RekapSimpanan::query()->where('anggota_id', $anggota->id)->first();

        $totalSimpanan = (float) ($dataRekapSimpanan?->total_simpanan ?? 0);

        $totalJasaPinjaman = (float) Angsuran::query()->whereHas(
            'pinjaman',
            function ($query) use ($anggota): void {
                $query->where('anggota_id', $anggota->id);
            }
        )->sum('jasa_pinjaman');

        $persentaseSimpanan = 50;

        $persentaseJasaPinjaman = 50;

        $shuSimpanan = $totalSimpanan * ($persentaseSimpanan / 100);

        $shuPinjaman = $totalJasaPinjaman * ($persentaseJasaPinjaman / 100);

        $jumlahSHU = $shuSimpanan + $shuPinjaman;

        return ShuAnggota::updateOrCreate(
            [
                'anggota_id' => $anggota->id,

                'tahun' => now()->year,
            ],
            [
                'total_simpanan' => $totalSimpanan,

                'total_jasa_pinjaman' => $totalJasaPinjaman,

                'persentase_simpanan' => $persentaseSimpanan,

                'persentase_jasa_pinjaman' => $persentaseJasaPinjaman,

                'shu_simpanan' => $shuSimpanan,

                'shu_pinjaman' => $shuPinjaman,

                'total_shu' => $jumlahSHU,

                'calculated_at' =>now(),
            ]
        );
    }
}
