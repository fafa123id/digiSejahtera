<?php

namespace App\Http\Controllers;

use App\Services\KartuRekeningGridService;
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
}