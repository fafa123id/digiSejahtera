<?php

namespace Tests\Unit\Services;

use App\Models\Anggota;
use App\Models\Simpanan;
use App\Models\User;
use App\Services\KartuRekeningTransactionService;
use App\Services\PinjamanCalculationService;
use App\Services\RekapSimpananService;
use App\Services\ShuService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class SubmitSimpananTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;
    protected RekapSimpananService $rekapService;
    protected ShuService $shuService;
    protected PinjamanCalculationService $pinjamanService;
    protected KartuRekeningTransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rekapService = Mockery::mock(RekapSimpananService::class);
        $this->shuService = Mockery::mock(ShuService::class);
        $this->pinjamanService = Mockery::mock(PinjamanCalculationService::class);
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->service = new KartuRekeningTransactionService(
            $this->rekapService,
            $this->pinjamanService,
            $this->shuService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_jalur_1_tanpa_perubahan_dan_tanpa_hitung_ulang(): void
    {
        $anggota = Anggota::factory()->create();

        $this->rekapService->shouldNotReceive('hitungTotalSimpanan');
        $this->shuService->shouldNotReceive('hitungSHUAnggota');

        $hasil = $this->service->submitSimpanan(
            $anggota,
            CarbonImmutable::create(2026, 6, 1),
            collect(),
            $this->user->id,
            false
        );

        $this->assertTrue($hasil);

        $this->assertDatabaseMissing('simpanans', [
            'anggota_id' => $anggota->id,
        ]);
    }

    public function test_jalur_2_simpan_data_dan_hitung_ulang(): void
    {
        $anggota = Anggota::factory()->create();

        $this->rekapService
            ->shouldReceive('hitungTotalSimpanan')
            ->once();

        $this->shuService
            ->shouldReceive('hitungSHUAnggota')
            ->once();

        $hasil = $this->service->submitSimpanan(
            $anggota,
            CarbonImmutable::create(2026, 6, 1),
            collect([
                [
                    'field' => 'simpanan_wajib',
                    'value' => 100000,
                ],
                [
                    'field' => 'simpanan_sukarela',
                    'value' => 50000,
                ],
            ]),
            $this->user->id,
            true
        );

        $this->assertTrue($hasil);

        $this->assertDatabaseHas('simpanans', [
            'anggota_id' => $anggota->id,
            'simpanan_wajib' => 100000,
            'simpanan_sukarela' => 50000,
            'jumlah_simpanan' => 150000,
        ]);
    }

    public function test_jalur_3_hapus_data_dan_hitung_ulang(): void
    {
        $anggota = Anggota::factory()->create();

        Simpanan::create([
            'anggota_id' => $anggota->id,
            'periode' => '2026-06-01',
            'simpanan_wajib' => 100000,
            'simpanan_sukarela' => 50000,
            'jumlah_simpanan' => 150000,
        ]);

        $this->rekapService
            ->shouldReceive('hitungTotalSimpanan')
            ->once();

        $this->shuService
            ->shouldReceive('hitungSHUAnggota')
            ->once();

        $hasil = $this->service->submitSimpanan(
            $anggota,
            CarbonImmutable::create(2026, 6, 1),
            collect([
                [
                    'field' => 'simpanan_wajib',
                    'value' => 0,
                ],
                [
                    'field' => 'simpanan_sukarela',
                    'value' => 0,
                ],
            ]),
            $this->user->id,
            true
        );

        $this->assertTrue($hasil);

        $this->assertDatabaseCount('simpanans', 0);
    }

    public function test_jalur_4_data_baru_nol_tetapi_hitung_ulang(): void
    {
        $anggota = Anggota::factory()->create();

        $this->rekapService
            ->shouldReceive('hitungTotalSimpanan')
            ->once();

        $this->shuService
            ->shouldReceive('hitungSHUAnggota')
            ->once();

        $hasil = $this->service->submitSimpanan(
            $anggota,
            CarbonImmutable::create(2026, 6, 1),
            collect([
                [
                    'field' => 'simpanan_wajib',
                    'value' => 0,
                ],
            ]),
            $this->user->id,
            true
        );

        $this->assertTrue($hasil);

        $this->assertDatabaseMissing('simpanans', [
            'anggota_id' => $anggota->id,
        ]);
    }

    public function test_jalur_5_hapus_data_tanpa_hitung_ulang(): void
    {
        $anggota = Anggota::factory()->create();

        Simpanan::create([
            'anggota_id' => $anggota->id,
            'periode' => '2026-06-01',
            'simpanan_wajib' => 100000,
            'jumlah_simpanan' => 100000,
        ]);

        $this->rekapService->shouldNotReceive('hitungTotalSimpanan');
        $this->shuService->shouldNotReceive('hitungSHUAnggota');

        $hasil = $this->service->submitSimpanan(
            $anggota,
            CarbonImmutable::create(2026, 6, 1),
            collect([
                [
                    'field' => 'simpanan_wajib',
                    'value' => 0,
                ],
            ]),
            $this->user->id,
            false
        );

        $this->assertTrue($hasil);

        $this->assertDatabaseCount('simpanans', 0);
    }

    public function test_jalur_6_simpan_data_tanpa_hitung_ulang(): void
    {
        $anggota = Anggota::factory()->create();

        $this->rekapService->shouldNotReceive('hitungTotalSimpanan');
        $this->shuService->shouldNotReceive('hitungSHUAnggota');

        $hasil = $this->service->submitSimpanan(
            $anggota,
            CarbonImmutable::create(2026, 6, 1),
            collect([
                [
                    'field' => 'simpanan_hari_raya',
                    'value' => 200000,
                ],
                [
                    'field' => 'simpanan_rekreasi',
                    'value' => 30000,
                ],
            ]),
            $this->user->id,
            false
        );

        $this->assertTrue($hasil);

        $this->assertDatabaseHas('simpanans', [
            'anggota_id' => $anggota->id,
            'jumlah_simpanan' => 230000,
        ]);
    }
}
