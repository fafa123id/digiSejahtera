<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Services\LaporanJasaPinjamanExcelExportService;
use App\Services\LaporanJasaPinjamanService;
use App\Services\LaporanShuExcelExportService;
use App\Services\LaporanShuService;
use App\Services\LaporanSimpananHariRayaExcelExportService;
use App\Services\LaporanTagihanBulananExcelExportService;
use App\Support\Toast;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function index(
        Request $request,
        LaporanJasaPinjamanService $jasaPinjamanService,
        LaporanShuService $shuService
    ): Response {
        $tahun = (int) $request->integer('tahun', now()->year);
        $jenis = (string) $request->input('jenis', 'jasa-pinjaman');

        if (!in_array($jenis, ['jasa-pinjaman', 'shu'], true)) {
            $jenis = 'jasa-pinjaman';
        }

        $report = match ($jenis) {
            'shu' => $this->buatLaporanShu($shuService, $tahun),
            default => $this->buatLaporanJasaPinjaman($jasaPinjamanService, $tahun),
        };

        return Inertia::render('Laporan/Index', [
            'report' => $report,

            'filters' => [
                'jenis' => $jenis,
                'tahun' => $tahun,
            ],

            'types' => [
                [
                    'value' => 'jasa-pinjaman',
                    'label' => 'Laporan Jasa Pinjaman Tahunan',
                ],
                [
                    'value' => 'shu',
                    'label' => 'Laporan SHU',
                ],
            ],

            'years' => collect(range(now()->year - 3, now()->year + 1))
                ->reverse()
                ->values(),
        ]);
    }

    public function exportJasaPinjaman(
        Request $request,
        LaporanJasaPinjamanExcelExportService $exportService
    ) {
        $tahun = $this->validasiTahun($request);
        if (!Anggota::exists()) {
            return back()->with(
                'toast',
                Toast::error(
                    'Data anggota tidak ditemukan.'
                )
            );
        }
        $path = $exportService->generate($tahun);

        return response()
            ->download($path, "laporan-jasa-pinjaman-{$tahun}.xlsx")
            ->deleteFileAfterSend(true);
    }

    public function exportShu(
        Request $request,
        LaporanShuExcelExportService $exportService
    ) {
        $tahun = $this->validasiTahun($request);
        if (!Anggota::exists()) {
            return back()->with(
                'toast',
                Toast::error(
                    'Data anggota tidak ditemukan.'
                )
            );
        }
        $path = $exportService->generate($tahun);

        return response()
            ->download($path, "laporan-shu-{$tahun}.xlsx")
            ->deleteFileAfterSend(true);
    }

    public function exportSimpananHariRaya(
        Request $request,
        LaporanSimpananHariRayaExcelExportService $exportService
    ) {
        $tahun = $this->validasiTahun($request);
        $bulan = $request->validate([
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
        ])['bulan'];
        if (!Anggota::exists()) {
            return back()->with(
                'toast',
                Toast::error(
                    'Data anggota tidak ditemukan.'
                )
            );
        }
        $path = $exportService->generate($tahun, $bulan);

        return response()
            ->download($path, "laporan-simpanan-hari-raya-{$tahun}-.xlsx")
            ->deleteFileAfterSend(true);
    }

    public function exportTagihanBulanan(
        Request $request,
        LaporanTagihanBulananExcelExportService $exportService
    ) {
        $tahun = $this->validasiTahun($request);
        if (!Anggota::exists()) {
            return back()->with(
                'toast',
                Toast::error(
                    'Data anggota tidak ditemukan.'
                )
            );
        }
        $path = $exportService->generate($tahun);

        return response()
            ->download($path, "laporan-tagihan-bulanan-{$tahun}.xlsx")
            ->deleteFileAfterSend(true);
    }

    private function buatLaporanJasaPinjaman(
        LaporanJasaPinjamanService $service,
        int $tahun
    ): array {
        $data = $service->buatData($tahun);

        return [
            'title' => 'Laporan Jasa Pinjaman Tahunan',
            'description' => 'Rekapitulasi jasa pinjaman reguler dan sebrak anggota selama satu tahun.',
            'export_url' => route('laporan.jasa-pinjaman.export', ['tahun' => $tahun]),
            'columns' => [
                ['key' => 'nomor_anggota', 'label' => 'No. Anggota', 'align' => 'center'],
                ['key' => 'nama', 'label' => 'Nama Anggota', 'align' => 'left'],
                ['key' => 'jasa_reguler', 'label' => 'Jasa Reguler', 'align' => 'right', 'money' => true],
                ['key' => 'jasa_sebrak', 'label' => 'Jasa Sebrak', 'align' => 'right', 'money' => true],
                ['key' => 'jumlah_jasa', 'label' => 'Jumlah Jasa', 'align' => 'right', 'money' => true],
            ],
            'rows' => $data['rows'],
            'totals' => $data['totals'],
        ];
    }

    private function buatLaporanShu(LaporanShuService $service, int $tahun): array
    {
        $data = $service->buatData($tahun);

        return [
            'title' => 'Laporan SHU Anggota',
            'description' => 'Rekapitulasi simpanan, jasa pinjaman, dan pembagian SHU setiap anggota.',
            'export_url' => route('laporan.shu.export', ['tahun' => $tahun]),
            'columns' => [
                ['key' => 'nomor_anggota', 'label' => 'No.', 'align' => 'center'],
                ['key' => 'nama', 'label' => 'Nama Anggota', 'align' => 'left'],
                ['key' => 'simpanan_pokok', 'label' => 'S. Pokok', 'align' => 'right', 'money' => true],
                ['key' => 'simpanan_wajib', 'label' => 'S. Wajib', 'align' => 'right', 'money' => true],
                ['key' => 'simpanan_sukarela', 'label' => 'SSR', 'align' => 'right', 'money' => true],
                ['key' => 'simpanan_hari_raya', 'label' => 'SHR', 'align' => 'right', 'money' => true],
                ['key' => 'simpanan_rekreasi', 'label' => 'S. Rekreasi', 'align' => 'right', 'money' => true],
                ['key' => 'jumlah_simpanan', 'label' => 'Jumlah Simpanan', 'align' => 'right', 'money' => true],
                ['key' => 'pinjaman_reguler', 'label' => 'P. Reguler', 'align' => 'right', 'money' => true],
                ['key' => 'pinjaman_sebrak', 'label' => 'P. Sebrak', 'align' => 'right', 'money' => true],
                ['key' => 'jumlah_pinjaman', 'label' => 'Jumlah Pinjaman', 'align' => 'right', 'money' => true],
                ['key' => 'shu_simpanan', 'label' => 'SHU Simpanan', 'align' => 'right', 'money' => true],
                ['key' => 'shu_pinjaman', 'label' => 'SHU Pinjaman', 'align' => 'right', 'money' => true],
                ['key' => 'jumlah_shu', 'label' => 'Jumlah SHU', 'align' => 'right', 'money' => true],
            ],
            'rows' => $data['rows'],
            'totals' => $data['totals'],
        ];
    }

    private function validasiTahun(Request $request): int
    {
        $validated = $request->validate([
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        return (int) $validated['tahun'];
    }
}
