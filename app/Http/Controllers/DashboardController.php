<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Models\RekapSimpanan;
use App\Models\Simpanan;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $awalPeriode = now()
            ->startOfMonth()
            ->subMonths(11);

        return Inertia::render('Dashboard', [
            'summary' => $this->getSummary(),

            'monthlyTrend' => $this->getMonthlyTrend(
                $awalPeriode
            ),

            'loanComposition' =>
            $this->getLoanComposition(),

            'recentTransactions' =>
            $this->getRecentTransactions(),

            'generatedAt' => now()
                ->format('d M Y, H:i'),
        ]);
    }

    /**
     * Mengambil angka ringkasan yang ditampilkan
     * pada empat kartu dashboard.
     */
    private function getSummary(): array
    {
        $awalBulan = now()->startOfMonth();
        $akhirBulan = now()->endOfMonth();

        return [
            'active_members' => Anggota::query()
                ->where(
                    'status',
                    Anggota::STATUS_AKTIF
                )
                ->count(),

            'total_savings' => (float) RekapSimpanan::query()
                ->sum('total_simpanan'),

            'active_loans' => (float) Pinjaman::query()
                ->where(
                    'status',
                    Pinjaman::STATUS_AKTIF
                )
                ->sum('sisa_pinjaman'),

            'active_loan_members' => Pinjaman::query()
                ->where(
                    'status',
                    Pinjaman::STATUS_AKTIF
                )
                ->distinct('anggota_id')
                ->count('anggota_id'),

            'current_shu' => (float) \App\Models\ShuAnggota::query()
                ->where(
                    'tahun',
                    now()->year
                )
                ->sum('total_shu'),

            'current_period' => now()->year,

            'monthly_installments' => (float) Angsuran::query()
                ->whereBetween(
                    'periode',
                    [
                        $awalBulan,
                        $akhirBulan,
                    ]
                )
                ->sum('nominal_angsuran'),
        ];
    }

    /**
     * Mengambil tren transaksi selama 12 bulan.
     *
     * Grafik menampilkan:
     * - jumlah simpanan bersih pada bulan terkait;
     * - nominal pinjaman baru;
     * - pembayaran angsuran pokok.
     */
    private function getMonthlyTrend(
        \DateTimeInterface $awalPeriode
    ): array {
        $bulan = collect(
            range(0, 11)
        )->mapWithKeys(
            function (int $offset) use (
                $awalPeriode
            ): array {
                $periode = now()
                    ->parse($awalPeriode)
                    ->addMonths($offset);

                return [
                    $periode->format('Y-m') => [
                        'month' =>
                        $periode->translatedFormat(
                            'M Y'
                        ),

                        'savings' => 0,
                        'loans' => 0,
                        'installments' => 0,
                    ],
                ];
            }
        );

        $simpanan = Simpanan::query()
            ->where(
                'periode',
                '>=',
                $awalPeriode
            )
            ->selectRaw(
                "
                TO_CHAR(periode, 'YYYY-MM') AS bulan,
                COALESCE(
                    SUM(jumlah_simpanan),
                    0
                ) AS total
                "
            )
            ->groupByRaw(
                "TO_CHAR(periode, 'YYYY-MM')"
            )
            ->pluck(
                'total',
                'bulan'
            );

        $pinjaman = Pinjaman::query()
            ->where(
                'tanggal_pinjaman',
                '>=',
                $awalPeriode
            )
            ->selectRaw(
                "
                TO_CHAR(
                    tanggal_pinjaman,
                    'YYYY-MM'
                ) AS bulan,
                COALESCE(
                    SUM(nominal_pinjaman),
                    0
                ) AS total
                "
            )
            ->groupByRaw(
                "
                TO_CHAR(
                    tanggal_pinjaman,
                    'YYYY-MM'
                )
                "
            )
            ->pluck(
                'total',
                'bulan'
            );

        $angsuran = Angsuran::query()
            ->where(
                'periode',
                '>=',
                $awalPeriode
            )
            ->selectRaw(
                "
                TO_CHAR(periode, 'YYYY-MM') AS bulan,
                COALESCE(
                    SUM(nominal_angsuran),
                    0
                ) AS total
                "
            )
            ->groupByRaw(
                "TO_CHAR(periode, 'YYYY-MM')"
            )
            ->pluck(
                'total',
                'bulan'
            );

        return $bulan
            ->map(
                function (
                    array $item,
                    string $key
                ) use (
                    $simpanan,
                    $pinjaman,
                    $angsuran
                ): array {
                    return [
                        ...$item,

                        'savings' => (float) (
                            $simpanan[$key]
                            ?? 0
                        ),

                        'loans' => (float) (
                            $pinjaman[$key]
                            ?? 0
                        ),

                        'installments' => (float) (
                            $angsuran[$key]
                            ?? 0
                        ),
                    ];
                }
            )
            ->values()
            ->all();
    }

    /**
     * Mengambil komposisi sisa pinjaman aktif.
     */
    private function getLoanComposition(): array
    {
        $composition = Pinjaman::query()
            ->where(
                'status',
                Pinjaman::STATUS_AKTIF
            )
            ->selectRaw(
                "
                jenis_pinjaman,
                COALESCE(
                    SUM(sisa_pinjaman),
                    0
                ) AS total
                "
            )
            ->groupBy('jenis_pinjaman')
            ->pluck(
                'total',
                'jenis_pinjaman'
            );

        return [
            'reguler' => (float) (
                $composition[Pinjaman::JENIS_REGULER]
                ?? 0
            ),

            'sebrak' => (float) (
                $composition[Pinjaman::JENIS_SEBRAK]
                ?? 0
            ),
        ];
    }

    /**
     * Menggabungkan aktivitas simpanan, pinjaman,
     * dan angsuran terbaru ke dalam satu daftar.
     */
    private function getRecentTransactions(): array
    {
        $simpanans = Simpanan::query()
            ->with([
                'anggota:id,nomor_anggota,nama',
            ])
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(
                function (
                    Simpanan $simpanan
                ): array {
                    $nominal = (float)
                    $simpanan
                        ->jumlah_simpanan;

                    return [
                        'id' =>
                        'simpanan-' . $simpanan->id,

                        'type' => $nominal < 0
                            ? 'withdrawal'
                            : 'saving',

                        'title' => $nominal < 0
                            ? 'Penarikan simpanan'
                            : 'Simpanan anggota',

                        'member_name' =>
                        $simpanan
                            ->anggota
                            ->nama,

                        'member_number' =>
                        $simpanan
                            ->anggota
                            ->nomor_anggota,

                        'amount' =>
                        abs($nominal),

                        'date' =>
                        $simpanan
                            ->periode
                            ->format('Y-m-d'),

                        'sort_at' =>
                        $simpanan
                            ->updated_at
                            ->toISOString(),
                    ];
                }
            );

        $pinjamans = Pinjaman::query()
            ->with([
                'anggota:id,nomor_anggota,nama',
            ])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(
                function (
                    Pinjaman $pinjaman
                ): array {
                    return [
                        'id' =>
                        'pinjaman-' . $pinjaman->id,

                        'type' => 'loan',

                        'title' =>
                        'Pinjaman '
                            . ucfirst(
                                $pinjaman
                                    ->jenis_pinjaman
                            ),

                        'member_name' =>
                        $pinjaman
                            ->anggota
                            ->nama,

                        'member_number' =>
                        $pinjaman
                            ->anggota
                            ->nomor_anggota,

                        'amount' => (float)
                        $pinjaman
                            ->nominal_pinjaman,

                        'date' =>
                        $pinjaman
                            ->tanggal_pinjaman
                            ->format('Y-m-d'),

                        'sort_at' =>
                        $pinjaman
                            ->created_at
                            ->toISOString(),
                    ];
                }
            );

        $angsurans = Angsuran::query()
            ->with([
                'pinjaman.anggota:id,nomor_anggota,nama',
            ])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(
                function (
                    Angsuran $angsuran
                ): array {
                    return [
                        'id' =>
                        'angsuran-' . $angsuran->id,

                        'type' => 'installment',

                        'title' =>
                        'Pembayaran angsuran',

                        'member_name' =>
                        $angsuran
                            ->pinjaman
                            ->anggota
                            ->nama,

                        'member_number' =>
                        $angsuran
                            ->pinjaman
                            ->anggota
                            ->nomor_anggota,

                        'amount' => (float)
                        $angsuran
                            ->jumlah_tagihan,

                        'date' =>
                        $angsuran
                            ->tanggal_pembayaran
                            ->format('Y-m-d'),

                        'sort_at' =>
                        $angsuran
                            ->created_at
                            ->toISOString(),
                    ];
                }
            );

        return collect()
            ->concat($simpanans)
            ->concat($pinjamans)
            ->concat($angsurans)
            ->sortByDesc('sort_at')
            ->take(8)
            ->values()
            ->all();
    }
}
