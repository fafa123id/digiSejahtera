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


        $tahun = now()->year;

        $persentasePengurus = 10.0;
        $persentaseCadangan = 10.0;

        $persentaseSimpanan = 50.0;
        $persentaseJasaPinjaman = 50.0;

        $biayaOperasional = 29_000_000.0;

        $dataRekapSimpanan = RekapSimpanan::query()
            ->where('anggota_id', $anggota->id)
            ->first();

        $totalSimpananAnggota = (float) (
            $dataRekapSimpanan?->total_simpanan ?? 0
        );

        $totalSimpananSeluruhAnggota = (float) RekapSimpanan::query()
            ->sum('total_simpanan');

        $totalJasaPinjamanAnggota = (float) Angsuran::query()
            ->whereYear('periode', $tahun)
            ->whereHas(
                'pinjaman',
                function ($query) use ($anggota): void {
                    $query->where(
                        'anggota_id',
                        $anggota->id
                    );
                }
            )
            ->sum('jasa_pinjaman');


        $totalJasaPinjamanSeluruhAnggota = (float) Angsuran::query()
            ->whereYear('periode', $tahun)
            ->sum('jasa_pinjaman');


        $shuSebelumAlokasi = max(
            0,
            $totalJasaPinjamanSeluruhAnggota - $biayaOperasional
        );

        $danaPengurus = $shuSebelumAlokasi
            * ($persentasePengurus / 100);

        $danaCadangan = $shuSebelumAlokasi
            * ($persentaseCadangan / 100);

        $danaUntukSeluruhAnggota = max(
            0,
            $shuSebelumAlokasi
                - $danaPengurus
                - $danaCadangan
        );


        $danaBagianSimpanan = $danaUntukSeluruhAnggota
            * ($persentaseSimpanan / 100);

        $danaBagianJasaPinjaman = $danaUntukSeluruhAnggota
            * ($persentaseJasaPinjaman / 100);


        $shuSimpanan = $totalSimpananSeluruhAnggota > 0
            ? (
                $totalSimpananAnggota
                / $totalSimpananSeluruhAnggota
            ) * $danaBagianSimpanan
            : 0;

        $shuPinjaman = $totalJasaPinjamanSeluruhAnggota > 0
            ? (
                $totalJasaPinjamanAnggota
                / $totalJasaPinjamanSeluruhAnggota
            ) * $danaBagianJasaPinjaman
            : 0;

        $shuSimpanan = round($shuSimpanan, 2);
        $shuPinjaman = round($shuPinjaman, 2);
        $jumlahSHU = round(
            $shuSimpanan + $shuPinjaman,
            2
        );



        return ShuAnggota::updateOrCreate(
            [
                'anggota_id' => $anggota->id,
                'tahun' => $tahun,
            ],
            [
                'total_simpanan' =>
                $totalSimpananAnggota,

                'total_jasa_pinjaman' =>
                $totalJasaPinjamanAnggota,

                'persentase_simpanan' =>
                $persentaseSimpanan,

                'persentase_jasa_pinjaman' =>
                $persentaseJasaPinjaman,

                'shu_simpanan' =>
                $shuSimpanan,

                'shu_pinjaman' =>
                $shuPinjaman,

                'total_shu' =>
                $jumlahSHU,

                'calculated_at' => now(),
            ]
        );
    }
}
