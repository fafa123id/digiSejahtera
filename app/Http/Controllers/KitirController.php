<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Services\KitirExcelExportService;
use App\Services\KitirService;
use App\Support\Toast;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KitirController extends Controller
{
    public function index(
        Request $request,
        KitirService $kitirService
    ): Response {
        $tahun =
            (int) $request->integer(
                'tahun',
                now()->year
            );

        $bulan =
            (int) $request->integer(
                'bulan',
                now()->month
            );

        if (
            $bulan < 1
            || $bulan > 12
        ) {
            $bulan =
                now()->month;
        }

        return Inertia::render(
            'Kitir/Index',
            [
                'kitirs' =>
                $kitirService
                    ->generateKitir(
                        tahun: $tahun,

                        bulan: $bulan,
                    ),

                'filters' => [
                    'tahun' =>
                    $tahun,

                    'bulan' =>
                    $bulan,
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

                'months' => [
                    [
                        'value' =>
                        1,

                        'label' =>
                        'Januari',
                    ],

                    [
                        'value' =>
                        2,

                        'label' =>
                        'Februari',
                    ],

                    [
                        'value' =>
                        3,

                        'label' =>
                        'Maret',
                    ],

                    [
                        'value' =>
                        4,

                        'label' =>
                        'April',
                    ],

                    [
                        'value' =>
                        5,

                        'label' =>
                        'Mei',
                    ],

                    [
                        'value' =>
                        6,

                        'label' =>
                        'Juni',
                    ],

                    [
                        'value' =>
                        7,

                        'label' =>
                        'Juli',
                    ],

                    [
                        'value' =>
                        8,

                        'label' =>
                        'Agustus',
                    ],

                    [
                        'value' =>
                        9,

                        'label' =>
                        'September',
                    ],

                    [
                        'value' =>
                        10,

                        'label' =>
                        'Oktober',
                    ],

                    [
                        'value' =>
                        11,

                        'label' =>
                        'November',
                    ],

                    [
                        'value' =>
                        12,

                        'label' =>
                        'Desember',
                    ],
                ],
            ]
        );
    }
    public function download(
        Request $request,
        KitirExcelExportService $exportService
    ) {
        $validated = $request->validate([
            'tahun' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],

            'bulan' => [
                'required',
                'integer',
                'min:1',
                'max:12',
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
            (int) $validated['tahun'];

        $bulan =
            (int) $validated['bulan'];

        $path =
            $exportService
            ->generate(
                tahun: $tahun,

                bulan: $bulan,
            );

        $bulanFile =
            str_pad(
                (string) $bulan,
                2,
                '0',
                STR_PAD_LEFT
            );

        return response()
            ->download(
                file: $path,

                name: "kitir-{$tahun}-{$bulanFile}.xlsx"
            )
            ->deleteFileAfterSend(
                true
            );
    }
}
