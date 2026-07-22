<?php

namespace Database\Factories;

use App\Models\Anggota;
use App\Models\Pinjaman;
use Illuminate\Database\Eloquent\Factories\Factory;

class PinjamanFactory extends Factory
{
    protected $model = Pinjaman::class;

    public function definition(): array
    {
        return [
            'anggota_id' => Anggota::factory(),
            'tanggal_pinjaman' => now()->startOfMonth(),
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
            'nominal_pinjaman' => 1000000,
            'persentase_jasa' => 1.50,
            'sisa_pinjaman' => 1000000,
            'status' => Pinjaman::STATUS_AKTIF,
            'keterangan' => null,
            'created_by' => null,
        ];
    }

    public function reguler(): static
    {
        return $this->state([
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
            'persentase_jasa' => 1.50,
        ]);
    }

    public function sebrak(): static
    {
        return $this->state([
            'jenis_pinjaman' => Pinjaman::JENIS_SEBRAK,
            'persentase_jasa' => 2.00,
        ]);
    }

    public function lunas(): static
    {
        return $this->state([
            'sisa_pinjaman' => 0,
            'status' => Pinjaman::STATUS_LUNAS,
        ]);
    }
}