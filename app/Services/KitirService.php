<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class KitirService
{
    public function buatSemuaData(
        int $tahun,
        int $bulan
    ): array {
        $awalPeriode =
            CarbonImmutable::create(
                year: $tahun,
                month: $bulan,
                day: 1,
            )
            ->startOfMonth();

        $akhirPeriode =
            $awalPeriode
            ->endOfMonth();

        return Anggota::query()
            ->where(
                function (
                    $query
                ) use (
                    $akhirPeriode
                ): void {
                    $query
                        ->whereNull(
                            'tanggal_masuk'
                        )
                        ->orWhereDate(
                            'tanggal_masuk',
                            '<=',
                            $akhirPeriode
                                ->toDateString()
                        );
                }
            )
            ->where(
                function (
                    $query
                ) use (
                    $awalPeriode
                ): void {
                    $query
                        ->whereNull(
                            'tanggal_keluar'
                        )
                        ->orWhereDate(
                            'tanggal_keluar',
                            '>=',
                            $awalPeriode
                                ->toDateString()
                        );
                }
            )
            ->with([
                'simpanans' =>
                function (
                    $query
                ) use (
                    $awalPeriode,
                    $akhirPeriode
                ): void {
                    $query
                        ->whereBetween(
                            'periode',
                            [
                                $awalPeriode
                                    ->toDateString(),

                                $akhirPeriode
                                    ->toDateString(),
                            ]
                        );
                },

                'pinjamans' =>
                function (
                    $query
                ) use (
                    $akhirPeriode
                ): void {
                    $query
                        ->whereDate(
                            'tanggal_pinjaman',
                            '<=',
                            $akhirPeriode
                                ->toDateString()
                        )
                        ->with([
                            'angsurans' =>
                            function (
                                $query
                            ) use (
                                $akhirPeriode
                            ): void {
                                $query
                                    ->whereDate(
                                        'periode',
                                        '<=',
                                        $akhirPeriode
                                            ->toDateString()
                                    )
                                    ->orderBy(
                                        'periode'
                                    )
                                    ->orderBy(
                                        'id'
                                    );
                            },
                        ])
                        ->orderBy(
                            'tanggal_pinjaman'
                        )
                        ->orderBy(
                            'id'
                        );
                },
            ])
            ->orderByRaw(
                'CAST(nomor_anggota AS INTEGER)'
            )
            ->get()
            ->map(
                fn(
                    Anggota $anggota
                ): array =>
                $this
                    ->buatDataAnggota(
                        anggota: $anggota,
                        awalPeriode: $awalPeriode,
                        akhirPeriode: $akhirPeriode,
                    )
            )
            ->values()
            ->all();
    }

    private function buatDataAnggota(
        Anggota $anggota,
        CarbonImmutable $awalPeriode,
        CarbonImmutable $akhirPeriode
    ): array {
        $simpanan =
            $anggota
            ->simpanans
            ->first();

        $pinjamanReguler =
            $anggota
            ->pinjamans
            ->where(
                'jenis_pinjaman',
                Pinjaman::JENIS_REGULER
            )
            ->values();

        $pinjamanSebrak =
            $anggota
            ->pinjamans
            ->where(
                'jenis_pinjaman',
                Pinjaman::JENIS_SEBRAK
            )
            ->values();

        $reguler =
            $this
            ->buatDataPinjaman(
                pinjamans: $pinjamanReguler,

                awalPeriode: $awalPeriode,

                akhirPeriode: $akhirPeriode,
            );

        $sebrak =
            $this
            ->buatDataPinjaman(
                pinjamans: $pinjamanSebrak,

                awalPeriode: $awalPeriode,

                akhirPeriode: $akhirPeriode,
            );

        $simpananWajib =
            (float) (
                $simpanan
                ?->simpanan_wajib
                ?? 0
            );

        $simpananSukarela =
            (float) (
                $simpanan
                ?->simpanan_sukarela
                ?? 0
            );

        $simpananHariRaya =
            (float) (
                $simpanan
                ?->simpanan_hari_raya
                ?? 0
            );

        $simpananRekreasi =
            (float) (
                $simpanan
                ?->simpanan_rekreasi
                ?? 0
            );

        $jumlah =
            $simpananWajib
            + $simpananSukarela
            + $simpananHariRaya
            + $simpananRekreasi
            + (
                $reguler['nominal_angsuran']
                ?? 0
            )
            + (
                $reguler['jasa_pinjaman']
                ?? 0
            )
            + (
                $sebrak['nominal_angsuran']
                ?? 0
            )
            + (
                $sebrak['jasa_pinjaman']
                ?? 0
            );

        return [
            'id' =>
            $anggota->id,

            'nomor_anggota' =>
            (int) $anggota
                ->nomor_anggota,

            'nama' =>
            $anggota
                ->nama,

            'status' =>
            $anggota
                ->status,

            'periode' =>
            $awalPeriode
                ->format(
                    'Y-m'
                ),

            'simpanan_wajib' =>
            $simpananWajib,

            'simpanan_sukarela' =>
            $simpananSukarela,

            'simpanan_hari_raya' =>
            $simpananHariRaya,

            'simpanan_rekreasi' =>
            $simpananRekreasi,

            'reguler' =>
            $reguler,

            'sebrak' =>
            $sebrak,

            'jumlah' =>
            $jumlah,

            'sisa_pinjaman' => (
                $reguler['sisa_pinjaman']
                ?? 0
            )
                + (
                    $sebrak['sisa_pinjaman']
                    ?? 0
                ),
        ];
    }

    private function buatDataPinjaman(
        Collection $pinjamans,
        CarbonImmutable $awalPeriode,
        CarbonImmutable $akhirPeriode
    ): array {
        $angsurans =
            $pinjamans
            ->flatMap(
                fn(
                    Pinjaman $pinjaman
                ): Collection =>
                $pinjaman
                    ->angsurans
            )
            ->values();

        $angsuranPeriode =
            $angsurans
            ->first(
                fn(
                    Angsuran $angsuran
                ): bool =>
                $angsuran
                    ->periode
                    ->betweenIncluded(
                        $awalPeriode,
                        $akhirPeriode
                    )
            );

        $totalPinjaman =
            (float) $pinjamans
                ->filter(
                    fn(
                        Pinjaman $pinjaman
                    ): bool =>
                    $pinjaman
                        ->tanggal_pinjaman
                        ->lte(
                            $akhirPeriode
                        )
                )
                ->sum(
                    'nominal_pinjaman'
                );

        $totalAngsuran =
            (float) $angsurans
                ->filter(
                    fn(
                        Angsuran $angsuran
                    ): bool =>
                    $angsuran
                        ->periode
                        ->lte(
                            $akhirPeriode
                        )
                )
                ->sum(
                    'nominal_angsuran'
                );

        return [
            'angsuran_ke' =>
            $angsuranPeriode
                ?->angsuran_ke,

            'nominal_angsuran' =>
            $angsuranPeriode
                ? (float) $angsuranPeriode
                    ->nominal_angsuran
                : null,

            'jasa_pinjaman' =>
            $angsuranPeriode
                ? (float) $angsuranPeriode
                    ->jasa_pinjaman
                : null,

            'sisa_pinjaman' =>
            $totalPinjaman
                - $totalAngsuran,
        ];
    }
}
