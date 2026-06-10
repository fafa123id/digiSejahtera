<?php

namespace App\Http\Controllers;

use App\Services\LaporanSimpananHariRayaExcelExportService;
use App\Services\LaporanTagihanBulananExcelExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function downloadTagihan(Request $request, LaporanTagihanBulananExcelExportService $exportService): BinaryFileResponse
    {
        $validated = $request->validate([
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $tahun = (int) $validated['tahun'];
        $path = $exportService->generate($tahun);

        return response()
            ->download($path, "laporan-tagihan-bulanan-{$tahun}.xlsx")
            ->deleteFileAfterSend(true);
    }
    public function downloadSHR(Request $request, LaporanSimpananHariRayaExcelExportService $exportService): BinaryFileResponse
    {
        $validated = $request->validate([
            'tahun' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],
        ]);

        $tahun = (int) $validated['tahun'];
        $path = $exportService->generate($tahun);

        return response()
            ->download($path, "laporan-simpanan-hari-raya-{$tahun}.xlsx")
            ->deleteFileAfterSend(true);
    }
}
