<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

class KartuRekeningExcelExportService
{
    private const BLOCK_HEIGHT =
    17;

    private const TEMPLATE_BLOCK_START =
    1;

    private const FIRST_DATA_ROW_OFFSET =
    2;

    private const TOTAL_ROW_OFFSET =
    15;

    private const LAST_COLUMN =
    'R';

    /**
     * Menghasilkan file Excel kartu rekening seluruh anggota.
     */
    public function generate(
        int $tahun
    ): string {
        $templatePath =
            storage_path(
                'app/templates/template.xlsx'
            );

        if (!is_file($templatePath)) {
            throw new RuntimeException(
                'Template kartu rekening tidak ditemukan pada storage/app/templates/kartu-rekening-template.xlsx.'
            );
        }

        $anggota =
            $this->ambilSeluruhAnggota(
                $tahun
            );

        $spreadsheet =
            IOFactory::load(
                $templatePath
            );

        $sheet =
            $spreadsheet
            ->getActiveSheet();

        $sheet->setTitle(
            'Kartu Rekening'
        );

        $this->siapkanWorksheet(
            sheet: $sheet,

            jumlahAnggota: $anggota->count(),
        );

        if ($anggota->isEmpty()) {
            $sheet->setCellValue(
                'A1',
                'Data anggota belum tersedia.'
            );

            return $this->simpanFile(
                spreadsheet: $spreadsheet,

                tahun: $tahun,
            );
        }

        foreach (
            $anggota->values()
            as $index => $item
        ) {
            $startRow =
                self::TEMPLATE_BLOCK_START
                + (
                    $index
                    * self::BLOCK_HEIGHT
                );

            $data =
                $this->buatDataCetakAnggota(
                    anggota: $item,

                    tahun: $tahun,
                );

            $this->tulisBlokAnggota(
                sheet: $sheet,

                startRow: $startRow,

                data: $data,
            );
        }

        $lastRow =
            self::TEMPLATE_BLOCK_START
            + (
                $anggota->count()
                * self::BLOCK_HEIGHT
            )
            - 1;

        $this->aturHalamanCetak(
            sheet: $sheet,

            lastRow: $lastRow,
        );

        return $this->simpanFile(
            spreadsheet: $spreadsheet,

            tahun: $tahun,
        );
    }

    /**
     * Mengambil seluruh anggota yang belum dihapus permanen.
     *
     * Anggota nonaktif tetap dicetak agar histori kartu rekening
     * tidak hilang.
     */
    private function ambilSeluruhAnggota(
        int $tahun
    ): Collection {
        $akhirTahun =
            CarbonImmutable::create(
                year: $tahun,

                month: 12,

                day: 31,
            );

        return Anggota::query()
            ->with([
                'simpanans' =>
                fn($query) =>
                $query
                    ->whereDate(
                        'periode',
                        '<=',
                        $akhirTahun
                            ->toDateString()
                    )
                    ->orderBy(
                        'periode'
                    ),

                'pinjamans' =>
                fn($query) =>
                $query
                    ->whereDate(
                        'tanggal_pinjaman',
                        '<=',
                        $akhirTahun
                            ->toDateString()
                    )
                    ->with([
                        'angsurans' =>
                        fn($query) =>
                        $query
                            ->whereDate(
                                'periode',
                                '<=',
                                $akhirTahun
                                    ->toDateString()
                            )
                            ->orderBy(
                                'periode'
                            )
                            ->orderBy(
                                'id'
                            ),
                    ])
                    ->orderBy(
                        'tanggal_pinjaman'
                    )
                    ->orderBy(
                        'id'
                    ),
            ])
            ->orderBy(
                'nomor_anggota'
            )
            ->get();
    }

    /**
     * Menyiapkan jumlah blok berdasarkan jumlah anggota.
     *
     * Template hanya memakai blok anggota pertama sebagai sumber
     * formatting. Seluruh blok lain dibuat ulang secara dinamis.
     */
    private function siapkanWorksheet(
        Worksheet $sheet,
        int $jumlahAnggota
    ): void {
        $highestRow =
            $sheet->getHighestRow();

        /*
     * Sisakan satu blok pertama dari template sebagai pola.
     */
        if (
            $highestRow
            > self::BLOCK_HEIGHT
        ) {
            $sheet->removeRow(
                self::BLOCK_HEIGHT
                    + 1,
                $highestRow
                    - self::BLOCK_HEIGHT
            );
        }

        /*
     * Pastikan blok pertama juga memakai struktur merge final,
     * termasuk JUMLAH SIMPANAN yang merge vertikal 2 baris.
     */
        $this->terapkanMergeBlok(
            sheet: $sheet,
            startRow: self::TEMPLATE_BLOCK_START,
        );

        $this->hapusIsiBlok(
            sheet: $sheet,
            startRow: self::TEMPLATE_BLOCK_START,
        );

        if ($jumlahAnggota <= 1) {
            return;
        }

        for (
            $index = 1;
            $index < $jumlahAnggota;
            $index++
        ) {
            $targetStart =
                self::TEMPLATE_BLOCK_START
                + (
                    $index
                    * self::BLOCK_HEIGHT
                );

            $sheet->insertNewRowBefore(
                $targetStart,
                self::BLOCK_HEIGHT
            );

            $this->salinFormatBlok(
                sheet: $sheet,
                sourceStart: self::TEMPLATE_BLOCK_START,
                targetStart: $targetStart,
            );

            $this->terapkanMergeBlok(
                sheet: $sheet,
                startRow: $targetStart,
            );
        }
    }

    /**
     * Menghapus nilai dan formula lama, tetapi mempertahankan style.
     */
    private function hapusIsiBlok(
        Worksheet $sheet,
        int $startRow
    ): void {
        $endRow =
            $startRow
            + self::BLOCK_HEIGHT
            - 1;

        foreach (
            range(
                $startRow,
                $endRow
            )
            as $row
        ) {
            foreach (
                range(
                    'A',
                    self::LAST_COLUMN
                )
                as $column
            ) {
                $sheet
                    ->getCell(
                        "{$column}{$row}"
                    )
                    ->setValue(
                        null
                    );
            }
        }
    }

    /**
     * Menyalin style blok template pertama ke blok anggota berikutnya.
     */
    private function salinFormatBlok(
        Worksheet $sheet,
        int $sourceStart,
        int $targetStart
    ): void {
        for (
            $offset = 0;
            $offset < self::BLOCK_HEIGHT;
            $offset++
        ) {
            $sourceRow =
                $sourceStart
                + $offset;

            $targetRow =
                $targetStart
                + $offset;

            $sourceHeight =
                $sheet
                ->getRowDimension(
                    $sourceRow
                )
                ->getRowHeight();

            $sheet
                ->getRowDimension(
                    $targetRow
                )
                ->setRowHeight(
                    $sourceHeight
                );

            foreach (
                range(
                    'A',
                    self::LAST_COLUMN
                )
                as $column
            ) {
                $sheet->duplicateStyle(
                    $sheet->getStyle(
                        "{$column}{$sourceRow}"
                    ),

                    "{$column}{$targetRow}"
                );
            }
        }
    }

    /**
     * Merge cell mengikuti struktur template asli.
     */
    private function terapkanMergeBlok(
        Worksheet $sheet,
        int $startRow
    ): void {
        $totalRow =
            $startRow
            + self::TOTAL_ROW_OFFSET;

        /*
     * Bersihkan merge lama pada area blok ini agar aman.
     */
        $ranges = [
            "B{$startRow}:F{$startRow}",
            "G{$startRow}:G" . ($startRow + 1),
            "H{$startRow}:L{$startRow}",
            "M{$startRow}:Q{$startRow}",
            "R{$startRow}:R" . ($startRow + 1),
            "I{$totalRow}:J{$totalRow}",
            "M{$totalRow}:O{$totalRow}",
        ];

        foreach ($ranges as $range) {
            try {
                $sheet->unmergeCells($range);
            } catch (\Throwable $e) {
                // abaikan jika range belum merge
            }
        }

        $sheet->mergeCells(
            "B{$startRow}:F{$startRow}"
        );

        /*
     * JUMLAH SIMPANAN merge vertikal 2 baris
     * seperti JUMLAH TAGIHAN.
     */
        $sheet->mergeCells(
            "G{$startRow}:G"
                . (
                    $startRow
                    + 1
                )
        );

        $sheet->mergeCells(
            "H{$startRow}:L{$startRow}"
        );

        $sheet->mergeCells(
            "M{$startRow}:Q{$startRow}"
        );

        $sheet->mergeCells(
            "R{$startRow}:R"
                . (
                    $startRow
                    + 1
                )
        );

        $sheet->mergeCells(
            "I{$totalRow}:J{$totalRow}"
        );

        $sheet->mergeCells(
            "M{$totalRow}:O{$totalRow}"
        );
    }

    /**
     * Membentuk data satu kartu rekening anggota.
     */
    private function buatDataCetakAnggota(
        Anggota $anggota,
        int $tahun
    ): array {
        $awalTahun =
            CarbonImmutable::create(
                year: $tahun,

                month: 1,

                day: 1,
            );

        $simpanan =
            $anggota
            ->simpanans
            ->values();

        $pinjamanReguler =
            $anggota
            ->pinjamans
            ->where(
                'jenis_pinjaman',
                Pinjaman::JENIS_REGULER
            )
            ->values();

        $pinjamanSebrak =
            $anggota
            ->pinjamans
            ->where(
                'jenis_pinjaman',
                Pinjaman::JENIS_SEBRAK
            )
            ->values();

        $angsuranReguler =
            $this->ambilAngsuran(
                $pinjamanReguler
            );

        $angsuranSebrak =
            $this->ambilAngsuran(
                $pinjamanSebrak
            );

        $saldoSimpananAwal =
            $this->hitungSaldoSimpananSebelum(
                simpanan: $simpanan,

                periode: $awalTahun,
            );

        $saldoReguler =
            $this->hitungSaldoPinjamanSebelum(
                pinjamans: $pinjamanReguler,

                angsurans: $angsuranReguler,

                periode: $awalTahun,
            );

        $saldoSebrak =
            $this->hitungSaldoPinjamanSebelum(
                pinjamans: $pinjamanSebrak,

                angsurans: $angsuranSebrak,

                periode: $awalTahun,
            );

        $rows =
            [];

        /*
         * Baris DES sebelum Januari berisi saldo awal dari tahun
         * sebelumnya, bukan transaksi baru.
         */
        $rows[] = [
            'bulan' =>
            'DES',

            'simpanan' =>
            $saldoSimpananAwal,

            'reguler' =>
            $this->buatSaldoAwalPinjaman(
                $saldoReguler
            ),

            'sebrak' =>
            $this->buatSaldoAwalPinjaman(
                $saldoSebrak
            ),

            'jumlah_tagihan' =>
            null,
        ];

        foreach (
            range(
                1,
                12
            )
            as $bulan
        ) {
            $periode =
                CarbonImmutable::create(
                    year: $tahun,

                    month: $bulan,

                    day: 1,
                );

            $simpananPeriode =
                $this->buatSimpananPeriode(
                    simpanans: $simpanan,

                    periode: $periode,
                );

            $reguler =
                $this->buatPinjamanPeriode(
                    pinjamans: $pinjamanReguler,

                    angsurans: $angsuranReguler,

                    periode: $periode,

                    saldo: $saldoReguler,

                    jenisPinjaman: Pinjaman::JENIS_REGULER,
                );

            $sebrak =
                $this->buatPinjamanPeriode(
                    pinjamans: $pinjamanSebrak,

                    angsurans: $angsuranSebrak,

                    periode: $periode,

                    saldo: $saldoSebrak,
                    jenisPinjaman: Pinjaman::JENIS_SEBRAK,
                );

            $jumlahTagihan =
                $simpananPeriode['jumlah']
                + $reguler['tagihan']
                + $sebrak['tagihan'];

            $hasTagihan =
                $simpananPeriode['has_activity']
                || $reguler['has_tagihan']
                || $sebrak['has_tagihan'];

            $rows[] = [
                'bulan' =>
                $this->namaBulan(
                    $bulan
                ),

                'simpanan' =>
                $simpananPeriode,

                'reguler' =>
                $reguler,

                'sebrak' =>
                $sebrak,

                'jumlah_tagihan' =>
                $hasTagihan
                    ? $jumlahTagihan
                    : null,
            ];
        }

        return [
            'nomor_anggota' =>
            $anggota
                ->nomor_anggota,

            'nama' =>
            $anggota
                ->nama,

            'rows' =>
            $rows,
        ];
    }

    /**
     * Mengambil seluruh angsuran dari seluruh pencairan satu jenis pinjaman.
     */
    private function ambilAngsuran(
        Collection $pinjamans
    ): Collection {
        return $pinjamans
            ->flatMap(
                fn(Pinjaman $pinjaman) =>
                $pinjaman
                    ->angsurans
            )
            ->sortBy([
                [
                    'periode',
                    'asc',
                ],
                [
                    'id',
                    'asc',
                ],
            ])
            ->values();
    }

    /**
     * Menghitung saldo simpanan kumulatif sebelum Januari.
     */
    private function hitungSaldoSimpananSebelum(
        Collection $simpanan,
        CarbonImmutable $periode
    ): array {
        $data =
            $simpanan
            ->filter(
                fn(Simpanan $item): bool =>
                $item
                    ->periode
                    ->lt(
                        $periode
                    )
            );

        $hasil = [
            'simpanan_pokok' =>
            (float) $data
                ->sum(
                    'simpanan_pokok'
                ),

            'simpanan_wajib' =>
            (float) $data
                ->sum(
                    'simpanan_wajib'
                ),

            'simpanan_sukarela' =>
            (float) $data
                ->sum(
                    'simpanan_sukarela'
                ),

            'simpanan_hari_raya' =>
            (float) $data
                ->sum(
                    'simpanan_hari_raya'
                ),

            'simpanan_rekreasi' =>
            (float) $data
                ->sum(
                    'simpanan_rekreasi'
                ),

            'has_activity' =>
            $data
                ->isNotEmpty(),
        ];

        $hasil['jumlah'] =
            $this->jumlahSimpanan(
                $hasil
            );

        return $hasil;
    }

    private function buatSimpananPeriode(
        Collection $simpanans,
        CarbonImmutable $periode
    ): array {
        $simpanan =
            $simpanans
            ->first(
                fn(Simpanan $item): bool =>
                $item
                    ->periode
                    ->format(
                        'Y-m'
                    )
                    === $periode
                    ->format(
                        'Y-m'
                    )
            );

        $hasil = [
            'simpanan_pokok' =>
            (float) (
                $simpanan
                ?->simpanan_pokok
                ?? 0
            ),

            'simpanan_wajib' =>
            (float) (
                $simpanan
                ?->simpanan_wajib
                ?? 0
            ),

            'simpanan_sukarela' =>
            (float) (
                $simpanan
                ?->simpanan_sukarela
                ?? 0
            ),

            'simpanan_hari_raya' =>
            (float) (
                $simpanan
                ?->simpanan_hari_raya
                ?? 0
            ),

            'simpanan_rekreasi' =>
            (float) (
                $simpanan
                ?->simpanan_rekreasi
                ?? 0
            ),
        ];

        $hasil['jumlah'] =
            $this->jumlahSimpanan(
                $hasil
            );

        $hasil['has_activity'] =
            $hasil['simpanan_pokok']
            !== 0.0
            || $hasil['simpanan_wajib']
            !== 0.0
            || $hasil['simpanan_sukarela']
            !== 0.0
            || $hasil['simpanan_hari_raya']
            !== 0.0
            || $hasil['simpanan_rekreasi']
            !== 0.0;

        return $hasil;
    }

    private function jumlahSimpanan(
        array $simpanan
    ): float {
        return
            (float) $simpanan['simpanan_pokok']
            + (float) $simpanan['simpanan_wajib']
            + (float) $simpanan['simpanan_sukarela']
            + (float) $simpanan['simpanan_hari_raya']
            + (float) $simpanan['simpanan_rekreasi'];
    }

    private function hitungSaldoPinjamanSebelum(
        Collection $pinjamans,
        Collection $angsurans,
        CarbonImmutable $periode
    ): float {
        return
            (float) $pinjamans
                ->filter(
                    fn(Pinjaman $pinjaman): bool =>
                    $pinjaman
                        ->tanggal_pinjaman
                        ->lt(
                            $periode
                        )
                )
                ->sum(
                    'nominal_pinjaman'
                )
            - (float) $angsurans
                ->filter(
                    fn(Angsuran $angsuran): bool =>
                    $angsuran
                        ->periode
                        ->lt(
                            $periode
                        )
                )
                ->sum(
                    'nominal_angsuran'
                );
    }

    private function buatSaldoAwalPinjaman(
        float $saldo
    ): array {
        return [
            'ke' =>
            null,

            'jumlah' =>
            null,

            'sisa' =>
            $saldo !== 0.0
                ? $saldo
                : null,

            'jasa' =>
            null,

            'tagihan' =>
            0,

            'has_activity' =>
            $saldo !== 0.0,
        ];
    }
    private function buatPinjamanPeriode(
        Collection $pinjamans,
        Collection $angsurans,
        CarbonImmutable $periode,
        float &$saldo,
        string $jenisPinjaman
    ): array {
        $key =
            $periode
            ->format(
                'Y-m'
            );

        $saldoAwal =
            $saldo;

        $pinjamanPeriode =
            $pinjamans
            ->filter(
                fn(Pinjaman $pinjaman): bool =>
                $pinjaman
                    ->tanggal_pinjaman
                    ->format(
                        'Y-m'
                    )
                    === $key
            )
            ->values();

        $angsuran =
            $angsurans
            ->first(
                fn(Angsuran $item): bool =>
                $item
                    ->periode
                    ->format(
                        'Y-m'
                    )
                    === $key
            );

        $nominalPinjaman =
            (float) $pinjamanPeriode
                ->sum(
                    'nominal_pinjaman'
                );

        $nominalAngsuran =
            (float) (
                $angsuran
                ?->nominal_angsuran
                ?? 0
            );

        $hasSaldoSebelumnya =
            $saldoAwal
            > 0;

        $isPinjamanAwal =
            !$hasSaldoSebelumnya
            && $nominalPinjaman > 0;

        $jumlah =
            $angsuran
            ? $nominalAngsuran
            : (
                $isPinjamanAwal
                ? $nominalPinjaman
                : null
            );

        $saldo =
            $saldoAwal
            - $nominalAngsuran
            + $nominalPinjaman;

        $jasa =
            $angsuran
            ? (float) $angsuran
                ->jasa_pinjaman
            : null;

        $hasActivity =
            $pinjamanPeriode
            ->isNotEmpty()
            || $angsuran
            !== null;

        return [
            'ke' =>
            $angsuran
                ?->angsuran_ke,

            'jumlah' =>
            $jumlah,

            'sisa' =>
            $hasActivity
                ? $saldo
                : null,

            'jasa' =>
            $jasa,

            'tagihan' =>
            $angsuran
                ? $nominalAngsuran
                + (
                    $jasa
                    ?? 0
                )
                : 0,
            'has_activity' =>
            $hasActivity,

            'has_tagihan' =>
            $angsuran
                !== null,
        ];
    }

    /**
     * Menulis satu blok anggota menggunakan format template.
     */
    private function tulisBlokAnggota(
        Worksheet $sheet,
        int $startRow,
        array $data
    ): void {
        $headerRow =
            $startRow;

        $columnHeaderRow =
            $startRow
            + 1;

        $firstDataRow =
            $startRow
            + self::FIRST_DATA_ROW_OFFSET;

        $totalRow =
            $startRow
            + self::TOTAL_ROW_OFFSET;

        /*
     * Header anggota.
     */
        $nomorAnggota =
            $this->formatNomorAnggota(
                $data['nomor_anggota']
            );

        $sheet->setCellValueExplicit(
            "A{$headerRow}",
            $nomorAnggota,
            DataType::TYPE_NUMERIC
        );

        $sheet
            ->getStyle("A{$headerRow}")
            ->getNumberFormat()
            ->setFormatCode('0');

        $sheet->setCellValue(
            "B{$headerRow}",
            $data['nama']
        );

        $sheet->setCellValue(
            "G{$headerRow}",
            "JUMLAH\nSIMPANAN"
        );

        $sheet->setCellValue(
            "H{$headerRow}",
            'PINJAMAN REGULER'
        );

        $sheet->setCellValue(
            "M{$headerRow}",
            'PINJAMAN SEBRAK'
        );

        $sheet->setCellValue(
            "R{$headerRow}",
            "JUMLAH\nTAGIHAN"
        );

        /*
     * Header kolom.
     * Kolom G dikosongkan karena sudah merge vertikal
     * dengan header JUMLAH SIMPANAN.
     */
        $sheet->fromArray(
            [
                [
                    'BULAN',
                    'SIMPOK',
                    'SIMWA',
                    'SSR',
                    'SHR',
                    'SREK',
                    null,
                    'KE',
                    'JUMLAH',
                    'SISA',
                    'JASA',
                    '+/-',
                    'KE',
                    'JUMLAH',
                    'SISA',
                    'JASA',
                    '+/-',
                ],
            ],
            null,
            "A{$columnHeaderRow}"
        );

        /*
     * Detail saldo awal dan transaksi bulanan.
     */
        foreach (
            $data['rows']
            as $offset => $row
        ) {
            $currentRow =
                $firstDataRow
                + $offset;

            $sheet->setCellValue(
                "A{$currentRow}",
                $row['bulan']
            );

            $this->tulisSimpanan(
                sheet: $sheet,
                row: $currentRow,
                data: $row['simpanan'],
            );

            $this->tulisPinjaman(
                sheet: $sheet,
                row: $currentRow,
                startColumn: 'H',
                data: $row['reguler'],
            );

            $this->tulisPinjaman(
                sheet: $sheet,
                row: $currentRow,
                startColumn: 'M',
                data: $row['sebrak'],
            );

            if (
                $row['jumlah_tagihan']
                === null
            ) {
                $sheet->setCellValue(
                    "R{$currentRow}",
                    null
                );
            } else {
                $sheet->setCellValue(
                    "R{$currentRow}",
                    "=G{$currentRow}"
                        . "+I{$currentRow}"
                        . "+K{$currentRow}"
                        . "+N{$currentRow}"
                        . "+P{$currentRow}"
                );
            }
        }

        /*
     * Footer jumlah.
     */
        $sheet->setCellValue(
            "A{$totalRow}",
            'JUMLAH'
        );

        foreach (
            range(
                'B',
                'F'
            )
            as $column
        ) {
            $sheet->setCellValue(
                "{$column}{$totalRow}",
                "=SUM("
                    . "{$column}{$firstDataRow}"
                    . ":"
                    . "{$column}"
                    . (
                        $totalRow
                        - 1
                    )
                    . ")"
            );
        }

        $sheet->setCellValue(
            "G{$totalRow}",
            "=SUM("
                . "B{$totalRow}"
                . ":"
                . "F{$totalRow}"
                . ")"
        );

        $sheet->setCellValue(
            "I{$totalRow}",
            'JUMLAH JASA REGULER'
        );

        $sheet->setCellValue(
            "K{$totalRow}",
            "=SUM("
                . "K{$firstDataRow}"
                . ":"
                . "K"
                . (
                    $totalRow
                    - 1
                )
                . ")"
        );

        $sheet->setCellValue(
            "M{$totalRow}",
            'JUMLAH JASA SEBRAK'
        );

        $sheet->setCellValue(
            "P{$totalRow}",
            "=SUM("
                . "P{$firstDataRow}"
                . ":"
                . "P"
                . (
                    $totalRow
                    - 1
                )
                . ")"
        );
    }

    private function tulisSimpanan(
        Worksheet $sheet,
        int $row,
        array $data
    ): void {
        $mapping = [
            'B' =>
            'simpanan_pokok',

            'C' =>
            'simpanan_wajib',

            'D' =>
            'simpanan_sukarela',

            'E' =>
            'simpanan_hari_raya',

            'F' =>
            'simpanan_rekreasi',
        ];

        foreach (
            $mapping
            as $column => $field
        ) {
            $value =
                (float) $data[$field];

            $sheet->setCellValue(
                "{$column}{$row}",
                $value !== 0.0
                    ? $value
                    : null
            );
        }

        if (
            ($data['has_activity'] ?? false)
            || $data['jumlah'] !== 0.0
        ) {
            $sheet->setCellValue(
                "G{$row}",
                "=SUM("
                    . "B{$row}"
                    . ":"
                    . "F{$row}"
                    . ")"
            );
        } else {
            $sheet->setCellValue(
                "G{$row}",
                null
            );
        }
    }

    private function tulisPinjaman(
        Worksheet $sheet,
        int $row,
        string $startColumn,
        array $data
    ): void {
        $mapping =
            $startColumn
            === 'H'
            ? [
                'ke' =>
                'H',

                'jumlah' =>
                'I',

                'sisa' =>
                'J',

                'jasa' =>
                'K',
            ]
            : [
                'ke' =>
                'M',

                'jumlah' =>
                'N',

                'sisa' =>
                'O',

                'jasa' =>
                'P',
            ];

        foreach (
            $mapping
            as $field => $column
        ) {
            $sheet->setCellValue(
                "{$column}{$row}",
                $data[$field]
            );
        }
    }

    private function namaBulan(
        int $bulan
    ): string {
        return [
            1 =>
            'JAN',

            2 =>
            'FEB',

            3 =>
            'MAR',

            4 =>
            'APR',

            5 =>
            'MEI',

            6 =>
            'JUN',

            7 =>
            'JUL',

            8 =>
            'AGUST',

            9 =>
            'SEP',

            10 =>
            'OKT',

            11 =>
            'NOP',

            12 =>
            'DES',
        ][$bulan];
    }

    private function aturHalamanCetak(
        Worksheet $sheet,
        int $lastRow
    ): void {
        $sheet
            ->getPageSetup()
            ->setOrientation(
                PageSetup::ORIENTATION_LANDSCAPE
            )
            ->setPaperSize(
                PageSetup::PAPERSIZE_LEGAL
            )
            ->setFitToWidth(
                1
            )
            ->setFitToHeight(
                0
            );

        $sheet
            ->getPageSetup()
            ->setPrintArea(
                "A1:R{$lastRow}"
            );

        $sheet
            ->getPageMargins()
            ->setTop(
                0.20
            )
            ->setBottom(
                0.16
            )
            ->setLeft(
                0.20
            )
            ->setRight(
                0.20
            );

        $sheet
            ->getSheetView()
            ->setZoomScale(
                80
            );
    }

    private function simpanFile(
        Spreadsheet $spreadsheet,
        int $tahun
    ): string {
        $folder =
            storage_path(
                'app/private/exports'
            );

        if (!is_dir($folder)) {
            mkdir(
                directory: $folder,

                permissions: 0755,

                recursive: true
            );
        }

        $path =
            $folder
            . DIRECTORY_SEPARATOR
            . 'kartu-rekening-'
            . $tahun
            . '-'
            . Str::uuid()
            . '.xlsx';

        $writer =
            IOFactory::createWriter(
                $spreadsheet,
                'Xlsx'
            );

        $writer->setPreCalculateFormulas(
            true
        );

        $writer->save(
            $path
        );

        $spreadsheet
            ->disconnectWorksheets();

        return $path;
    }
    private function getPersentaseJasa(
        string $jenisPinjaman
    ): float {
        return $jenisPinjaman
            === Pinjaman::JENIS_REGULER
            ? 1.50
            : 2.00;
    }
    private function formatNomorAnggota(
        mixed $nomorAnggota
    ): int {
        if (is_numeric($nomorAnggota)) {
            return (int) $nomorAnggota;
        }

        $angka =
            preg_replace(
                '/[^0-9]/',
                '',
                (string) $nomorAnggota
            );

        return (int) ($angka ?: 0);
    }
}
