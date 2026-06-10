<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Pinjaman;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class KartuRekeningGridService
{
    public function buatSemuaData(
        int $tahun
    ): array {
        return Anggota::query()
            ->with([
                'simpanans' =>
                fn($query) =>
                $query
                    ->whereYear(
                        'periode',
                        $tahun
                    )
                    ->orderBy(
                        'periode'
                    ),

                'rekapSimpanan',

                'pinjamans' =>
                fn($query) =>
                $query
                    ->with([
                        'angsurans' =>
                        fn($query) =>
                        $query
                            ->orderBy(
                                'periode'
                            )
                            ->orderBy(
                                'id'
                            ),
                    ])
                    ->orderBy(
                        'tanggal_pinjaman'
                    )
                    ->orderBy(
                        'id'
                    ),
            ])
            ->orderBy(
                'nomor_anggota'
            )
            ->get()
            ->map(
                fn(Anggota $anggota): array =>
                $this->buatDataAnggota(
                    anggota: $anggota,
                    tahun: $tahun,
                )
            )
            ->values()
            ->all();
    }

    private function buatDataAnggota(
        Anggota $anggota,
        int $tahun
    ): array {
        $rows =
            $this->buatBarisKosong(
                $tahun
            );

        $this->isiSimpanan(
            anggota: $anggota,
            rows: $rows,
        );

        $this->isiJenisPinjaman(
            anggota: $anggota,
            tahun: $tahun,
            jenis: Pinjaman::JENIS_REGULER,
            rows: $rows,
        );

        $this->isiJenisPinjaman(
            anggota: $anggota,
            tahun: $tahun,
            jenis: Pinjaman::JENIS_SEBRAK,
            rows: $rows,
        );

        $rows =
            $rows->map(
                function (
                    array $row
                ): array {
                    $row['simpanan']['jumlah_simpanan'] =
                        array_sum([
                            $row['simpanan']['simpanan_pokok'],

                            $row['simpanan']['simpanan_wajib'],

                            $row['simpanan']['simpanan_sukarela'],

                            $row['simpanan']['simpanan_hari_raya'],

                            $row['simpanan']['simpanan_rekreasi'],
                        ]);

                    $row['jumlah_tagihan'] =
                        $row['simpanan']['jumlah_simpanan']
                        + $row['reguler']['jumlah_angsuran']
                        + $row['reguler']['jasa']
                        + $row['sebrak']['jumlah_angsuran']
                        + $row['sebrak']['jasa'];

                    return $row;
                }
            );

        $rekap =
            $anggota
            ->rekapSimpanan;

        return [
            'id' =>
            $anggota->id,

            'nomor_anggota' =>
            $anggota
                ->nomor_anggota,

            'nama' =>
            $anggota
                ->nama,
            'agama' => $anggota->agama,

            'tanggal_masuk' =>
            $anggota
                ->tanggal_masuk
                ?->format('Y-m-d'),

            'tanggal_keluar' =>
            $anggota
                ->tanggal_keluar
                ?->format('Y-m-d'),

            'status' =>
            $anggota
                ->status,

            'rows' =>
            $rows
                ->values()
                ->all(),

            'totals' => [
                'simpanan_pokok' =>
                (float) (
                    $rekap
                    ?->total_simpanan_pokok
                    ?? 0
                ),

                'simpanan_wajib' =>
                (float) (
                    $rekap
                    ?->total_simpanan_wajib
                    ?? 0
                ),

                'simpanan_sukarela' =>
                (float) (
                    $rekap
                    ?->total_simpanan_sukarela
                    ?? 0
                ),

                'simpanan_hari_raya' =>
                (float) (
                    $rekap
                    ?->total_simpanan_hari_raya
                    ?? 0
                ),

                'simpanan_rekreasi' =>
                (float) (
                    $rekap
                    ?->total_simpanan_rekreasi
                    ?? 0
                ),

                'total_simpanan' =>
                (float) (
                    $rekap
                    ?->total_simpanan
                    ?? 0
                ),

                'jasa_reguler' =>
                $this->jumlahJasa(
                    anggota: $anggota,
                    tahun: $tahun,
                    jenis: Pinjaman::JENIS_REGULER,
                ),

                'jasa_sebrak' =>
                $this->jumlahJasa(
                    anggota: $anggota,
                    tahun: $tahun,
                    jenis: Pinjaman::JENIS_SEBRAK,
                ),
            ],
        ];
    }

    private function isiSimpanan(
        Anggota $anggota,
        Collection $rows
    ): void {
        foreach (
            $anggota->simpanans
            as $simpanan
        ) {
            $key =
                $simpanan
                ->periode
                ->format('Y-m');

            $row =
                $rows->get(
                    $key
                );

            if (!$row) {
                continue;
            }

            $row['simpanan'] = [
                'simpanan_pokok' =>
                (float) $simpanan
                    ->simpanan_pokok,

                'simpanan_wajib' =>
                (float) $simpanan
                    ->simpanan_wajib,

                'simpanan_sukarela' =>
                (float) $simpanan
                    ->simpanan_sukarela,

                'simpanan_hari_raya' =>
                (float) $simpanan
                    ->simpanan_hari_raya,

                'simpanan_rekreasi' =>
                (float) $simpanan
                    ->simpanan_rekreasi,

                'jumlah_simpanan' =>
                (float) $simpanan
                    ->jumlah_simpanan,
            ];

            $rows->put(
                $key,
                $row
            );
        }
    }

    private function isiJenisPinjaman(
        Anggota $anggota,
        int $tahun,
        string $jenis,
        Collection $rows
    ): void {
        $pinjamans =
            $anggota
            ->pinjamans
            ->where(
                'jenis_pinjaman',
                $jenis
            )
            ->sortBy([
                [
                    'tanggal_pinjaman',
                    'asc',
                ],
                [
                    'id',
                    'asc',
                ],
            ])
            ->values();

        $angsurans =
            $pinjamans
            ->flatMap(
                fn(Pinjaman $pinjaman) =>
                $pinjaman
                    ->angsurans
            )
            ->sortBy([
                [
                    'periode',
                    'asc',
                ],
                [
                    'id',
                    'asc',
                ],
            ])
            ->values();

        $awalTahun =
            CarbonImmutable::create(
                year: $tahun,
                month: 1,
                day: 1,
            );

        $saldo =
            (float) $pinjamans
                ->filter(
                    fn(Pinjaman $pinjaman): bool =>
                    $pinjaman
                        ->tanggal_pinjaman
                        ->lt(
                            $awalTahun
                        )
                )
                ->sum(
                    'nominal_pinjaman'
                )
            - (float) $angsurans
                ->filter(
                    fn($angsuran): bool =>
                    $angsuran
                        ->periode
                        ->lt(
                            $awalTahun
                        )
                )
                ->sum(
                    'nominal_angsuran'
                );

        foreach (
            $rows
            as $key => $row
        ) {
            $periode =
                CarbonImmutable
                ::createFromFormat(
                    'Y-m',
                    $key
                )
                ->startOfMonth();

            $saldoAwal =
                $saldo;

            $pinjamanPeriode =
                $pinjamans
                ->filter(
                    fn(Pinjaman $pinjaman): bool =>
                    $pinjaman
                        ->tanggal_pinjaman
                        ->format('Y-m')
                        === $key
                )
                ->values();

            $angsuran =
                $angsurans
                ->first(
                    fn($angsuran): bool =>
                    $angsuran
                        ->periode
                        ->format('Y-m')
                        === $key
                );

            $entries =
                collect();

            if ($angsuran) {
                $entries->push([
                    'client_key' =>
                    'angsuran-'
                        . $angsuran->id,

                    'entry_id' =>
                    $angsuran->id,

                    'entry_type' =>
                    'angsuran',

                    'loan_label' =>
                    'angsuran',

                    'action' =>
                    'update_angsuran',

                    'jumlah' =>
                    (float) $angsuran
                        ->nominal_angsuran,
                ]);
            }

            $saldoUntukLabel =
                $saldoAwal;

            foreach (
                $pinjamanPeriode
                as $pinjaman
            ) {
                $isTambahan =
                    $saldoUntukLabel > 0;

                $entries->push([
                    'client_key' =>
                    'pinjaman-'
                        . $pinjaman->id,

                    'entry_id' =>
                    $pinjaman->id,

                    'entry_type' =>
                    'pinjaman',

                    'loan_label' =>
                    $isTambahan
                        ? 'pinjaman_tambahan'
                        : 'pinjaman',

                    'action' =>
                    'update_pinjaman',

                    'jumlah' =>
                    (float) $pinjaman
                        ->nominal_pinjaman,
                ]);

                $saldoUntukLabel +=
                    (float) $pinjaman
                        ->nominal_pinjaman;
            }

            $jumlahAngsuran =
                (float) (
                    $angsuran
                    ?->nominal_angsuran
                    ?? 0
                );

            $jasa =
                (float) (
                    $angsuran
                    ?->jasa_pinjaman
                    ?? 0
                );

            $saldo =
                $saldoAwal
                - $jumlahAngsuran
                + (float) $pinjamanPeriode
                    ->sum(
                        'nominal_pinjaman'
                    );

            $row[$jenis] = [
                'entries' =>
                $entries
                    ->values()
                    ->all(),

                'ke' =>
                $angsuran
                    ?->angsuran_ke,

                'sisa' =>
                $entries->isNotEmpty()
                    ? $saldo
                    : null,

                'jasa' =>
                $jasa,

                'jumlah_angsuran' =>
                $jumlahAngsuran,

                'can_add_angsuran' =>
                $saldoAwal > 0
                    && !$angsuran,

                'has_angsuran' =>
                $angsuran
                    !== null,
            ];

            $rows->put(
                $key,
                $row
            );
        }
    }

    private function buatBarisKosong(
        int $tahun
    ): Collection {
        return collect(
            range(
                1,
                12
            )
        )->mapWithKeys(
            function (
                int $bulan
            ) use (
                $tahun
            ): array {
                $periode =
                    CarbonImmutable::create(
                        year: $tahun,
                        month: $bulan,
                        day: 1,
                    );

                return [
                    $periode
                        ->format('Y-m') => [
                        'periode' =>
                        $periode
                            ->format('Y-m'),

                        'bulan' =>
                        strtoupper(
                            $periode
                                ->translatedFormat(
                                    'M'
                                )
                        ),

                        'simpanan' =>
                        $this
                            ->simpananKosong(),

                        'reguler' =>
                        $this
                            ->pinjamanKosong(),

                        'sebrak' =>
                        $this
                            ->pinjamanKosong(),

                        'jumlah_tagihan' =>
                        0,
                    ],
                ];
            }
        );
    }

    private function simpananKosong(): array
    {
        return [
            'simpanan_pokok' =>
            0,

            'simpanan_wajib' =>
            0,

            'simpanan_sukarela' =>
            0,

            'simpanan_hari_raya' =>
            0,

            'simpanan_rekreasi' =>
            0,

            'jumlah_simpanan' =>
            0,
        ];
    }

    private function pinjamanKosong(): array
    {
        return [
            'entries' =>
            [],

            'ke' =>
            null,

            'sisa' =>
            null,

            'jasa' =>
            0,

            'jumlah_angsuran' =>
            0,

            'can_add_angsuran' =>
            false,

            'has_angsuran' =>
            false,
        ];
    }

    private function jumlahJasa(
        Anggota $anggota,
        int $tahun,
        string $jenis
    ): float {
        return (float) $anggota
            ->pinjamans
            ->where(
                'jenis_pinjaman',
                $jenis
            )
            ->flatMap(
                fn(Pinjaman $pinjaman) =>
                $pinjaman
                    ->angsurans
            )
            ->filter(
                fn($angsuran): bool =>
                (int) $angsuran
                    ->periode
                    ->format('Y')
                    === $tahun
            )
            ->sum(
                'jasa_pinjaman'
            );
    }
}
