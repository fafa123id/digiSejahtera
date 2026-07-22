<?php

namespace Database\Factories;

use App\Models\Anggota;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShuAnggotaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'anggota_id' => Anggota::factory(),
            'tahun' => now()->year,
            'total_simpanan' => 0,
            'total_jasa_pinjaman' => 0,
            'persentase_simpanan' => 50,
            'persentase_jasa_pinjaman' => 50,
            'shu_simpanan' => 0,
            'shu_pinjaman' => 0,
            'total_shu' => 0,
            'calculated_at' => now(),
        ];
    }
}