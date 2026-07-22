<?php

namespace Tests\Integration;

use App\Models\Anggota;
use App\Models\Pinjaman;
use App\Models\User;
use App\Services\KartuRekeningTransactionService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SubmitPinjamanTest extends TestCase
{
    use RefreshDatabase;

    private KartuRekeningTransactionService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user);

        $this->service = app(KartuRekeningTransactionService::class);
    }

    public function test_jalur_1_tidak_ada_perubahan_data(): void
    {
        $anggota = Anggota::factory()->create();

        $hasil = $this->service->submitPinjaman(
            anggota: $anggota,
            jenis: Pinjaman::JENIS_REGULER,
            periode: CarbonImmutable::create(2025, 1, 1),
            action: 'create_pinjaman',
            rawValue: '0',
            pinjamanId: null,
            userId: $this->user->id,
            hitungUlang: true
        );

        $this->assertFalse($hasil);

        $this->assertDatabaseCount('pinjamans', 0);
    }

    public function test_jalur_2_create_dan_hitung_ulang(): void
    {
        $anggota = Anggota::factory()->create();

        $hasil = $this->service->submitPinjaman(
            anggota: $anggota,
            jenis: Pinjaman::JENIS_REGULER,
            periode: CarbonImmutable::create(2025, 1, 1),
            action: 'create_pinjaman',
            rawValue: '10000000',
            pinjamanId: null,
            userId: $this->user->id,
            hitungUlang: true
        );

        $this->assertTrue($hasil);

        $this->assertDatabaseHas('pinjamans', [
            'anggota_id' => $anggota->id,
            'nominal_pinjaman' => 10000000,
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
        ]);
    }

    public function test_jalur_3_update_dan_hitung_ulang(): void
    {
        $anggota = Anggota::factory()->create();

        $pinjaman = Pinjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'nominal_pinjaman' => 10000000,
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
            'tanggal_pinjaman' => CarbonImmutable::create(2025, 1, 1),
        ]);

        $hasil = $this->service->submitPinjaman(
            anggota: $anggota,
            jenis: Pinjaman::JENIS_REGULER,
            periode: CarbonImmutable::create(2025, 1, 1),
            action: 'update_pinjaman',
            rawValue: '12000000',
            pinjamanId: $pinjaman->id,
            userId: $this->user->id,
            hitungUlang: true
        );

        $this->assertTrue($hasil);

        $this->assertDatabaseHas('pinjamans', [
            'id' => $pinjaman->id,
            'nominal_pinjaman' => 12000000,
        ]);
    }

    public function test_jalur_4_action_tidak_valid(): void
    {
        $anggota = Anggota::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->submitPinjaman(
            anggota: $anggota,
            jenis: Pinjaman::JENIS_REGULER,
            periode: CarbonImmutable::create(2025, 1, 1),
            action: 'delete_pinjaman',
            rawValue: '10000000',
            pinjamanId: 1,
            userId: $this->user->id,
            hitungUlang: true
        );
    }
}