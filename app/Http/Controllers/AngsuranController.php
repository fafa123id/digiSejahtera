<?php

namespace App\Http\Controllers;

use App\Http\Requests\Angsuran\StoreAngsuranRequest;
use App\Models\Pinjaman;
use App\Services\AngsuranService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;

class AngsuranController extends Controller
{
    public function store(
        StoreAngsuranRequest $request,
        Pinjaman $pinjaman,
        AngsuranService $angsuranService
    ): RedirectResponse {
        $angsuranService->simpan(
            pinjaman: $pinjaman,
            data: $request->validated(),
            userId: $request->user()->id,
        );

        return back()->with(
            'toast',
            Toast::success(
                'Data angsuran berhasil disimpan.'
            )
        );
    }
}