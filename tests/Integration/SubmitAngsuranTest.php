<?php

namespace Tests\Integration;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Models\User;
use App\Services\KartuRekeningTransactionService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SubmitAngsuranTest extends TestCase
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

        $hasil = $this->service->submitAngsuran(
            anggota: $anggota,
            jenis: Pinjaman::JENIS_REGULER,
            periode: CarbonImmutable::create(2025, 2, 1),
            action: 'create_angsuran',
            rawValue: '0',
            angsuranId: null,
            userId: $this->user->id,
            hitungUlang: true
        );

        $this->assertFalse($hasil);

        $this->assertDatabaseCount('angsurans', 0);
    }

    public function test_jalur_2_create_dan_hitung_ulang(): void
    {
        $anggota = Anggota::factory()->create();

        $pinjaman = Pinjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
            'nominal_pinjaman' => 10000000,
            'tanggal_pinjaman' => CarbonImmutable::create(2025, 1, 1),
        ]);

        $hasil = $this->service->submitAngsuran(
            anggota: $anggota,
            jenis: Pinjaman::JENIS_REGULER,
            periode: CarbonImmutable::create(2025, 2, 1),
            action: 'create_angsuran',
            rawValue: '3000000',
            angsuranId: null,
            userId: $this->user->id,
            hitungUlang: true
        );

        $this->assertTrue($hasil);

        $this->assertDatabaseHas('angsurans', [
            'pinjaman_id' => $pinjaman->id,
            'nominal_angsuran' => 3000000,
        ]);
    }

    public function test_jalur_3_update_dan_hitung_ulang(): void
    {
        $anggota = Anggota::factory()->create();

        $pinjaman = Pinjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
            'nominal_pinjaman' => 10000000,
            'tanggal_pinjaman' => CarbonImmutable::create(2025, 1, 1),
        ]);

        $angsuran = Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'periode' => CarbonImmutable::create(2025, 2, 1),
            'nominal_angsuran' => 3000000,
        ]);

        $hasil = $this->service->submitAngsuran(
            anggota: $anggota,
            jenis: Pinjaman::JENIS_REGULER,
            periode: CarbonImmutable::create(2025, 2, 1),
            action: 'update_angsuran',
            rawValue: '4000000',
            angsuranId: $angsuran->id,
            userId: $this->user->id,
            hitungUlang: true
        );

        $this->assertTrue($hasil);

        $this->assertDatabaseHas('angsurans', [
            'id' => $angsuran->id,
            'nominal_angsuran' => 4000000,
        ]);
    }

    public function test_jalur_4_action_tidak_valid(): void
    {
        $anggota = Anggota::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->submitAngsuran(
            anggota: $anggota,
            jenis: Pinjaman::JENIS_REGULER,
            periode: CarbonImmutable::create(2025, 2, 1),
            action: 'delete_angsuran',
            rawValue: '3000000',
            angsuranId: 1,
            userId: $this->user->id,
            hitungUlang: true
        );
    }
}