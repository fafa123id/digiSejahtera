<?php

namespace App\Services;

use App\Models\Anggota;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnggotaService
{
    public function tambah(array $data): Anggota
    {
        return DB::transaction(function () use ($data): Anggota {
            return Anggota::create([
                'nomor_anggota' =>
                    $this->buatNomorAnggotaBerikutnya(),

                'nama' =>
                    $data['nama'],

                'tanggal_masuk' =>
                    $data['tanggal_masuk'],

                'tanggal_keluar' =>
                    null,

                'status' =>
                    Anggota::STATUS_AKTIF,
            ]);
        });
    }

    public function ubahNama(
        Anggota $anggota,
        array $data
    ): Anggota {
        $anggota->update([
            'nama' => $data['nama'],
        ]);

        return $anggota;
    }

    public function keluarkan(
        Anggota $anggota
    ): Anggota {
        $anggota->update([
            'status' =>
                Anggota::STATUS_NONAKTIF,

            'tanggal_keluar' =>
                now()->toDateString(),
        ]);

        return $anggota;
    }

    public function hapus(
        Anggota $anggota
    ): void {
        if (
            $anggota->simpanans()->exists()
            || $anggota->pinjamans()->exists()
            || $anggota->shuAnggotas()->exists()
        ) {
            throw ValidationException::withMessages([
                'anggota' =>
                    'Data anggota tidak dapat dihapus karena memiliki riwayat transaksi. Gunakan tombol keluarkan anggota.',
            ]);
        }

        $anggota->forceDelete();
    }

    private function buatNomorAnggotaBerikutnya(): string
    {
        $nomorTerakhir = Anggota::withTrashed()
            ->pluck('nomor_anggota')
            ->map(
                fn (string $nomor): int =>
                    (int) $nomor
            )
            ->max() ?? 0;

        return str_pad(
            string: (string) ($nomorTerakhir + 1),
            length: 3,
            pad_string: '0',
            pad_type: STR_PAD_LEFT,
        );
    }
}