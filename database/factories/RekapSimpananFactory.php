<?php

namespace Database\Factories;

use App\Models\Anggota;
use Illuminate\Database\Eloquent\Factories\Factory;

class RekapSimpananFactory extends Factory
{
    public function definition(): array
    {
        return [
            'anggota_id' => Anggota::factory(),
            'total_simpanan_pokok' => 0,
            'total_simpanan_wajib' => 0,
            'total_simpanan_sukarela' => 0,
            'total_simpanan_hari_raya' => 0,
            'total_simpanan_rekreasi' => 0,
            'total_simpanan' => 0,
        ];
    }
}