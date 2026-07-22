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
                'agama' => $data['agama'],
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
        if ($anggota->status === Anggota::STATUS_AKTIF) {
            throw ValidationException::withMessages([
                'anggota' => 'Anggota masih aktif, tidak bisa dihapus.',
            ]);
        }

        $anggota->forceDelete();
        DB::transaction(function () use ($anggota) {

            $nomor = (int) $anggota->nomor_anggota;

            $anggota->forceDelete();

            Anggota::whereRaw('CAST(nomor_anggota AS UNSIGNED) > ?', [$nomor])
                ->orderBy('nomor_anggota')
                ->get()
                ->each(function (Anggota $item) {
                    $item->update([
                        'nomor_anggota' => str_pad(
                            (string) ((int) $item->nomor_anggota - 1),
                            3,
                            '0',
                            STR_PAD_LEFT
                        ),
                    ]);
                });
        });
    }
    private function buatNomorAnggotaBerikutnya(): string
    {
        return str_pad(
            (string) (Anggota::count() + 1),
            3,
            '0',
            STR_PAD_LEFT
        );
    }
}
