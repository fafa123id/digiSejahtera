<?php

namespace App\Http\Controllers;

use App\Http\Requests\Simpanan\StoreSimpananRequest;
use App\Models\Anggota;
use App\Services\SimpananService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;

class SimpananController extends Controller
{
    public function store(
        StoreSimpananRequest $request,
        Anggota $anggota,
        SimpananService $simpananService
    ): RedirectResponse {
        $simpananService->simpan(
            anggota: $anggota,
            data: $request->validated(),
            userId: $request->user()->id,
        );

        return back()->with(
            'toast',
            Toast::success(
                'Data simpanan berhasil disimpan.'
            )
        );
    }
}