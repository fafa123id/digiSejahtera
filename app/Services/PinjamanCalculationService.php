<?php

namespace App\Services;

use App\Models\Angsuran;
use App\Models\Pinjaman;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class PinjamanCalculationService
{
    public function hitungSisaPinjaman(int $anggotaId, string $jenisPinjaman, ?CarbonImmutable $mulaiPeriode = null): void
    {
        $periodeMulai = $mulaiPeriode?->startOfMonth();
        $seluruhPeriode = $this->getPeriodeTransaksi($anggotaId, $jenisPinjaman, $periodeMulai);

        foreach ($seluruhPeriode as $periodeString) {
            $periode = CarbonImmutable::createFromFormat('Y-m', $periodeString)->startOfMonth();
            $awalPeriode = $periode->toDateString();
            $akhirPeriode = $periode->endOfMonth()->toDateString();

            $saldoAwal = $this->getSaldoSebelumPeriode($anggotaId, $jenisPinjaman, $periode);
            $angsuran = $this->getAngsuranPeriode($anggotaId, $jenisPinjaman, $periode);
            $nominalAngsuran = (float) ($angsuran?->nominal_angsuran ?? 0);

            if ($nominalAngsuran > $saldoAwal) {
                throw ValidationException::withMessages([
                    'angsuran' => 'Angsuran ' . $periode->translatedFormat('F Y') . ' melebihi saldo pinjaman yang tersedia.',
                ]);
            }

            $pinjamanTambahan = (float) Pinjaman::query()
                ->where('anggota_id', $anggotaId)
                ->where('jenis_pinjaman', $jenisPinjaman)
                ->whereBetween('tanggal_pinjaman', [$awalPeriode, $akhirPeriode])
                ->sum('nominal_pinjaman');

            $sisaPinjamanTerbaru = $saldoAwal - $nominalAngsuran + $pinjamanTambahan;

            if ($angsuran) {
                $angsuran->update([
                    'sisa_pinjaman' => $sisaPinjamanTerbaru,
                ]);
            }
        }
        $pinjamans = Pinjaman::query()
            ->where('anggota_id', $anggotaId)
            ->where('jenis_pinjaman', $jenisPinjaman)
            ->orderBy('tanggal_pinjaman')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $sisaAngsuran = (float) Angsuran::query()
            ->whereHas('pinjaman', fn($query) => $query
                ->where('anggota_id', $anggotaId)
                ->where('jenis_pinjaman', $jenisPinjaman))
            ->sum('nominal_angsuran');

        foreach ($pinjamans as $pinjaman) {
            $nominalPinjaman = (float) $pinjaman->nominal_pinjaman;
            $terbayar = min($nominalPinjaman, $sisaAngsuran);
            $sisaPencairan = $nominalPinjaman - $terbayar;
            $sisaAngsuran -= $terbayar;

            $pinjaman->update([
                'sisa_pinjaman' => $sisaPencairan,
                'status' => $sisaPencairan > 0 ? Pinjaman::STATUS_AKTIF : Pinjaman::STATUS_LUNAS,
            ]);
        }
    }


    public function hitungJasaPinjaman(int $anggotaId, string $jenisPinjaman): void
    {
        $dataSeluruhAngsuran = Angsuran::query()
            ->whereHas('pinjaman', fn($query) => $query
                ->where('anggota_id', $anggotaId)
                ->where('jenis_pinjaman', $jenisPinjaman))
            ->orderBy('periode')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $persentaseJasa = $jenisPinjaman === Pinjaman::JENIS_REGULER ? 1.50 : 2.00;
        $angsuranKe = 0;

        foreach ($dataSeluruhAngsuran as $angsuran) {
            $periode = CarbonImmutable::parse($angsuran->periode);
            $saldoAwal = $this->getSaldoSebelumPeriode($anggotaId, $jenisPinjaman, $periode);
            $nominalAngsuran = (float) $angsuran->nominal_angsuran;
            $jasaPinjaman = $saldoAwal * ($persentaseJasa / 100);
            $jumlahTagihan = $nominalAngsuran + $jasaPinjaman;

            $angsuranKe++;

            $angsuran->update([
                'angsuran_ke' => $angsuranKe,
                'saldo_awal' => $saldoAwal,
                'persentase_jasa' => $persentaseJasa,
                'jasa_pinjaman' => $jasaPinjaman,
                'jumlah_tagihan' => $jumlahTagihan,
            ]);
        }
    }

    public function getSaldoSebelumPeriode(int $anggotaId, string $jenisPinjaman, CarbonImmutable $periode): float
    {
        $awalPeriode = $periode->startOfMonth()->toDateString();

        $totalPinjaman = (float) Pinjaman::query()
            ->where('anggota_id', $anggotaId)
            ->where('jenis_pinjaman', $jenisPinjaman)
            ->whereDate('tanggal_pinjaman', '<', $awalPeriode)
            ->sum('nominal_pinjaman');

        $totalAngsuran = (float) Angsuran::query()
            ->whereHas('pinjaman', fn($query) => $query
                ->where('anggota_id', $anggotaId)
                ->where('jenis_pinjaman', $jenisPinjaman))
            ->whereDate('periode', '<', $awalPeriode)
            ->sum('nominal_angsuran');

        return $totalPinjaman - $totalAngsuran;
    }

    private function getPeriodeTransaksi(
        int $anggotaId,
        string $jenisPinjaman,
        ?CarbonImmutable $mulaiPeriode
    ): \Illuminate\Support\Collection {

        $periodePinjaman = Pinjaman::query()
            ->where('anggota_id', $anggotaId)
            ->where('jenis_pinjaman', $jenisPinjaman)
            ->when(
                $mulaiPeriode,
                fn($query) =>
                $query->whereDate('tanggal_pinjaman', '>=', $mulaiPeriode->toDateString())
            )
            ->pluck('tanggal_pinjaman')
            ->map(fn($tanggal) => CarbonImmutable::parse($tanggal)->format('Y-m'));

        $periodeAngsuran = Angsuran::query()
            ->whereHas('pinjaman', fn($query) => $query
                ->where('anggota_id', $anggotaId)
                ->where('jenis_pinjaman', $jenisPinjaman))
            ->when(
                $mulaiPeriode,
                fn($query) =>
                $query->whereDate('periode', '>=', $mulaiPeriode->toDateString())
            )
            ->pluck('periode')
            ->map(fn($tanggal) => CarbonImmutable::parse($tanggal)->format('Y-m'));

        return $periodePinjaman
            ->merge($periodeAngsuran)
            ->unique()
            ->sort()
            ->values();
    }

    private function getAngsuranPeriode(int $anggotaId, string $jenisPinjaman, CarbonImmutable $periode): ?Angsuran
    {
        return Angsuran::query()
            ->whereHas('pinjaman', fn($query) => $query
                ->where('anggota_id', $anggotaId)
                ->where('jenis_pinjaman', $jenisPinjaman))
            ->whereDate('periode', $periode->startOfMonth()->toDateString())
            ->orderBy('id')
            ->lockForUpdate()
            ->first();
    }

}
