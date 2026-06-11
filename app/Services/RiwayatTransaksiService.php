<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RiwayatTransaksiService
{
    public function buatData(
        int $tahun,
        ?int $bulan = null,
        ?int $anggotaId = null,
        ?string $jenis = null
    ): array {
        $histories = collect();

        if (!$jenis || $jenis === 'simpanan') {
            $histories = $histories->merge(
                $this->ambilSimpanan($tahun, $bulan, $anggotaId)
            );
        }

        if (!$jenis || $jenis === 'pinjaman') {
            $histories = $histories->merge(
                $this->ambilPinjaman($tahun, $bulan, $anggotaId)
            );
        }

        if (!$jenis || $jenis === 'angsuran') {
            $histories = $histories->merge(
                $this->ambilAngsuran($tahun, $bulan, $anggotaId)
            );
        }

        return $histories
            ->sortByDesc('timestamp')
            ->values()
            ->all();
    }

    public function ambilAnggota(): array
    {
        return Anggota::query()
            ->orderByRaw('CAST(nomor_anggota AS INTEGER)')
            ->get([
                'id',
                'nomor_anggota',
                'nama',
                'status',
            ])
            ->map(fn (Anggota $anggota): array => [
                'id' => $anggota->id,
                'nomor_anggota' => $anggota->nomor_anggota,
                'nama' => $anggota->nama,
                'status' => $anggota->status,
            ])
            ->values()
            ->all();
    }

    private function ambilSimpanan(int $tahun, ?int $bulan, ?int $anggotaId): Collection
    {
        $query = Simpanan::query()->with('anggota');

        $this->filterPeriode($query, 'periode', $tahun, $bulan);

        if ($anggotaId) {
            $query->where('anggota_id', $anggotaId);
        }

        return $query
            ->get()
            ->map(function (Simpanan $simpanan): ?array {
                if (!$simpanan->anggota) {
                    return null;
                }

                $nominal = (float) $simpanan->simpanan_pokok
                    + (float) $simpanan->simpanan_wajib
                    + (float) $simpanan->simpanan_sukarela
                    + (float) $simpanan->simpanan_hari_raya
                    + (float) $simpanan->simpanan_rekreasi;

                return [
                    'key' => "simpanan-{$simpanan->id}",
                    'timestamp' => CarbonImmutable::parse($simpanan->periode)->timestamp,
                    'tanggal' => CarbonImmutable::parse($simpanan->periode)->format('Y-m-d'),
                    'periode' => CarbonImmutable::parse($simpanan->periode)->locale('id')->translatedFormat('M Y'),
                    'anggota_id' => $simpanan->anggota->id,
                    'nomor_anggota' => $simpanan->anggota->nomor_anggota,
                    'nama' => $simpanan->anggota->nama,
                    'jenis' => 'simpanan',
                    'jenis_label' => 'Simpanan',
                    'rincian' => $this->buatRincianSimpanan($simpanan),
                    'nominal' => $nominal,
                    'jasa' => null,
                    'sisa_pinjaman' => null,
                    'keterangan' => $nominal < 0
                        ? 'Penarikan simpanan anggota'
                        : 'Setoran simpanan anggota',
                ];
            })
            ->filter()
            ->values();
    }

    private function ambilPinjaman(int $tahun, ?int $bulan, ?int $anggotaId): Collection
    {
        $query = Pinjaman::query()->with('anggota');

        $this->filterPeriode($query, 'tanggal_pinjaman', $tahun, $bulan);

        if ($anggotaId) {
            $query->where('anggota_id', $anggotaId);
        }

        return $query
            ->get()
            ->map(function (Pinjaman $pinjaman): ?array {
                if (!$pinjaman->anggota) {
                    return null;
                }

                $jenisPinjaman = ucfirst($pinjaman->jenis_pinjaman);

                return [
                    'key' => "pinjaman-{$pinjaman->id}",
                    'timestamp' => CarbonImmutable::parse($pinjaman->tanggal_pinjaman)->timestamp,
                    'tanggal' => CarbonImmutable::parse($pinjaman->tanggal_pinjaman)->format('Y-m-d'),
                    'periode' => CarbonImmutable::parse($pinjaman->tanggal_pinjaman)->locale('id')->translatedFormat('M Y'),
                    'anggota_id' => $pinjaman->anggota->id,
                    'nomor_anggota' => $pinjaman->anggota->nomor_anggota,
                    'nama' => $pinjaman->anggota->nama,
                    'jenis' => 'pinjaman',
                    'jenis_label' => 'Pinjaman',
                    'rincian' => "Pinjaman {$jenisPinjaman}",
                    'nominal' => (float) $pinjaman->nominal_pinjaman,
                    'jasa' => null,
                    'sisa_pinjaman' => (float) $pinjaman->sisa_pinjaman,
                    'keterangan' => $pinjaman->keterangan ?: "Pencairan pinjaman {$jenisPinjaman}",
                ];
            })
            ->filter()
            ->values();
    }

    private function ambilAngsuran(int $tahun, ?int $bulan, ?int $anggotaId): Collection
    {
        $query = Angsuran::query()->with('pinjaman.anggota');

        $this->filterPeriode($query, 'periode', $tahun, $bulan);

        if ($anggotaId) {
            $query->whereHas('pinjaman', function (Builder $query) use ($anggotaId): void {
                $query->where('anggota_id', $anggotaId);
            });
        }

        return $query
            ->get()
            ->map(function (Angsuran $angsuran): ?array {
                $pinjaman = $angsuran->pinjaman;
                $anggota = $pinjaman?->anggota;

                if (!$pinjaman || !$anggota) {
                    return null;
                }

                $jenisPinjaman = ucfirst($pinjaman->jenis_pinjaman);
                $angsuranKe = $angsuran->angsuran_ke
                    ? " ke-{$angsuran->angsuran_ke}"
                    : '';

                $tanggal = $angsuran->tanggal_pembayaran ?: $angsuran->periode;

                return [
                    'key' => "angsuran-{$angsuran->id}",
                    'timestamp' => CarbonImmutable::parse($tanggal)->timestamp,
                    'tanggal' => CarbonImmutable::parse($tanggal)->format('Y-m-d'),
                    'periode' => CarbonImmutable::parse($angsuran->periode)->locale('id')->translatedFormat('M Y'),
                    'anggota_id' => $anggota->id,
                    'nomor_anggota' => $anggota->nomor_anggota,
                    'nama' => $anggota->nama,
                    'jenis' => 'angsuran',
                    'jenis_label' => 'Angsuran',
                    'rincian' => "Angsuran {$jenisPinjaman}{$angsuranKe}",
                    'nominal' => (float) $angsuran->nominal_angsuran,
                    'jasa' => (float) $angsuran->jasa_pinjaman,
                    'sisa_pinjaman' => (float) $angsuran->sisa_pinjaman,
                    'keterangan' => $angsuran->keterangan ?: "Pembayaran angsuran pinjaman {$jenisPinjaman}",
                ];
            })
            ->filter()
            ->values();
    }

    private function buatRincianSimpanan(Simpanan $simpanan): string
    {
        $fields = [
            'simpanan_pokok' => 'SIMPOK',
            'simpanan_wajib' => 'SIMWA',
            'simpanan_sukarela' => 'SSR',
            'simpanan_hari_raya' => 'SHR',
            'simpanan_rekreasi' => 'SREK',
        ];

        return collect($fields)
            ->filter(fn (string $label, string $field): bool => (float) $simpanan->{$field} !== 0.0)
            ->values()
            ->implode(', ');
    }

    private function filterPeriode(Builder $query, string $column, int $tahun, ?int $bulan): void
    {
        $query->whereYear($column, $tahun);

        if ($bulan) {
            $query->whereMonth($column, $bulan);
        }
    }
}