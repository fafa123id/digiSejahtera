<?php

namespace Tests\Unit;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Services\PinjamanCalculationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HitungSisaPinjamanTest extends TestCase
{
    use RefreshDatabase;

    private PinjamanCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PinjamanCalculationService::class);
    }

    public function test_jalur_1_tidak_ada_pinjaman_dan_angsuran(): void
    {
        $anggota = Anggota::factory()->create();

        $this->service->hitungSisaPinjaman(
            anggotaId: $anggota->id,
            jenisPinjaman: Pinjaman::JENIS_REGULER
        );

        $this->assertDatabaseCount('pinjamans', 0);
        $this->assertDatabaseCount('angsurans', 0);
    }

    public function test_jalur_2_angsuran_melebihi_saldo(): void
    {
        $anggota = Anggota::factory()->create();

        $pinjaman = Pinjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
            'tanggal_pinjaman' => '2025-01-05',
            'nominal_pinjaman' => 1000000,
        ]);

        Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'periode' => '2025-02-01',
            'nominal_angsuran' => 1200000,
        ]);

        $this->expectException(ValidationException::class);

        $this->service->hitungSisaPinjaman(
            anggotaId: $anggota->id,
            jenisPinjaman: Pinjaman::JENIS_REGULER
        );
    }

    public function test_jalur_3_pinjaman_tanpa_angsuran(): void
    {
        $anggota = Anggota::factory()->create();

        $pinjaman = Pinjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
            'tanggal_pinjaman' => '2025-01-10',
            'nominal_pinjaman' => 10000000,
        ]);

        $this->service->hitungSisaPinjaman(
            anggotaId: $anggota->id,
            jenisPinjaman: Pinjaman::JENIS_REGULER
        );

        $pinjaman->refresh();

        $this->assertEquals(
            10000000,
            $pinjaman->sisa_pinjaman
        );

        $this->assertEquals(
            Pinjaman::STATUS_AKTIF,
            $pinjaman->status
        );
    }

    public function test_jalur_4_pinjaman_dengan_angsuran_sebagian(): void
    {
        $anggota = Anggota::factory()->create();

        $pinjaman = Pinjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
            'tanggal_pinjaman' => '2025-01-10',
            'nominal_pinjaman' => 10000000,
        ]);

        $angsuran = Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'periode' => '2025-02-01',
            'nominal_angsuran' => 3000000,
        ]);

        $this->service->hitungSisaPinjaman(
            anggotaId: $anggota->id,
            jenisPinjaman: Pinjaman::JENIS_REGULER
        );

        $pinjaman->refresh();
        $angsuran->refresh();

        $this->assertEquals(
            7000000,
            $angsuran->sisa_pinjaman
        );

        $this->assertEquals(
            7000000,
            $pinjaman->sisa_pinjaman
        );

        $this->assertEquals(
            Pinjaman::STATUS_AKTIF,
            $pinjaman->status
        );
    }

    public function test_jalur_5_pinjaman_lunas(): void
    {
        $anggota = Anggota::factory()->create();

        $pinjaman = Pinjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
            'tanggal_pinjaman' => '2025-01-10',
            'nominal_pinjaman' => 10000000,
        ]);

        $angsuran = Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'periode' => '2025-02-01',
            'nominal_angsuran' => 10000000,
        ]);

        $this->service->hitungSisaPinjaman(
            anggotaId: $anggota->id,
            jenisPinjaman: Pinjaman::JENIS_REGULER
        );

        $pinjaman->refresh();
        $angsuran->refresh();

        $this->assertEquals(
            0,
            $angsuran->sisa_pinjaman
        );

        $this->assertEquals(
            0,
            $pinjaman->sisa_pinjaman
        );

        $this->assertEquals(
            Pinjaman::STATUS_LUNAS,
            $pinjaman->status
        );
    }

    public function test_jalur_6_hitung_ulang_mulai_periode_tertentu(): void
    {
        $anggota = Anggota::factory()->create();

        $pinjamanJanuari = Pinjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
            'tanggal_pinjaman' => '2025-01-05',
            'nominal_pinjaman' => 12000000,
        ]);

        Angsuran::factory()->create([
            'pinjaman_id' => $pinjamanJanuari->id,
            'periode' => '2025-02-01',
            'nominal_angsuran' => 3000000,
        ]);

        $pinjamanMaret = Pinjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
            'tanggal_pinjaman' => '2025-03-10',
            'nominal_pinjaman' => 2000000,
        ]);

        $this->service->hitungSisaPinjaman(
            anggotaId: $anggota->id,
            jenisPinjaman: Pinjaman::JENIS_REGULER,
            mulaiPeriode: CarbonImmutable::parse('2025-01-01')
        );

        $pinjamanJanuari->refresh();
        $pinjamanMaret->refresh();

        $angsuran = Angsuran::first();

        $this->assertEquals(
            9000000,
            $angsuran->sisa_pinjaman
        );

        $this->assertEquals(
            9000000,
            $pinjamanJanuari->sisa_pinjaman
        );

        $this->assertEquals(
            2000000,
            $pinjamanMaret->sisa_pinjaman
        );

        $this->assertEquals(
            Pinjaman::STATUS_AKTIF,
            $pinjamanJanuari->status
        );

        $this->assertEquals(
            Pinjaman::STATUS_AKTIF,
            $pinjamanMaret->status
        );
    }
}