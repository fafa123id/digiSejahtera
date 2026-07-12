<?php

namespace Tests\Unit;

use App\Models\Anggota;
use App\Models\RekapSimpanan;
use App\Models\Simpanan;
use App\Services\RekapSimpananService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HitungTotalSimpananTest extends TestCase
{
    use RefreshDatabase;

    public function test_hitung_total_simpanan_berhasil()
    {
        $anggota = Anggota::factory()->create();

        Simpanan::create([
            'anggota_id' => $anggota->id,
            'periode' => '2025-01-01',
            'simpanan_pokok' => 100000,
            'simpanan_wajib' => 50000,
            'simpanan_sukarela' => 25000,
            'simpanan_hari_raya' => 100000,
            'simpanan_rekreasi' => 30000,
            'jumlah_simpanan' => 305000,
        ]);

        Simpanan::create([
            'anggota_id' => $anggota->id,
            'periode' => '2025-02-01',
            'simpanan_pokok' => 0,
            'simpanan_wajib' => 50000,
            'simpanan_sukarela' => 25000,
            'simpanan_hari_raya' => 100000,
            'simpanan_rekreasi' => 30000,
            'jumlah_simpanan' => 205000,
        ]);

        $service = new RekapSimpananService();

        $hasil = $service->hitungTotalSimpanan($anggota);

        $this->assertInstanceOf(RekapSimpanan::class, $hasil);

        $this->assertEquals(100000, $hasil->total_simpanan_pokok);
        $this->assertEquals(100000, $hasil->total_simpanan_wajib);
        $this->assertEquals(50000, $hasil->total_simpanan_sukarela);
        $this->assertEquals(200000, $hasil->total_simpanan_hari_raya);
        $this->assertEquals(60000, $hasil->total_simpanan_rekreasi);
        $this->assertEquals(510000, $hasil->total_simpanan);

        $this->assertDatabaseHas('rekap_simpanans', [
            'anggota_id' => $anggota->id,
            'total_simpanan_pokok' => 100000,
            'total_simpanan_wajib' => 100000,
            'total_simpanan_sukarela' => 50000,
            'total_simpanan_hari_raya' => 200000,
            'total_simpanan_rekreasi' => 60000,
            'total_simpanan' => 510000,
        ]);
    }
}