<?php

namespace Tests\Unit\Services;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use App\Services\KitirService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateKitirTest extends TestCase
{
    use RefreshDatabase;

    public function test_jalur_1_tidak_ada_anggota_aktif(): void
    {
        $service = new KitirService();

        $hasil = $service->generateKitir(2026, 6);

        $this->assertIsArray($hasil);
        $this->assertCount(0, $hasil);
    }

    public function test_jalur_2_generate_kitir_berhasil(): void
    {
        $anggota = Anggota::factory()->create([
            'nomor_anggota' => '1001',
            'nama' => 'Ahmad Fauzan',
            'status' => 'aktif',
            'tanggal_masuk' => '2025-01-01',
            'tanggal_keluar' => null,
        ]);

        Simpanan::create([
            'anggota_id' => $anggota->id,
            'periode' => '2026-06-01',
            'simpanan_wajib' => 100000,
            'simpanan_sukarela' => 50000,
            'simpanan_hari_raya' => 200000,
            'simpanan_rekreasi' => 30000,
            'jumlah_simpanan' => 380000,
        ]);

        $pinjamanReguler = Pinjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'jenis_pinjaman' => Pinjaman::JENIS_REGULER,
            'tanggal_pinjaman' => '2026-01-10',
            'nominal_pinjaman' => 1000000,
            'persentase_jasa' => 1.50,
            'sisa_pinjaman' => 800000,
            'status' => Pinjaman::STATUS_AKTIF,
        ]);

        Angsuran::factory()->create([
            'pinjaman_id' => $pinjamanReguler->id,
            'periode' => '2026-06-01',
            'tanggal_pembayaran' => '2026-06-05',
            'angsuran_ke' => 1,
            'saldo_awal' => 1000000,
            'nominal_angsuran' => 200000,
            'persentase_jasa' => 1.50,
            'jasa_pinjaman' => 15000,
            'sisa_pinjaman' => 800000,
            'jumlah_tagihan' => 215000,
        ]);

        $pinjamanSebrak = Pinjaman::factory()->create([
            'anggota_id' => $anggota->id,
            'jenis_pinjaman' => Pinjaman::JENIS_SEBRAK,
            'tanggal_pinjaman' => '2026-03-10',
            'nominal_pinjaman' => 500000,
            'persentase_jasa' => 2.00,
            'sisa_pinjaman' => 500000,
            'status' => Pinjaman::STATUS_AKTIF,
        ]);

        Angsuran::factory()->create([
            'pinjaman_id' => $pinjamanSebrak->id,
            'periode' => '2026-06-01',
            'tanggal_pembayaran' => '2026-06-05',
            'angsuran_ke' => 1,
            'saldo_awal' => 500000,
            'nominal_angsuran' => 0,
            'persentase_jasa' => 2.00,
            'jasa_pinjaman' => 10000,
            'sisa_pinjaman' => 500000,
            'jumlah_tagihan' => 10000,
        ]);

        $service = new KitirService();

        $hasil = $service->generateKitir(2026, 6);

        $this->assertCount(1, $hasil);

        $this->assertEquals(100000, $hasil[0]['simpanan_wajib']);
        $this->assertEquals(50000, $hasil[0]['simpanan_sukarela']);
        $this->assertEquals(200000, $hasil[0]['simpanan_hari_raya']);
        $this->assertEquals(30000, $hasil[0]['simpanan_rekreasi']);

        $this->assertEquals(200000, $hasil[0]['reguler']['nominal_angsuran']);
        $this->assertEquals(15000, $hasil[0]['reguler']['jasa_pinjaman']);

        $this->assertEquals(0, $hasil[0]['sebrak']['nominal_angsuran']);
        $this->assertEquals(10000, $hasil[0]['sebrak']['jasa_pinjaman']);

        $this->assertEquals(605000, $hasil[0]['jumlah']);
        $this->assertEquals(1300000, $hasil[0]['sisa_pinjaman']);
    }
}