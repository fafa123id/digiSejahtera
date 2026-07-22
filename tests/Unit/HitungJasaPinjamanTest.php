<?php

namespace Tests\Unit;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Services\PinjamanCalculationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HitungJasaPinjamanTest extends TestCase
{
    use RefreshDatabase;

    private PinjamanCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PinjamanCalculationService::class);
    }

    public function test_jalur_1_tidak_ada_data_angsuran(): void
    {
        $anggota = Anggota::factory()->create();

        $this->service->hitungJasaPinjaman(
            $anggota->id,
            Pinjaman::JENIS_REGULER
        );

        $this->assertDatabaseCount('angsurans', 0);
        $this->assertTrue(true);
    }

    public function test_jalur_2_pinjaman_reguler(): void
    {
        $anggota = Anggota::factory()->create();

        $pinjaman = Pinjaman::factory()
            ->reguler()
            ->create([
                'anggota_id' => $anggota->id,
                'tanggal_pinjaman' => CarbonImmutable::parse('2025-01-05'),
                'nominal_pinjaman' => 1000000,
                'sisa_pinjaman' => 1000000,
            ]);

        $angsuran = Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'periode' => '2025-02-01',
            'tanggal_pembayaran' => '2025-02-05',
            'nominal_angsuran' => 200000,
            'saldo_awal' => 0,
            'persentase_jasa' => 0,
            'jasa_pinjaman' => 0,
            'jumlah_tagihan' => 0,
            'sisa_pinjaman' => 800000,
        ]);

        $this->service->hitungJasaPinjaman(
            $anggota->id,
            Pinjaman::JENIS_REGULER
        );

        $angsuran->refresh();

        $this->assertEquals(1, $angsuran->angsuran_ke);
        $this->assertEquals(1000000, $angsuran->saldo_awal);
        $this->assertEquals(1.50, (float) $angsuran->persentase_jasa);
        $this->assertEquals(15000, (float) $angsuran->jasa_pinjaman);
        $this->assertEquals(215000, (float) $angsuran->jumlah_tagihan);
    }

    public function test_jalur_3_pinjaman_sebrak(): void
    {
        $anggota = Anggota::factory()->create();

        $pinjaman = Pinjaman::factory()
            ->sebrak()
            ->create([
                'anggota_id' => $anggota->id,
                'tanggal_pinjaman' => CarbonImmutable::parse('2025-01-05'),
                'nominal_pinjaman' => 1000000,
                'sisa_pinjaman' => 1000000,
            ]);

        $angsuran = Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'periode' => '2025-02-01',
            'tanggal_pembayaran' => '2025-02-05',
            'nominal_angsuran' => 0,
            'saldo_awal' => 0,
            'persentase_jasa' => 0,
            'jasa_pinjaman' => 0,
            'jumlah_tagihan' => 0,
            'sisa_pinjaman' => 1000000,
        ]);

        $this->service->hitungJasaPinjaman(
            $anggota->id,
            Pinjaman::JENIS_SEBRAK
        );

        $angsuran->refresh();

        $this->assertEquals(1, $angsuran->angsuran_ke);
        $this->assertEquals(1000000, $angsuran->saldo_awal);
        $this->assertEquals(2.00, (float) $angsuran->persentase_jasa);
        $this->assertEquals(20000, (float) $angsuran->jasa_pinjaman);
        $this->assertEquals(20000, (float) $angsuran->jumlah_tagihan);
    }
}