<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class IdulFitriService
{
    public function getTanggal(int $tahun): CarbonImmutable
    {
        $tanggal = Cache::remember(
            "idul-fitri:v2:{$tahun}",
            now()->addYear(),
            fn (): string => $this->ambilTanggalDariApi($tahun)->toDateString()
        );

        return CarbonImmutable::parse($tanggal)->startOfDay();
    }

    public function getBulan(int $tahun): int
    {
        return $this->getTanggal($tahun)->month;
    }

    public function getNamaBulan(int $tahun): string
    {
        return ucfirst($this->getTanggal($tahun)->locale('id')->translatedFormat('F'));
    }

    private function ambilTanggalDariApi(int $tahun): CarbonImmutable
    {
        $perkiraanTahunHijriah = (int) floor(($tahun - 622) * 33 / 32);

        foreach (range($perkiraanTahunHijriah - 2, $perkiraanTahunHijriah + 2) as $tahunHijriah) {
            $tanggal = $this->konversiKeGregorian($tahunHijriah);

            if ($tanggal && $tanggal->year === $tahun) {
                return $tanggal;
            }
        }

        throw new RuntimeException("Tanggal Idulfitri tahun {$tahun} tidak dapat ditemukan.");
    }

    private function konversiKeGregorian(int $tahunHijriah): ?CarbonImmutable
    {
        try {
            $response = Http::baseUrl(config('services.aladhan.url'))
                ->timeout(10)
                ->retry(2, 250)
                ->get("/hToG/01-10-{$tahunHijriah}");
        } catch (Throwable) {
            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $tanggal = $response->json('data.gregorian.date');

        if (!$tanggal) {
            return null;
        }

        try {
            $hasil = CarbonImmutable::createFromFormat('d-m-Y', $tanggal);

            return $hasil ? $hasil->startOfDay() : null;
        } catch (Throwable) {
            return null;
        }
    }
}