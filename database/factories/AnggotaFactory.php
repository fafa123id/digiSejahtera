<?php

namespace Database\Factories;

use App\Models\Anggota;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnggotaFactory extends Factory
{
    protected $model = Anggota::class;

    public function definition(): array
    {
        return [
            'nomor_anggota' => fake()->unique()->numerify('A###'),
            'nama' => fake()->name(),
            'agama' => fake()->randomElement([Anggota::AGAMA_ISLAM, Anggota::AGAMA_NONISLAM]),
            'tanggal_masuk' => now(),
            'tanggal_keluar' => null,
            'status' => 'aktif',
        ];
    }
}