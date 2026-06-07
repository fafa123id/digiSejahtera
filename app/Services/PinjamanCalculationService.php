<?php

namespace App\Services;

use App\Models\Angsuran;
use App\Models\Pinjaman;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class PinjamanCalculationService
{
    public function hitungJasaDanSisaPinjaman(
        int $anggotaId,
        string $jenisPinjaman
    ): void {
        $dataSeluruhPinjaman = Pinjaman::query()
            ->where(
                'anggota_id',
                $anggotaId
            )
            ->where(
                'jenis_pinjaman',
                $jenisPinjaman
            )
            ->orderBy(
                'tanggal_pinjaman'
            )
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $dataSeluruhAngsuran = Angsuran::query()
            ->whereHas(
                'pinjaman',
                fn ($query) =>
                    $query
                        ->where(
                            'anggota_id',
                            $anggotaId
                        )
                        ->where(
                            'jenis_pinjaman',
                            $jenisPinjaman
                        )
            )
            ->orderBy('periode')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $seluruhPeriode = $dataSeluruhPinjaman
            ->map(
                fn (Pinjaman $pinjaman): string =>
                    $pinjaman
                        ->tanggal_pinjaman
                        ->format('Y-m')
            )
            ->merge(
                $dataSeluruhAngsuran->map(
                    fn (Angsuran $angsuran): string =>
                        $angsuran
                            ->periode
                            ->format('Y-m')
                )
            )
            ->unique()
            ->sort()
            ->values();

        $sisaPerPinjaman =
            [];

        $angsuranKe =
            0;

        $persentaseJasa =
            $this->getPersentaseJasa(
                $jenisPinjaman
            );

        foreach (
            $seluruhPeriode
            as $periode
        ) {

            $saldoAwal =
                (float) array_sum(
                    $sisaPerPinjaman
                );

            $pinjamanPeriode = $dataSeluruhPinjaman
                ->filter(
                    fn (Pinjaman $pinjaman): bool =>
                        $pinjaman
                            ->tanggal_pinjaman
                            ->format('Y-m')
                        === $periode
                )
                ->values();

            $angsuranPeriode = $dataSeluruhAngsuran
                ->filter(
                    fn (Angsuran $angsuran): bool =>
                        $angsuran
                            ->periode
                            ->format('Y-m')
                        === $periode
                )
                ->values();

            if (
                $angsuranPeriode->count()
                > 1
            ) {
                throw ValidationException::withMessages([
                    'angsuran' =>
                        'Terdapat lebih dari satu angsuran pada periode yang sama.',
                ]);
            }

            $angsuran =
                $angsuranPeriode
                    ->first();

            $nominalAngsuran =
                (float) (
                    $angsuran
                        ?->nominal_angsuran
                    ?? 0
                );

            if (
                $nominalAngsuran
                > $saldoAwal
            ) {
                $namaBulan = CarbonImmutable
                    ::createFromFormat(
                        'Y-m',
                        $periode
                    )
                    ->translatedFormat(
                        'F Y'
                    );

                throw ValidationException::withMessages([
                    'angsuran' =>
                        'Angsuran '
                        .$namaBulan
                        .' melebihi saldo pinjaman yang tersedia.',
                ]);
            }

            $jasaPinjaman =
                $angsuran
                    ? $saldoAwal
                        * (
                            $persentaseJasa
                            / 100
                        )
                    : 0;

            $sisaPembayaran =
                $nominalAngsuran;

            foreach (
                $sisaPerPinjaman
                as $pinjamanId => $sisaPencairan
            ) {
                if (
                    $sisaPembayaran
                    <= 0
                ) {
                    break;
                }

                $pengurang =
                    min(
                        $sisaPencairan,
                        $sisaPembayaran
                    );

                $sisaPerPinjaman[
                    $pinjamanId
                ] -= $pengurang;

                $sisaPembayaran -=
                    $pengurang;
            }

            foreach (
                $pinjamanPeriode
                as $pinjaman
            ) {
                $sisaPerPinjaman[
                    $pinjaman->id
                ] =
                    (float) $pinjaman
                        ->nominal_pinjaman;
            }

            $sisaPinjamanTerbaru =
                (float) array_sum(
                    $sisaPerPinjaman
                );

            if ($angsuran) {
 
                $angsuranKe++;

                $angsuran->update([
                    'angsuran_ke' =>
                        $angsuranKe,

                    'saldo_awal' =>
                        $saldoAwal,

                    'persentase_jasa' =>
                        $persentaseJasa,

                    'jasa_pinjaman' =>
                        $jasaPinjaman,

                    'sisa_pinjaman' =>
                        $sisaPinjamanTerbaru,

                    'jumlah_tagihan' =>
                        $nominalAngsuran
                        + $jasaPinjaman,
                ]);
            }
        }

        foreach (
            $dataSeluruhPinjaman
            as $pinjaman
        ) {
            $sisaPencairan =
                (float) (
                    $sisaPerPinjaman[
                        $pinjaman->id
                    ]
                    ?? 0
                );

            $pinjaman->update([
                'sisa_pinjaman' =>
                    $sisaPencairan,

                'status' =>
                    $sisaPencairan > 0
                        ? Pinjaman::STATUS_AKTIF
                        : Pinjaman::STATUS_LUNAS,
            ]);
        }
    }

    public function getSaldoSebelumPeriode(
        int $anggotaId,
        string $jenisPinjaman,
        CarbonImmutable $periode
    ): float {
        $awalPeriode =
            $periode
                ->startOfMonth()
                ->toDateString();

        $totalPinjaman =
            (float) Pinjaman::query()
                ->where(
                    'anggota_id',
                    $anggotaId
                )
                ->where(
                    'jenis_pinjaman',
                    $jenisPinjaman
                )
                ->whereDate(
                    'tanggal_pinjaman',
                    '<',
                    $awalPeriode
                )
                ->sum(
                    'nominal_pinjaman'
                );

        $totalAngsuran =
            (float) Angsuran::query()
                ->whereHas(
                    'pinjaman',
                    fn ($query) =>
                        $query
                            ->where(
                                'anggota_id',
                                $anggotaId
                            )
                            ->where(
                                'jenis_pinjaman',
                                $jenisPinjaman
                            )
                )
                ->whereDate(
                    'periode',
                    '<',
                    $awalPeriode
                )
                ->sum(
                    'nominal_angsuran'
                );

        return
            $totalPinjaman
            - $totalAngsuran;
    }

    private function getPersentaseJasa(
        string $jenisPinjaman
    ): float {
        return $jenisPinjaman
            === Pinjaman::JENIS_REGULER
                ? 1.50
                : 2.00;
    }
}