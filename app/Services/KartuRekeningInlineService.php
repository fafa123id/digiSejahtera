<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Pinjaman;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KartuRekeningInlineService
{
    public function __construct(
        private readonly KartuRekeningTransactionService $transactionService,
        private readonly RekapSimpananService $rekapSimpananService,
        private readonly PinjamanCalculationService $pinjamanCalculationService,
        private readonly ShuService $shuService
    ) {}

    public function simpan(
        array $changes,
        int $userId
    ): void {
        DB::transaction(
            function () use (
                $changes,
                $userId
            ): void {
                $collection =
                    collect(
                        $changes
                    );

                $affectedAnggotaIds =
                    [];

                $affectedLoanAccounts =
                    [];

                $this->simpanAnggota(
                    changes: $collection,
                    affectedAnggotaIds: $affectedAnggotaIds,
                );

                $this->simpanSimpanan(
                    changes: $collection,

                    userId: $userId,

                    affectedAnggotaIds: $affectedAnggotaIds,
                );

                $this->simpanTransaksiPinjaman(
                    changes: $collection,

                    userId: $userId,

                    affectedAnggotaIds: $affectedAnggotaIds,

                    affectedLoanAccounts: $affectedLoanAccounts,
                );

                foreach (
                    $affectedLoanAccounts
                    as $account
                ) {
                    $this->pinjamanCalculationService->hitungSisaPinjaman(
                        anggotaId: $account['anggota_id'],
                        jenisPinjaman: $account['jenis'],
                        mulaiPeriode: $account['mulai_periode'],
                    );

                    $this->pinjamanCalculationService->hitungJasaPinjaman(
                        anggotaId: $account['anggota_id'],
                        jenisPinjaman: $account['jenis'],
                    );
                }

                foreach (
                    array_unique(
                        $affectedAnggotaIds
                    )
                    as $anggotaId
                ) {
                    $anggota = Anggota::query()
                        ->findOrFail(
                            $anggotaId
                        );

                    $this
                        ->rekapSimpananService
                        ->hitungTotalSimpanan(
                            $anggota
                        );

                    $this
                        ->shuService
                        ->hitungSHUAnggota(
                            $anggota
                        );
                }
            }
        );
    }

    private function simpanAnggota(Collection $changes, array &$affectedAnggotaIds): void
    {
        $changes
            ->where('section', 'anggota')
            ->each(function (array $change) use (&$affectedAnggotaIds): void {
                $anggota = Anggota::query()->findOrFail($change['anggota_id']);

                if ($change['field'] === 'nama') {
                    $anggota->update([
                        'nama' => trim((string) $change['value']),
                    ]);
                }

                if ($change['field'] === 'agama') {
                    $anggota->update([
                        'agama' => $change['value'],
                    ]);
                }

                $affectedAnggotaIds[] = $anggota->id;
            });
    }

    private function simpanSimpanan(
        Collection $changes,
        int $userId,
        array &$affectedAnggotaIds
    ): void {
        $changes
            ->where(
                'section',
                'simpanan'
            )
            ->groupBy(
                fn(array $change): string =>
                $change['anggota_id']
                    . '|'
                    . $change['periode']
            )
            ->each(
                function (
                    Collection $group
                ) use (
                    $userId,
                    &$affectedAnggotaIds
                ): void {
                    $first =
                        $group->first();

                    $anggota = Anggota::query()
                        ->findOrFail(
                            $first['anggota_id']
                        );

                    $periode =
                        $this->buatPeriode(
                            $first['periode']
                        );

                    $this
                        ->transactionService
                        ->submitSimpanan(
                            anggota: $anggota,

                            periode: $periode,

                            changes: $group,

                            userId: $userId,

                            hitungUlang: false,
                        );

                    $affectedAnggotaIds[] =
                        $anggota->id;
                }
            );
    }

    private function simpanTransaksiPinjaman(
        Collection $changes,
        int $userId,
        array &$affectedAnggotaIds,
        array &$affectedLoanAccounts
    ): void {
        $changes
            ->filter(
                fn(array $change): bool =>
                in_array(
                    $change['section'],
                    [
                        Pinjaman::JENIS_REGULER,
                        Pinjaman::JENIS_SEBRAK,
                    ],
                    true
                )
            )
            ->sortBy(
                fn(array $change): string =>
                $change['periode']
                    . '|'
                    . $this->urutanPerubahan(
                        $change
                    )
            )
            ->each(
                function (
                    array $change
                ) use (
                    $userId,
                    &$affectedAnggotaIds,
                    &$affectedLoanAccounts
                ): void {
                    $anggota = Anggota::query()
                        ->findOrFail(
                            $change['anggota_id']
                        );

                    $jenis =
                        $change['section'];

                    $periode =
                        $this->buatPeriode(
                            $change['periode']
                        );

                    $rawValue =
                        $change['value']
                        ?? null;

                    $berubah = match ($change['action']) {
                        'create_pinjaman',
                        'update_pinjaman' =>
                        $this
                            ->transactionService
                            ->submitPinjaman(
                                anggota: $anggota,

                                jenis: $jenis,

                                periode: $periode,

                                action: $change['action'],

                                rawValue: $rawValue,

                                pinjamanId: isset(
                                    $change['entry_id']
                                )
                                    ? (int) $change['entry_id']
                                    : null,

                                userId: $userId,

                                hitungUlang: false,
                            ),

                        'create_angsuran',
                        'update_angsuran' =>
                        $this
                            ->transactionService
                            ->submitAngsuran(
                                anggota: $anggota,

                                jenis: $jenis,

                                periode: $periode,

                                action: $change['action'],

                                rawValue: $rawValue,

                                angsuranId: isset(
                                    $change['entry_id']
                                )
                                    ? (int) $change['entry_id']
                                    : null,

                                userId: $userId,

                                hitungUlang: false,
                            ),

                        default =>
                        throw ValidationException
                            ::withMessages([
                                'pinjaman' =>
                                'Jenis perubahan transaksi tidak valid.',
                            ]),
                    };

                    if (!$berubah) {
                        return;
                    }


                    $affectedAnggotaIds[] = $anggota->id;

                    $accountKey = $anggota->id . '|' . $jenis;
                    $periodeSebelumnya = $affectedLoanAccounts[$accountKey]['mulai_periode'] ?? null;

                    $affectedLoanAccounts[$accountKey] = [
                        'anggota_id' => $anggota->id,
                        'jenis' => $jenis,
                        'mulai_periode' => $periodeSebelumnya && $periodeSebelumnya->lte($periode) ? $periodeSebelumnya : $periode,
                    ];
                }
            );
    }

    private function buatPeriode(
        string $periode
    ): CarbonImmutable {
        return CarbonImmutable
            ::createFromFormat(
                'Y-m',
                $periode
            )
            ->startOfMonth();
    }

    private function urutanPerubahan(
        array $change
    ): string {
        $action =
            $change['action']
            ?? null;

        $rawValue =
            $change['value']
            ?? null;

        $nominal =
            $this->angka(
                $rawValue
            );

        $isKosong =
            $this->nilaiKosong(
                $rawValue
            );

        $section =
            $change['section']
            ?? null;

        if (
            $action
            === 'update_angsuran'
            && (
                $isKosong
                || (
                    $section
                    === Pinjaman::JENIS_REGULER
                    && $nominal <= 0
                )
            )
        ) {
            return '0';
        }

        if (
            in_array(
                $action,
                [
                    'create_pinjaman',
                    'update_pinjaman',
                ],
                true
            )
            && !(
                $action
                === 'update_pinjaman'
                && (
                    $isKosong
                    || $nominal <= 0
                )
            )
        ) {
            return '1';
        }

        if (
            in_array(
                $action,
                [
                    'create_angsuran',
                    'update_angsuran',
                ],
                true
            )
        ) {
            return '2';
        }

        if (
            $action
            === 'update_pinjaman'
            && (
                $isKosong
                || $nominal <= 0
            )
        ) {
            return '3';
        }

        return '9';
    }

    private function angka(
        mixed $value
    ): float {
        if (
            $value === ''
            || $value === null
        ) {
            return 0;
        }

        return (float) $value;
    }

    private function nilaiKosong(
        mixed $value
    ): bool {
        return
            $value === ''
            || $value === null;
    }
}
