<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Pinjaman;
use App\Models\RekapSimpanan;
use App\Models\ShuAnggota;

class ShuService
{
    public function hitungSHUAnggota(
        Anggota $anggota
    ): ShuAnggota {
        $dataRekapSimpanan = RekapSimpanan::query()
            ->where(
                'anggota_id',
                $anggota->id
            )
            ->first();

        $totalSimpanan =
            (float) (
                $dataRekapSimpanan
                    ?->total_simpanan
                ?? 0
            );

        $totalPinjaman =
            (float) Pinjaman::query()
                ->where(
                    'anggota_id',
                    $anggota->id
                )
                ->sum(
                    'nominal_pinjaman'
                );

        $persentaseSimpanan =
            50;

        $persentasePinjaman =
            50;

        $shuSimpanan =
            $totalSimpanan
            * (
                $persentaseSimpanan
                / 100
            );

        $shuPinjaman =
            $totalPinjaman
            * (
                $persentasePinjaman
                / 100
            );

        $jumlahSHU =
            $shuSimpanan
            + $shuPinjaman;

        return ShuAnggota::updateOrCreate(
            [
                'anggota_id' =>
                    $anggota->id,

                'tahun' =>
                    now()->year,
            ],
            [
                'total_simpanan' =>
                    $totalSimpanan,

                'total_pinjaman' =>
                    $totalPinjaman,

                'persentase_simpanan' =>
                    $persentaseSimpanan,

                'persentase_pinjaman' =>
                    $persentasePinjaman,

                'shu_simpanan' =>
                    $shuSimpanan,

                'shu_pinjaman' =>
                    $shuPinjaman,

                'total_shu' =>
                    $jumlahSHU,

                'calculated_at' =>
                    now(),
            ]
        );
    }
}