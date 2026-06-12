<?php

namespace App\Http\Controllers;

use App\Http\Requests\KartuRekening\SaveKartuRekeningRequest;
use App\Models\Anggota;
use App\Services\KartuRekeningExcelExportService;
use App\Services\KartuRekeningGridService;
use App\Services\KartuRekeningInlineService;
use App\Support\Toast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KartuRekeningController extends Controller
{
    public function index(
        Request $request,
        KartuRekeningGridService $gridService
    ): Response {
        $tahun =
            (int) $request->integer(
                'tahun',
                now()->year
            );

        return Inertia::render(
            'KartuRekening/Index',
            [
                'members' =>
                $gridService
                    ->buatSemuaData(
                        $tahun
                    ),

                'filters' => [
                    'tahun' =>
                    $tahun,
                ],

                'years' =>
                collect(
                    range(
                        now()->year - 3,
                        now()->year + 1
                    )
                )
                    ->reverse()
                    ->values(),
            ]
        );
    }
    public function update(
        SaveKartuRekeningRequest $request,
        KartuRekeningInlineService $inlineService
    ): RedirectResponse {
        $inlineService->simpan(
            changes: $request->validated(
                'changes'
            )
        );

        return back()->with(
            'toast',
            Toast::success(
                'Perubahan kartu rekening berhasil disimpan.'
            )
        );
    }
    public function download(
        Request $request,
        KartuRekeningExcelExportService $exportService
    ) {
        $validated = $request->validate([
            'tahun' => [
                'nullable',
                'integer',
                'min:2000',
                'max:2100',
            ],
        ]);
        if (!Anggota::exists()) {
            return back()->with(
                'toast',
                Toast::error(
                    'Data anggota tidak ditemukan.'
                )
            );
        }

        $tahun =
            (int) (
                $validated['tahun']
                ?? now()->year
            );

        $path =
            $exportService
            ->generate(
                $tahun
            );

        return response()
            ->download(
                file: $path,

                name: "kartu-rekening-{$tahun}.xlsx"
            )
            ->deleteFileAfterSend(
                true
            );
    }
}
