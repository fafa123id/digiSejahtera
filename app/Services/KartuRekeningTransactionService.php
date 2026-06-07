<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KartuRekeningTransactionService
{
    public function __construct(
        private readonly RekapSimpananService $rekapSimpananService,
        private readonly PinjamanCalculationService $pinjamanCalculationService,
        private readonly ShuService $shuService
    ) {}
    private function isSimpananKosong(
        Simpanan $simpanan
    ): bool {
        return
            (float) $simpanan
                ->simpanan_pokok
            === 0.0
            && (float) $simpanan
                ->simpanan_wajib
            === 0.0
            && (float) $simpanan
                ->simpanan_sukarela
            === 0.0
            && (float) $simpanan
                ->simpanan_hari_raya
            === 0.0
            && (float) $simpanan
                ->simpanan_rekreasi
            === 0.0;
    }
    public function submitSimpanan(
        Anggota $anggota,
        CarbonImmutable $periode,
        Collection $changes,
        int $userId,
        bool $hitungUlang = true
    ): bool {
        return DB::transaction(
            function () use (
                $anggota,
                $periode,
                $changes,
                $userId,
                $hitungUlang
            ): bool {
                $simpanan = Simpanan::query()
                    ->firstOrNew([
                        'anggota_id' =>
                        $anggota->id,

                        'periode' =>
                        $periode
                            ->toDateString(),
                    ]);

                $simpanan->fill([
                    'simpanan_pokok' =>
                    $simpanan
                        ->simpanan_pokok
                        ?? 0,

                    'simpanan_wajib' =>
                    $simpanan
                        ->simpanan_wajib
                        ?? 0,

                    'simpanan_sukarela' =>
                    $simpanan
                        ->simpanan_sukarela
                        ?? 0,

                    'simpanan_hari_raya' =>
                    $simpanan
                        ->simpanan_hari_raya
                        ?? 0,

                    'simpanan_rekreasi' =>
                    $simpanan
                        ->simpanan_rekreasi
                        ?? 0,
                ]);

                foreach (
                    $changes
                    as $change
                ) {
                    $simpanan->setAttribute(
                        $change['field'],
                        $this->angka(
                            $change['value']
                                ?? null
                        )
                    );
                }

                $simpanan->jumlah_simpanan =
                    (float) $simpanan
                        ->simpanan_pokok
                    + (float) $simpanan
                        ->simpanan_wajib
                    + (float) $simpanan
                        ->simpanan_sukarela
                    + (float) $simpanan
                        ->simpanan_hari_raya
                    + (float) $simpanan
                        ->simpanan_rekreasi;


                if (
                    $this->isSimpananKosong(
                        $simpanan
                    )
                ) {
                    if ($simpanan->exists) {
                        $simpanan->delete();
                    }

                    if ($hitungUlang) {
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

                    return true;
                }

                $simpanan->created_by =
                    $simpanan->created_by
                    ?? $userId;

                $simpanan->save();

                if ($hitungUlang) {
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

                return true;
            }
        );
    }

    public function submitPinjaman(
        Anggota $anggota,
        string $jenis,
        CarbonImmutable $periode,
        string $action,
        mixed $rawValue,
        ?int $pinjamanId,
        int $userId,
        bool $hitungUlang = true
    ): bool {
        return DB::transaction(
            function () use (
                $anggota,
                $jenis,
                $periode,
                $action,
                $rawValue,
                $pinjamanId,
                $userId,
                $hitungUlang
            ): bool {
                $nominal =
                    $this->angka(
                        $rawValue
                    );

                $berubah = match ($action) {
                    'create_pinjaman' =>
                    $this->buatPinjaman(
                        anggota: $anggota,
                        jenis: $jenis,
                        periode: $periode,
                        rawValue: $rawValue,
                        nominal: $nominal,
                        userId: $userId,
                    ),

                    'update_pinjaman' =>
                    $this->ubahPinjaman(
                        anggota: $anggota,
                        jenis: $jenis,
                        pinjamanId: (int) $pinjamanId,
                        rawValue: $rawValue,
                        nominal: $nominal,
                    ),

                    default =>
                    throw ValidationException
                        ::withMessages([
                            'pinjaman' =>
                            'Jenis perubahan pinjaman tidak valid.',
                        ]),
                };

                if (
                    $berubah
                    && $hitungUlang
                ) {
                    $this
                        ->pinjamanCalculationService
                        ->hitungJasaDanSisaPinjaman(
                            anggotaId: $anggota->id,

                            jenisPinjaman: $jenis,
                        );

                    $this
                        ->shuService
                        ->hitungSHUAnggota(
                            $anggota
                        );
                }

                return $berubah;
            }
        );
    }

    public function submitAngsuran(
        Anggota $anggota,
        string $jenis,
        CarbonImmutable $periode,
        string $action,
        mixed $rawValue,
        ?int $angsuranId,
        int $userId,
        bool $hitungUlang = true
    ): bool {
        return DB::transaction(
            function () use (
                $anggota,
                $jenis,
                $periode,
                $action,
                $rawValue,
                $angsuranId,
                $userId,
                $hitungUlang
            ): bool {
                $nominal =
                    $this->angka(
                        $rawValue
                    );

                $berubah = match ($action) {
                    'create_angsuran' =>
                    $this->buatAngsuran(
                        anggota: $anggota,
                        jenis: $jenis,
                        periode: $periode,
                        rawValue: $rawValue,
                        nominal: $nominal,
                        userId: $userId,
                    ),

                    'update_angsuran' =>
                    $this->ubahAngsuran(
                        anggota: $anggota,
                        jenis: $jenis,
                        angsuranId: (int) $angsuranId,
                        rawValue: $rawValue,
                        nominal: $nominal,
                    ),

                    default =>
                    throw ValidationException
                        ::withMessages([
                            'angsuran' =>
                            'Jenis perubahan angsuran tidak valid.',
                        ]),
                };

                if (
                    $berubah
                    && $hitungUlang
                ) {
                    $this
                        ->pinjamanCalculationService
                        ->hitungJasaDanSisaPinjaman(
                            anggotaId: $anggota->id,

                            jenisPinjaman: $jenis,
                        );

                    $this
                        ->shuService
                        ->hitungSHUAnggota(
                            $anggota
                        );
                }

                return $berubah;
            }
        );
    }

    private function buatPinjaman(
        Anggota $anggota,
        string $jenis,
        CarbonImmutable $periode,
        mixed $rawValue,
        float $nominal,
        int $userId
    ): bool {

        if (
            $this->nilaiKosong(
                $rawValue
            )
            || $nominal <= 0
        ) {
            return false;
        }

        $saldoSebelum =
            $this
            ->pinjamanCalculationService
            ->getSaldoSebelumPeriode(
                anggotaId: $anggota->id,

                jenisPinjaman: $jenis,

                periode: $periode,
            );

        $sudahAdaPencairan =
            Pinjaman::query()
            ->where(
                'anggota_id',
                $anggota->id
            )
            ->where(
                'jenis_pinjaman',
                $jenis
            )
            ->exists();

        Pinjaman::create([
            'anggota_id' =>
            $anggota->id,

            'tanggal_pinjaman' =>
            $periode
                ->toDateString(),

            'jenis_pinjaman' =>
            $jenis,

            'nominal_pinjaman' =>
            $nominal,

            'persentase_jasa' =>
            $this->getPersentaseJasa(
                $jenis
            ),

            'sisa_pinjaman' =>
            $nominal,

            'status' =>
            Pinjaman::STATUS_AKTIF,

            'keterangan' =>
            $saldoSebelum > 0
                || $sudahAdaPencairan
                ? 'Tambahan pinjaman'
                : 'Pencairan pinjaman awal',

            'created_by' =>
            $userId,
        ]);

        return true;
    }

    private function ubahPinjaman(
        Anggota $anggota,
        string $jenis,
        int $pinjamanId,
        mixed $rawValue,
        float $nominal
    ): bool {
        $pinjaman = Pinjaman::query()
            ->where(
                'anggota_id',
                $anggota->id
            )
            ->where(
                'jenis_pinjaman',
                $jenis
            )
            ->findOrFail(
                $pinjamanId
            );

        if (
            $this->nilaiKosong(
                $rawValue
            )
            || $nominal <= 0
        ) {
            $this->hapusPinjaman(
                $pinjaman
            );

            return true;
        }

        $pinjaman->update([
            'nominal_pinjaman' =>
            $nominal,
        ]);

        return true;
    }

    private function buatAngsuran(
        Anggota $anggota,
        string $jenis,
        CarbonImmutable $periode,
        mixed $rawValue,
        float $nominal,
        int $userId
    ): bool {
        /*
     * Cell kosong berarti user tidak jadi melakukan input.
     */
        if (
            $this->nilaiKosong(
                $rawValue
            )
        ) {
            return false;
        }

        /*
     * Reguler 0 diabaikan.
     * Sebrak 0 tetap diproses sebagai pembayaran jasa.
     */
        if (
            $jenis
            === Pinjaman::JENIS_REGULER
            && $nominal <= 0
        ) {
            return false;
        }

        $saldoSebelum =
            $this
            ->pinjamanCalculationService
            ->getSaldoSebelumPeriode(
                anggotaId: $anggota->id,

                jenisPinjaman: $jenis,

                periode: $periode,
            );

        $this->validasiNominalAngsuran(
            jenis: $jenis,

            nominal: $nominal,

            saldoSebelum: $saldoSebelum,
        );

        $sudahAdaAngsuran =
            Angsuran::query()
            ->whereHas(
                'pinjaman',
                fn($query) =>
                $query
                    ->where(
                        'anggota_id',
                        $anggota->id
                    )
                    ->where(
                        'jenis_pinjaman',
                        $jenis
                    )
            )
            ->whereDate(
                'periode',
                $periode
                    ->toDateString()
            )
            ->exists();

        if ($sudahAdaAngsuran) {
            throw ValidationException::withMessages([
                'angsuran' =>
                'Angsuran pada periode tersebut sudah tersedia. Ubah nilai angsuran yang sudah ada.',
            ]);
        }

        $pinjamanAnchor = Pinjaman::query()
            ->where(
                'anggota_id',
                $anggota->id
            )
            ->where(
                'jenis_pinjaman',
                $jenis
            )
            ->whereDate(
                'tanggal_pinjaman',
                '<',
                $periode
                    ->startOfMonth()
                    ->toDateString()
            )
            ->latest(
                'tanggal_pinjaman'
            )
            ->latest('id')
            ->first();

        if (!$pinjamanAnchor) {
            throw ValidationException::withMessages([
                'angsuran' =>
                'Belum terdapat pinjaman dari periode sebelumnya yang dapat diangsur.',
            ]);
        }

        Angsuran::create([
            'pinjaman_id' =>
            $pinjamanAnchor->id,

            'periode' =>
            $periode
                ->toDateString(),

            'tanggal_pembayaran' =>
            $periode
                ->toDateString(),

            'angsuran_ke' =>
            null,

            'saldo_awal' =>
            0,

            'nominal_angsuran' =>
            $nominal,

            'persentase_jasa' =>
            $this->getPersentaseJasa(
                $jenis
            ),

            'jasa_pinjaman' =>
            0,

            'sisa_pinjaman' =>
            0,

            'jumlah_tagihan' =>
            0,

            'keterangan' =>
            $nominal > 0
                ? 'Pembayaran angsuran pinjaman'
                : 'Pembayaran jasa pinjaman',

            'created_by' =>
            $userId,
        ]);

        return true;
    }

    private function ubahAngsuran(
        Anggota $anggota,
        string $jenis,
        int $angsuranId,
        mixed $rawValue,
        float $nominal
    ): bool {
        $angsuran = Angsuran::query()
            ->with('pinjaman')
            ->findOrFail(
                $angsuranId
            );

        if (
            $angsuran
            ->pinjaman
            ->anggota_id
            !== $anggota->id
            || $angsuran
            ->pinjaman
            ->jenis_pinjaman
            !== $jenis
        ) {
            abort(404);
        }

        /*
     * Jangan memakai kondisi $nominal <= 0 secara umum.
     *
     * Sebrak bernilai 0 harus tetap disimpan.
     */
        $harusDihapus =
            $this->nilaiKosong(
                $rawValue
            )
            || (
                $jenis
                === Pinjaman::JENIS_REGULER
                && $nominal <= 0
            );

        if ($harusDihapus) {
            $angsuran->delete();

            return true;
        }

        $saldoSebelum =
            $this
            ->pinjamanCalculationService
            ->getSaldoSebelumPeriode(
                anggotaId: $anggota->id,

                jenisPinjaman: $jenis,

                periode: CarbonImmutable::parse(
                    $angsuran->periode
                ),
            );

        $this->validasiNominalAngsuran(
            jenis: $jenis,

            nominal: $nominal,

            saldoSebelum: $saldoSebelum,
        );

        $angsuran->update([
            'nominal_angsuran' =>
            $nominal,

            'keterangan' =>
            $nominal > 0
                ? 'Pembayaran angsuran pinjaman'
                : 'Pembayaran jasa pinjaman',
        ]);

        return true;
    }

    private function hapusPinjaman(
        Pinjaman $pinjaman
    ): void {
        $angsurans =
            $pinjaman
            ->angsurans()
            ->orderBy('periode')
            ->orderBy('id')
            ->get();

        foreach (
            $angsurans
            as $angsuran
        ) {
            $awalPeriodeAngsuran =
                CarbonImmutable::parse(
                    $angsuran->periode
                )
                ->startOfMonth()
                ->toDateString();

            $pinjamanPengganti = Pinjaman::query()
                ->where(
                    'anggota_id',
                    $pinjaman->anggota_id
                )
                ->where(
                    'jenis_pinjaman',
                    $pinjaman->jenis_pinjaman
                )
                ->where(
                    'id',
                    '!=',
                    $pinjaman->id
                )
                ->whereDate(
                    'tanggal_pinjaman',
                    '<',
                    $awalPeriodeAngsuran
                )
                ->latest(
                    'tanggal_pinjaman'
                )
                ->latest('id')
                ->first();

            if (!$pinjamanPengganti) {
                throw ValidationException::withMessages([
                    'pinjaman' =>
                    'Pinjaman tidak dapat dihapus karena masih menjadi dasar angsuran. Kosongkan angsuran terkait terlebih dahulu.',
                ]);
            }

            $angsuran->update([
                'pinjaman_id' =>
                $pinjamanPengganti->id,
            ]);
        }

        $pinjaman->delete();
    }

    private function validasiNominalAngsuran(
        string $jenis,
        float $nominal,
        float $saldoSebelum
    ): void {
        if ($saldoSebelum <= 0) {
            throw ValidationException::withMessages([
                'angsuran' =>
                'Tidak terdapat saldo pinjaman dari periode sebelumnya.',
            ]);
        }

        if ($nominal < 0) {
            throw ValidationException::withMessages([
                'angsuran' =>
                'Nominal angsuran tidak boleh negatif.',
            ]);
        }

        if (
            $jenis
            === Pinjaman::JENIS_REGULER
            && $nominal <= 0
        ) {
            throw ValidationException::withMessages([
                'angsuran' =>
                'Angsuran reguler harus lebih besar dari nol.',
            ]);
        }

        if (
            $nominal
            > $saldoSebelum
        ) {
            throw ValidationException::withMessages([
                'angsuran' =>
                'Nominal angsuran melebihi saldo pinjaman.',
            ]);
        }
    }

    private function getPersentaseJasa(
        string $jenisPinjaman
    ): float {
        return $jenisPinjaman
            === Pinjaman::JENIS_REGULER
            ? 1.50
            : 2.00;
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
