<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Services\KartuRekeningExcelExportService;
use App\Support\Toast;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KartuRekeningExportController extends Controller
{
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
