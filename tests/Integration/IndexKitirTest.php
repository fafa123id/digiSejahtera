<?php

namespace Tests\Integration;

use App\Models\User;
use App\Services\KitirService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class IndexKitirTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($user);
    }
    public function test_jalur_1_bulan_valid(): void
    {
        $service = Mockery::mock(KitirService::class);

        $service->shouldReceive('generateKitir')
            ->once()
            ->with(2026, 6)
            ->andReturn([]);

        $this->app->instance(KitirService::class, $service);

        $response = $this->get('/kitir?tahun=2026&bulan=6');

        $response->assertStatus(200);

        $response->assertInertia(
            fn($page) => $page
                ->component('Kitir/Index')
                ->where('filters.tahun', 2026)
                ->where('filters.bulan', 6)
                ->has('years')
                ->has('months')
        );
    }

    public function test_jalur_2_bulan_tidak_valid(): void
    {
        $bulanSekarang = now()->month;

        $service = Mockery::mock(KitirService::class);

        $service->shouldReceive('generateKitir')
            ->once()
            ->with(2026, $bulanSekarang)
            ->andReturn([]);

        $this->app->instance(KitirService::class, $service);

        $response = $this->get('/kitir?tahun=2026&bulan=13');

        $response->assertStatus(200);

        $response->assertInertia(
            fn($page) => $page
                ->component('Kitir/Index')
                ->where('filters.tahun', 2026)
                ->where('filters.bulan', $bulanSekarang)
                ->has('years')
                ->has('months')
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
