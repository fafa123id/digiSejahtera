<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class KitirExcelExportService
{
    private const TEMPLATE_SHEET =
    'Sheet1';

    private const BLOCK_HEIGHT =
    19;

    private const BLOCK_START_ROW =
    1;

    private const BLOCK_END_ROW =
    19;

    private const BLOCK_START_COLUMN =
    1;

    private const BLOCK_END_COLUMN =
    15;

    private const CARDS_PER_BLOCK =
    3;

    private const CARD_WIDTH =
    5;

    public function __construct(
        private readonly KitirService $kitirService
    ) {}

    public function generate(
        int $tahun,
        int $bulan
    ): string {
        $templatePath =
            storage_path(
                'app/templates/template-kitir.xlsx'
            );

        if (!is_file($templatePath)) {
            throw new RuntimeException(
                'Template KITIR tidak ditemukan pada storage/app/templates/template-kitir.xlsx.'
            );
        }

        $kitirs =
            collect(
                $this
                    ->kitirService
                    ->buatSemuaData(
                        tahun: $tahun,

                        bulan: $bulan,
                    )
            )
            ->values();

        $spreadsheet =
            IOFactory::load(
                $templatePath
            );

        $sheet =
            $spreadsheet
            ->getSheetByName(
                self::TEMPLATE_SHEET
            )
            ?? $spreadsheet
            ->getActiveSheet();

        $this->sisakanSheetTemplate(
            spreadsheet: $spreadsheet,

            sheet: $sheet,
        );

        $sheet->setTitle(
            'KITIR'
        );

        $sheet->setShowGridlines(
            false
        );

        $templateMerges =
            $this->ambilMergeTemplate(
                $sheet
            );

        $this->sisakanBlokTemplate(
            sheet: $sheet,

            templateMerges: $templateMerges,
        );

        $jumlahBlok =
            max(
                1,
                (int) ceil(
                    $kitirs->count()
                        / self::CARDS_PER_BLOCK
                )
            );

        $this->siapkanBlokOutput(
            sheet: $sheet,

            templateMerges: $templateMerges,

            jumlahBlok: $jumlahBlok,
        );

        $tanggalCetak =
            config(
                'koperasi.lokasi',
                'Grogol'
            )
            . ', '
            . CarbonImmutable
            ::now()
            ->locale(
                'id'
            )
            ->translatedFormat(
                'd F Y'
            );

        foreach (
            range(
                0,
                $jumlahBlok - 1
            )
            as $blockIndex
        ) {
            $startRow =
                self::BLOCK_START_ROW
                + (
                    $blockIndex
                    * self::BLOCK_HEIGHT
                );

            foreach (
                range(
                    0,
                    self::CARDS_PER_BLOCK - 1
                )
                as $slot
            ) {
                $dataIndex =
                    (
                        $blockIndex
                        * self::CARDS_PER_BLOCK
                    )
                    + $slot;

                $kitir =
                    $kitirs
                    ->get(
                        $dataIndex
                    );

                if (!$kitir) {
                    $this->kosongkanSlot(
                        sheet: $sheet,

                        startRow: $startRow,

                        slot: $slot,
                    );

                    continue;
                }

                $this->tulisKartu(
                    sheet: $sheet,

                    startRow: $startRow,

                    slot: $slot,

                    kitir: $kitir,

                    tanggalCetak: $tanggalCetak,
                );
            }
        }

        $lastRow =
            $jumlahBlok
            * self::BLOCK_HEIGHT;

        $this->aturHalamanCetak(
            sheet: $sheet,

            lastRow: $lastRow,
        );

        return $this->simpanFile(
            spreadsheet: $spreadsheet,

            tahun: $tahun,

            bulan: $bulan,
        );
    }

    private function sisakanSheetTemplate(
        Spreadsheet $spreadsheet,
        Worksheet $sheet
    ): void {
        for (
            $index =
                $spreadsheet
                ->getSheetCount()
                - 1;

            $index >= 0;

            $index--
        ) {
            if (
                $spreadsheet
                ->getSheet(
                    $index
                )
                === $sheet
            ) {
                continue;
            }

            $spreadsheet
                ->removeSheetByIndex(
                    $index
                );
        }

        $spreadsheet
            ->setActiveSheetIndex(
                0
            );
    }

    private function ambilMergeTemplate(
        Worksheet $sheet
    ): array {
        return collect(
            $sheet
                ->getMergeCells()
        )
            ->filter(
                function (
                    string $range
                ): bool {
                    [
                        [
                            $startColumn,
                            $startRow,
                        ],
                        [
                            $endColumn,
                            $endRow,
                        ],
                    ] =
                        Coordinate
                        ::rangeBoundaries(
                            $range
                        );

                    return
                        $startColumn
                        >= self::BLOCK_START_COLUMN
                        && $endColumn
                        <= self::BLOCK_END_COLUMN
                        && $startRow
                        >= self::BLOCK_START_ROW
                        && $endRow
                        <= self::BLOCK_END_ROW;
                }
            )
            ->values()
            ->all();
    }

    private function sisakanBlokTemplate(
        Worksheet $sheet,
        array $templateMerges
    ): void {
        foreach (
            $sheet
                ->getMergeCells()
            as $range
        ) {
            if (
                in_array(
                    $range,
                    $templateMerges,
                    true
                )
            ) {
                continue;
            }

            $sheet->unmergeCells(
                $range
            );
        }

        $highestRow =
            $sheet
            ->getHighestRow();

        if (
            $highestRow
            > self::BLOCK_END_ROW
        ) {
            $sheet->removeRow(
                self::BLOCK_END_ROW + 1,
                $highestRow
                    - self::BLOCK_END_ROW
            );
        }
    }

    private function siapkanBlokOutput(
        Worksheet $sheet,
        array $templateMerges,
        int $jumlahBlok
    ): void {
        if (
            $jumlahBlok
            <= 1
        ) {
            return;
        }

        foreach (
            range(
                1,
                $jumlahBlok - 1
            )
            as $blockIndex
        ) {
            $targetStartRow =
                self::BLOCK_START_ROW
                + (
                    $blockIndex
                    * self::BLOCK_HEIGHT
                );

            $sheet->insertNewRowBefore(
                $targetStartRow,
                self::BLOCK_HEIGHT
            );

            $this->salinBlokTemplate(
                sheet: $sheet,

                templateMerges: $templateMerges,

                targetStartRow: $targetStartRow,
            );
        }
    }

    private function salinBlokTemplate(
        Worksheet $sheet,
        array $templateMerges,
        int $targetStartRow
    ): void {
        foreach (
            range(
                self::BLOCK_START_ROW,
                self::BLOCK_END_ROW
            )
            as $sourceRow
        ) {
            $targetRow =
                $targetStartRow
                + (
                    $sourceRow
                    - self::BLOCK_START_ROW
                );

            $sheet
                ->getRowDimension(
                    $targetRow
                )
                ->setRowHeight(
                    $sheet
                        ->getRowDimension(
                            $sourceRow
                        )
                        ->getRowHeight()
                );

            foreach (
                range(
                    self::BLOCK_START_COLUMN,
                    self::BLOCK_END_COLUMN
                )
                as $columnIndex
            ) {
                $column =
                    Coordinate
                    ::stringFromColumnIndex(
                        $columnIndex
                    );

                $sourceCoordinate =
                    "{$column}{$sourceRow}";

                $targetCoordinate =
                    "{$column}{$targetRow}";

                $sourceCell =
                    $sheet
                    ->getCell(
                        $sourceCoordinate
                    );

                $sheet->setCellValue(
                    $targetCoordinate,
                    $sourceCell
                        ->getValue()
                );

                $sheet->duplicateStyle(
                    $sheet
                        ->getStyle(
                            $sourceCoordinate
                        ),
                    $targetCoordinate
                );
            }
        }

        foreach (
            $templateMerges
            as $range
        ) {
            [
                [
                    $startColumn,
                    $startRow,
                ],
                [
                    $endColumn,
                    $endRow,
                ],
            ] =
                Coordinate
                ::rangeBoundaries(
                    $range
                );

            $targetStartColumn =
                Coordinate
                ::stringFromColumnIndex(
                    $startColumn
                );

            $targetEndColumn =
                Coordinate
                ::stringFromColumnIndex(
                    $endColumn
                );

            $targetStartRowIndex =
                $targetStartRow
                + (
                    $startRow
                    - self::BLOCK_START_ROW
                );

            $targetEndRowIndex =
                $targetStartRow
                + (
                    $endRow
                    - self::BLOCK_START_ROW
                );

            $targetRange =
                "{$targetStartColumn}{$targetStartRowIndex}:{$targetEndColumn}{$targetEndRowIndex}";

            if (
                !in_array(
                    $targetRange,
                    $sheet
                        ->getMergeCells(),
                    true
                )
            ) {
                $sheet->mergeCells(
                    $targetRange
                );
            }
        }
    }

    private function tulisKartu(
        Worksheet $sheet,
        int $startRow,
        int $slot,
        array $kitir,
        string $tanggalCetak
    ): void {
        $baseColumnIndex =
            self::BLOCK_START_COLUMN
            + (
                $slot
                * self::CARD_WIDTH
            );

        $labelColumn =
            Coordinate
            ::stringFromColumnIndex(
                $baseColumnIndex
            );

        $periodColumn =
            Coordinate
            ::stringFromColumnIndex(
                $baseColumnIndex
                    + 1
            );

        $separatorColumn =
            Coordinate
            ::stringFromColumnIndex(
                $baseColumnIndex
                    + 2
            );

        $valueColumn =
            Coordinate
            ::stringFromColumnIndex(
                $baseColumnIndex
                    + 3
            );

        $numberColumn =
            Coordinate
            ::stringFromColumnIndex(
                $baseColumnIndex
                    + 4
            );

        $this->bersihkanNilaiDinamis(
            sheet: $sheet,

            startRow: $startRow,

            slot: $slot,
        );

        $sheet->setCellValue(
            "{$labelColumn}{$startRow}",
            config(
                'koperasi.nama',
                'KOPERASI SEJAHTERA'
            )
        );

        $sheet->setCellValue(
            "{$labelColumn}"
                . (
                    $startRow
                    + 2
                ),
            'Nama/ No Angt.'
        );

        $sheet->setCellValue(
            "{$periodColumn}"
                . (
                    $startRow
                    + 2
                ),
            ':'
        );

        $sheet->setCellValue(
            "{$separatorColumn}"
                . (
                    $startRow
                    + 2
                ),
            $kitir['nama']
        );

        $sheet->setCellValueExplicit(
            "{$numberColumn}"
                . (
                    $startRow
                    + 2
                ),
            (int) $kitir['nomor_anggota'],
            DataType::TYPE_NUMERIC
        );

        $sheet->setCellValue(
            "{$periodColumn}"
                . (
                    $startRow
                    + 7
                ),
            $kitir['reguler']['angsuran_ke']
                ?? null
        );

        $sheet->setCellValue(
            "{$periodColumn}"
                . (
                    $startRow
                    + 9
                ),
            $kitir['sebrak']['angsuran_ke']
                ?? null
        );

        $values = [
            $kitir['simpanan_wajib']
                ?? 0,

            $kitir['simpanan_sukarela']
                ?? 0,

            $kitir['simpanan_hari_raya']
                ?? 0,

            $kitir['reguler']['nominal_angsuran']
                ?? 0,

            $kitir['reguler']['jasa_pinjaman']
                ?? 0,

            $kitir['sebrak']['nominal_angsuran']
                ?? 0,

            $kitir['sebrak']['jasa_pinjaman']
                ?? 0,

            $kitir['simpanan_rekreasi']
                ?? 0,
        ];

        foreach (
            $values
            as $index => $value
        ) {
            $sheet->setCellValue(
                "{$valueColumn}"
                    . (
                        $startRow
                        + 4
                        + $index
                    ),
                (float) $value
            );
        }

        $sheet->setCellValue(
            "{$valueColumn}"
                . (
                    $startRow
                    + 12
                ),
            '=SUM('
                . $valueColumn
                . (
                    $startRow
                    + 4
                )
                . ':'
                . $valueColumn
                . (
                    $startRow
                    + 11
                )
                . ')'
        );

        $sheet->setCellValue(
            "{$valueColumn}"
                . (
                    $startRow
                    + 13
                ),
            (float) (
                $kitir['sisa_pinjaman']
                ?? 0
            )
        );

        $sheet->setCellValue(
            "{$valueColumn}"
                . (
                    $startRow
                    + 14
                ),
            $tanggalCetak
        );

        $sheet->setCellValue(
            "{$valueColumn}"
                . (
                    $startRow
                    + 15
                ),
            'Bendahara,'
        );

        $sheet->setCellValue(
            "{$valueColumn}"
                . (
                    $startRow
                    + 18
                ),
            config(
                'koperasi.bendahara',
                'SULASTRI'
            )
        );

        $sheet
            ->getStyle(
                "{$valueColumn}"
                    . (
                        $startRow
                        + 4
                    )
                    . ':'
                    . $valueColumn
                    . (
                        $startRow
                        + 14
                    )
            )
            ->getNumberFormat()
            ->setFormatCode(
                '#,##0;[Red]-#,##0;-'
            );
    }

    private function bersihkanNilaiDinamis(
        Worksheet $sheet,
        int $startRow,
        int $slot
    ): void {
        $baseColumnIndex =
            self::BLOCK_START_COLUMN
            + (
                $slot
                * self::CARD_WIDTH
            );

        $periodColumn =
            Coordinate
            ::stringFromColumnIndex(
                $baseColumnIndex
                    + 1
            );

        $nameColumn =
            Coordinate
            ::stringFromColumnIndex(
                $baseColumnIndex
                    + 2
            );

        $valueColumn =
            Coordinate
            ::stringFromColumnIndex(
                $baseColumnIndex
                    + 3
            );

        $numberColumn =
            Coordinate
            ::stringFromColumnIndex(
                $baseColumnIndex
                    + 4
            );

        $sheet->setCellValue(
            "{$nameColumn}"
                . (
                    $startRow
                    + 2
                ),
            null
        );

        $sheet->setCellValue(
            "{$numberColumn}"
                . (
                    $startRow
                    + 2
                ),
            null
        );

        $sheet->setCellValue(
            "{$periodColumn}"
                . (
                    $startRow
                    + 7
                ),
            null
        );

        $sheet->setCellValue(
            "{$periodColumn}"
                . (
                    $startRow
                    + 9
                ),
            null
        );

        foreach (
            range(
                4,
                18
            )
            as $offset
        ) {
            $sheet->setCellValue(
                "{$valueColumn}"
                    . (
                        $startRow
                        + $offset
                    ),
                null
            );
        }
    }

    private function kosongkanSlot(
        Worksheet $sheet,
        int $startRow,
        int $slot
    ): void {
        $startColumnIndex =
            self::BLOCK_START_COLUMN
            + (
                $slot
                * self::CARD_WIDTH
            );

        $endColumnIndex =
            $startColumnIndex
            + self::CARD_WIDTH
            - 1;

        foreach (
            range(
                $startColumnIndex,
                $endColumnIndex
            )
            as $columnIndex
        ) {
            $column =
                Coordinate
                ::stringFromColumnIndex(
                    $columnIndex
                );

            foreach (
                range(
                    $startRow,
                    $startRow
                        + self::BLOCK_HEIGHT
                        - 1
                )
                as $row
            ) {
                $sheet->setCellValue(
                    "{$column}{$row}",
                    null
                );
            }
        }
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
            ->setFitToPage(
                true
            )
            ->setFitToWidth(
                1
            )
            ->setFitToHeight(
                0
            )
            ->setPrintArea(
                "A1:O{$lastRow}"
            );

        $sheet
            ->getPageMargins()
            ->setLeft(
                0.31
            )
            ->setRight(
                0.12
            )
            ->setTop(
                0.16
            )
            ->setBottom(
                0.16
            );

        for (
            $row =
                39;

            $row
                <= $lastRow;

            $row +=
                38
        ) {
            $sheet->setBreak(
                "A{$row}",
                Worksheet::BREAK_ROW
            );
        }
    }

    private function simpanFile(
        Spreadsheet $spreadsheet,
        int $tahun,
        int $bulan
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

        if (
            $spreadsheet
            ->getSheetCount()
            === 0
        ) {
            throw new RuntimeException(
                'Workbook KITIR tidak memiliki worksheet.'
            );
        }

        $bulanFile =
            str_pad(
                (string) $bulan,
                2,
                '0',
                STR_PAD_LEFT
            );

        $path =
            $folder
            . DIRECTORY_SEPARATOR
            . 'kitir-'
            . $tahun
            . '-'
            . $bulanFile
            . '-'
            . Str::uuid()
            . '.xlsx';

        $writer =
            new Xlsx(
                $spreadsheet
            );

        $writer->setPreCalculateFormulas(
            true
        );

        $writer->save(
            $path
        );

        return $path;
    }
}
