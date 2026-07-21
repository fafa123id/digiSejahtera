<?php

namespace Tests\Unit\Services;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Models\RekapSimpanan;
use App\Models\ShuAnggota;
use App\Services\ShuService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HitungSHUAnggotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_jalur_1_tanpa_rekap_simpanan(): void
    {
        $anggota = Anggota::factory()->create();

        $pinjaman = Pinjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
        ]);

        Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'jasa_pinjaman' => 200000,
        ]);

        $hasil = app(ShuService::class)
            ->hitungSHUAnggota($anggota);

        $this->assertInstanceOf(ShuAnggota::class, $hasil);

        $this->assertEquals(0, $hasil->total_simpanan);
        $this->assertEquals(200000, $hasil->total_jasa_pinjaman);

        $this->assertEquals(0, $hasil->shu_simpanan);
        $this->assertEquals(100000, $hasil->shu_pinjaman);

        $this->assertEquals(100000, $hasil->total_shu);
    }

    public function test_jalur_2_dengan_rekap_simpanan(): void
    {
        $anggota = Anggota::factory()->create();

        RekapSimpanan::factory()->create([
            'anggota_id' => $anggota->id,
            'total_simpanan' => 1000000,
        ]);

        $pinjaman = Pinjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
        ]);

        Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'jasa_pinjaman' => 200000,
        ]);

        $hasil = app(ShuService::class)
            ->hitungSHUAnggota($anggota);

        $this->assertEquals(1000000, $hasil->total_simpanan);
        $this->assertEquals(200000, $hasil->total_jasa_pinjaman);

        $this->assertEquals(500000, $hasil->shu_simpanan);
        $this->assertEquals(100000, $hasil->shu_pinjaman);

        $this->assertEquals(600000, $hasil->total_shu);
    }
}