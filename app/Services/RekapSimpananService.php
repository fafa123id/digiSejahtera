<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\RekapSimpanan;
use App\Models\Simpanan;

class RekapSimpananService
{
    public function hitungTotalSimpanan(Anggota $anggota): RekapSimpanan
    {
        $dataSeluruhSimpanan = Simpanan::query()
            ->where('anggota_id', $anggota->id);

        $totalSimpananPokok = (float) (clone $dataSeluruhSimpanan)->sum('simpanan_pokok');
        $totalSimpananWajib = (float) (clone $dataSeluruhSimpanan)->sum('simpanan_wajib');
        $totalSimpananSukarela = (float) (clone $dataSeluruhSimpanan)->sum('simpanan_sukarela');
        $totalSimpananHariRaya = (float) (clone $dataSeluruhSimpanan)->sum('simpanan_hari_raya');
        $totalSimpananRekreasi = (float) (clone $dataSeluruhSimpanan)->sum('simpanan_rekreasi');

        $jumlahSimpanan = $totalSimpananPokok
            + $totalSimpananWajib
            + $totalSimpananSukarela
            + $totalSimpananHariRaya
            + $totalSimpananRekreasi;

        return RekapSimpanan::updateOrCreate(
            ['anggota_id' => $anggota->id],
            [
                'total_simpanan_pokok' => $totalSimpananPokok,
                'total_simpanan_wajib' => $totalSimpananWajib,
                'total_simpanan_sukarela' => $totalSimpananSukarela,
                'total_simpanan_hari_raya' => $totalSimpananHariRaya,
                'total_simpanan_rekreasi' => $totalSimpananRekreasi,
                'total_simpanan' => $jumlahSimpanan,
            ]
        );
    }
}
