<?php

namespace App\Http\Controllers;

use App\Services\RiwayatTransaksiService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RiwayatController extends Controller
{
    public function index(Request $request, RiwayatTransaksiService $riwayatService): Response
    {
        $validated = $request->validate([
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'anggota_id' => ['nullable', 'integer', 'exists:anggotas,id'],
            'jenis' => [
                'nullable',
                Rule::in([
                    'simpanan',
                    'pinjaman',
                    'angsuran',
                ]),
            ],
        ]);

        $tahun = (int) ($validated['tahun'] ?? now()->year);
        $bulan = isset($validated['bulan']) ? (int) $validated['bulan'] : null;
        $anggotaId = isset($validated['anggota_id']) ? (int) $validated['anggota_id'] : null;
        $jenis = $validated['jenis'] ?? null;

        return Inertia::render('Riwayat/Index', [
            'histories' => $riwayatService->buatData(
                tahun: $tahun,
                bulan: $bulan,
                anggotaId: $anggotaId,
                jenis: $jenis
            ),

            'members' => $riwayatService->ambilAnggota(),

            'filters' => [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'anggota_id' => $anggotaId,
                'jenis' => $jenis,
            ],

            'years' => collect(range(now()->year - 4, now()->year + 1))
                ->reverse()
                ->values(),
        ]);
    }
}