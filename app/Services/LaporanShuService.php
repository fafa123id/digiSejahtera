<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Pinjaman;
use Carbon\CarbonImmutable;

class LaporanShuService
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
                'simpanans' => function ($query) use ($akhirTahun): void {
                    $query->whereDate('periode', '<=', $akhirTahun->toDateString())
                        ->orderBy('periode')
                        ->orderBy('id');
                },

                'pinjamans' => function ($query) use ($akhirTahun, $tahun): void {
                    $query->whereDate('tanggal_pinjaman', '<=', $akhirTahun->toDateString())
                        ->with([
                            'angsurans' => function ($query) use ($tahun): void {
                                $query->whereYear('periode', $tahun)
                                    ->orderBy('periode')
                                    ->orderBy('id');
                            },
                        ])
                        ->orderBy('tanggal_pinjaman')
                        ->orderBy('id');
                },
            ])
            ->orderByRaw('CAST(nomor_anggota AS INTEGER)')
            ->get()
            ->map(function (Anggota $anggota) use ($tahun): array {
                $simpananPokok = (float) $anggota->simpanans->sum('simpanan_pokok');
                $simpananWajib = (float) $anggota->simpanans->sum('simpanan_wajib');
                $simpananSukarela = (float) $anggota->simpanans->sum('simpanan_sukarela');
                $simpananHariRaya = (float) $anggota->simpanans->sum('simpanan_hari_raya');
                $simpananRekreasi = (float) $anggota->simpanans->sum('simpanan_rekreasi');

                $jumlahSimpanan = $simpananPokok
                    + $simpananWajib
                    + $simpananSukarela
                    + $simpananHariRaya
                    + $simpananRekreasi;

                $pinjamanReguler = $this->jumlahPinjaman($anggota, Pinjaman::JENIS_REGULER, $tahun);
                $pinjamanSebrak = $this->jumlahPinjaman($anggota, Pinjaman::JENIS_SEBRAK, $tahun);
                $jumlahPinjaman = $pinjamanReguler + $pinjamanSebrak;

                $totalJasaPinjaman = $this->jumlahJasaPinjaman($anggota);

                $shuSimpanan = $jumlahSimpanan * 0.50;
                $shuPinjaman = $totalJasaPinjaman * 0.50;
                $jumlahShu = $shuSimpanan + $shuPinjaman;

                return [
                    'id' => $anggota->id,
                    'nomor_anggota' => (int) $anggota->nomor_anggota,
                    'nama' => $anggota->nama,
                    'simpanan_pokok' => $simpananPokok,
                    'simpanan_wajib' => $simpananWajib,
                    'simpanan_sukarela' => $simpananSukarela,
                    'simpanan_hari_raya' => $simpananHariRaya,
                    'simpanan_rekreasi' => $simpananRekreasi,
                    'jumlah_simpanan' => $jumlahSimpanan,
                    'pinjaman_reguler' => $pinjamanReguler,
                    'pinjaman_sebrak' => $pinjamanSebrak,
                    'jumlah_pinjaman' => $jumlahPinjaman,
                    'total_jasa_pinjaman' => $totalJasaPinjaman,
                    'shu_simpanan' => $shuSimpanan,
                    'shu_pinjaman' => $shuPinjaman,
                    'jumlah_shu' => $jumlahShu,
                ];
            })
            ->values();

        return [
            'rows' => $rows,

            'totals' => [
                'simpanan_pokok' => (float) $rows->sum('simpanan_pokok'),
                'simpanan_wajib' => (float) $rows->sum('simpanan_wajib'),
                'simpanan_sukarela' => (float) $rows->sum('simpanan_sukarela'),
                'simpanan_hari_raya' => (float) $rows->sum('simpanan_hari_raya'),
                'simpanan_rekreasi' => (float) $rows->sum('simpanan_rekreasi'),
                'jumlah_simpanan' => (float) $rows->sum('jumlah_simpanan'),
                'pinjaman_reguler' => (float) $rows->sum('pinjaman_reguler'),
                'pinjaman_sebrak' => (float) $rows->sum('pinjaman_sebrak'),
                'jumlah_pinjaman' => (float) $rows->sum('jumlah_pinjaman'),
                'total_jasa_pinjaman' => (float) $rows->sum('total_jasa_pinjaman'),
                'shu_simpanan' => (float) $rows->sum('shu_simpanan'),
                'shu_pinjaman' => (float) $rows->sum('shu_pinjaman'),
                'jumlah_shu' => (float) $rows->sum('jumlah_shu'),
            ],
        ];
    }

    private function jumlahPinjaman(Anggota $anggota, string $jenisPinjaman, int $tahun): float
    {
        return (float) $anggota->pinjamans
            ->where('jenis_pinjaman', $jenisPinjaman)
            ->filter(function (Pinjaman $pinjaman) use ($tahun): bool {
                return CarbonImmutable::parse($pinjaman->tanggal_pinjaman)->year === $tahun;
            })
            ->sum('nominal_pinjaman');
    }

    private function jumlahJasaPinjaman(Anggota $anggota): float
    {
        return (float) $anggota->pinjamans->sum(function (Pinjaman $pinjaman): float {
            return (float) $pinjaman->angsurans->sum('jasa_pinjaman');
        });
    }
}