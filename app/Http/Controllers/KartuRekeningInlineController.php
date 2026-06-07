<?php

namespace App\Http\Controllers;

use App\Http\Requests\KartuRekening\SaveKartuRekeningRequest;
use App\Services\KartuRekeningInlineService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;

class KartuRekeningInlineController extends Controller
{
    public function update(
        SaveKartuRekeningRequest $request,
        KartuRekeningInlineService $inlineService
    ): RedirectResponse {
        $inlineService->simpan(
            changes:
                $request->validated(
                    'changes'
                ),

            userId:
                $request->user()->id,
        );

        return back()->with(
            'toast',
            Toast::success(
                'Perubahan kartu rekening berhasil disimpan.'
            )
        );
    }
}