<?php

namespace App\Http\Controllers;

use App\Http\Requests\Anggota\StoreAnggotaRequest;
use App\Http\Requests\Anggota\UpdateAnggotaRequest;
use App\Models\Anggota;
use App\Services\AnggotaService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;

class AnggotaController extends Controller
{
    public function store(
        StoreAnggotaRequest $request,
        AnggotaService $anggotaService
    ): RedirectResponse {
        $anggota = $anggotaService->tambah(
            $request->validated()
        );

        return redirect()
            ->route(
                'kartu-rekening.index',
                [
                    'anggota' => $anggota->id,
                ]
            )
            ->with(
                'toast',
                Toast::success(
                    'Data anggota berhasil disimpan.'
                )
            );
    }

    public function update(
        UpdateAnggotaRequest $request,
        Anggota $anggota,
        AnggotaService $anggotaService
    ): RedirectResponse {
        $anggotaService->ubahNama(
            anggota: $anggota,
            data: $request->validated(),
        );

        return back()->with(
            'toast',
            Toast::success(
                'Data anggota berhasil diubah.'
            )
        );
    }
    public function ubahAgama(
        UpdateAnggotaRequest $request,
        Anggota $anggota,
        AnggotaService $anggotaService
    ): RedirectResponse {
        $anggotaService->ubahAgama(
            anggota: $anggota,
            data: $request->validated(),
        );

        return back()->with(
            'toast',
            Toast::success(
                'Data agama anggota berhasil diubah.'
            )
        );
    }

    public function keluarkan(
        Anggota $anggota,
        AnggotaService $anggotaService
    ): RedirectResponse {
        $anggotaService->keluarkan(
            $anggota
        );

        return back()->with(
            'toast',
            Toast::success(
                'Anggota berhasil dikeluarkan.'
            )
        );
    }

    public function destroy(
        Anggota $anggota,
        AnggotaService $anggotaService
    ): RedirectResponse {
        $anggotaService->hapus(
            $anggota
        );

        return redirect()
            ->route('kartu-rekening.index')
            ->with(
                'toast',
                Toast::success(
                    'Data anggota berhasil dihapus.'
                )
            );
    }
}