<?php

namespace Database\Factories;

use App\Models\Angsuran;
use App\Models\Pinjaman;
use Illuminate\Database\Eloquent\Factories\Factory;

class AngsuranFactory extends Factory
{
    protected $model = Angsuran::class;

    public function definition(): array
    {
        return [
            'pinjaman_id' => Pinjaman::factory(),
            'periode' => now()->startOfMonth(),
            'tanggal_pembayaran' => now()->startOfMonth(),
            'angsuran_ke' => 1,
            'saldo_awal' => 0,
            'nominal_angsuran' => 0,
            'persentase_jasa' => 1.50,
            'jasa_pinjaman' => 0,
            'sisa_pinjaman' => 0,
            'jumlah_tagihan' => 0,
            'keterangan' => null,
            'created_by' => null,
        ];
    }
}