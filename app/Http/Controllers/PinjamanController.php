<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pinjaman\StorePinjamanRequest;
use App\Models\Anggota;
use App\Services\PinjamanService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;

class PinjamanController extends Controller
{
    public function store(
        StorePinjamanRequest $request,
        Anggota $anggota,
        PinjamanService $pinjamanService
    ): RedirectResponse {
        $pinjamanService->simpan(
            anggota: $anggota,
            data: $request->validated(),
            userId: $request->user()->id,
        );

        return back()->with(
            'toast',
            Toast::success(
                'Data pinjaman berhasil disimpan.'
            )
        );
    }
}