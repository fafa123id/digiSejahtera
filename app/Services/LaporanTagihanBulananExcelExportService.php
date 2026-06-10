<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaporanTagihanBulananExcelExportService
{
    private const DATA_PER_BLOCK = 27;
    private const COLUMNS_PER_BLOCK = 3;
    private const FIRST_DATA_ROW = 5;
    private const LAST_DATA_ROW = 31;
    private const TOTAL_ROW = 36;
    private const DATE_ROW = 39;
    private const POSITION_ROW = 40;
    private const TREASURER_ROW = 44;

    private const SHEET_NAMES = [
        1 => 'JAN',
        2 => 'PEB',
        3 => 'MAR',
        4 => 'APR',
        5 => 'MEI',
        6 => 'JUN',
        7 => 'JUL',
        8 => 'AGU',
        9 => 'SEP',
        10 => 'OKT',
        11 => 'NOP',
        12 => 'DES',
    ];

    public function __construct(private readonly LaporanTagihanBulananService $laporanService) {}

    public function generate(int $tahun): string
    {
        $templatePath = storage_path('app/templates/tagihan-template.xlsx');

        if (!is_file($templatePath)) {
            throw new RuntimeException('Template laporan tagihan bulanan tidak ditemukan.');
        }

        $sourceSpreadsheet = IOFactory::load($templatePath);
        $spreadsheet = IOFactory::load($templatePath);
        $sourceSheet = $sourceSpreadsheet->getSheet(0);
        $data = $this->laporanService->buatData($tahun);

        foreach (range(1, 12) as $bulan) {
            $sheet = $spreadsheet->getSheet($bulan - 1);
            $sheet->setTitle($this->buatNamaSheet($bulan, $tahun));

            $this->tulisSheet(
                source: $sourceSheet,
                target: $sheet,
                anggota: $data[$bulan],
                bulan: $bulan,
                tahun: $tahun
            );
        }

        $spreadsheet->setActiveSheetIndex(0);

        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setSelectedCell('A1');
        $activeSheet->setTopLeftCell('A1');
        $activeSheet->setPaneTopLeftCell('A1');

        return $this->simpanFile($spreadsheet, $tahun);
    }

    private function tulisSheet(Worksheet $source, Worksheet $target, Collection $anggota, int $bulan, int $tahun): void
    {
        $jumlahBlok = max(2, (int) ceil($anggota->count() / self::DATA_PER_BLOCK));
        $lastColumnIndex = $jumlahBlok * self::COLUMNS_PER_BLOCK;
        $lastColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);

        $this->bersihkanNilaiDanMerge($target);
        $this->salinUkuranBaris($source, $target);
        $this->siapkanStyleBlok($source, $target, $jumlahBlok);
        $this->aturMerge($target, $jumlahBlok, $lastColumn);
        $this->tulisJudul($target, $bulan, $tahun);
        $this->tulisSemuaBlok($target, $anggota, $jumlahBlok);
        $this->tulisTandaTangan($source, $target, $lastColumnIndex);
        $this->aturHalamanCetak($source, $target, $lastColumn);

        $target->setShowGridlines(false);
        $target->setSelectedCell('A1');
        $target->setTopLeftCell('A1');
        $target->setPaneTopLeftCell('A1');
    }

    private function bersihkanNilaiDanMerge(Worksheet $sheet): void
    {
        foreach ($sheet->getMergeCells() as $range) {
            $sheet->unmergeCells($range);
        }

        $highestColumnIndex = max(32, Coordinate::columnIndexFromString($sheet->getHighestColumn()));
        $highestRow = max(self::TREASURER_ROW, $sheet->getHighestRow());

        foreach (range(1, $highestColumnIndex) as $columnIndex) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);

            foreach (range(1, $highestRow) as $row) {
                $sheet->setCellValue("{$column}{$row}", null);
            }
        }
    }

    private function salinUkuranBaris(Worksheet $source, Worksheet $target): void
    {
        foreach (range(1, self::TREASURER_ROW) as $row) {
            $target->getRowDimension($row)->setRowHeight(
                $source->getRowDimension($row)->getRowHeight()
            );
        }
    }

    private function siapkanStyleBlok(Worksheet $source, Worksheet $target, int $jumlahBlok): void
    {
        foreach (range(0, $jumlahBlok - 1) as $blockIndex) {
            $sourceStartColumnIndex = $blockIndex === 1 ? 4 : 1;
            $targetStartColumnIndex = 1 + ($blockIndex * self::COLUMNS_PER_BLOCK);

            $this->salinStyleBlok(
                source: $source,
                target: $target,
                sourceStartColumnIndex: $sourceStartColumnIndex,
                targetStartColumnIndex: $targetStartColumnIndex
            );
        }
    }

    private function salinStyleBlok(
        Worksheet $source,
        Worksheet $target,
        int $sourceStartColumnIndex,
        int $targetStartColumnIndex
    ): void {
        foreach (range(0, self::COLUMNS_PER_BLOCK - 1) as $offset) {
            $sourceColumn = Coordinate::stringFromColumnIndex($sourceStartColumnIndex + $offset);
            $targetColumn = Coordinate::stringFromColumnIndex($targetStartColumnIndex + $offset);

            $target->getColumnDimension($targetColumn)->setWidth(
                $source->getColumnDimension($sourceColumn)->getWidth()
            );

            foreach (range(1, self::TREASURER_ROW) as $row) {
                $target->duplicateStyle(
                    $source->getStyle("{$sourceColumn}{$row}"),
                    "{$targetColumn}{$row}"
                );
            }
        }
    }

    private function aturMerge(Worksheet $sheet, int $jumlahBlok, string $lastColumn): void
    {
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->mergeCells("A2:{$lastColumn}2");

        foreach (range(0, $jumlahBlok - 1) as $blockIndex) {
            $startColumnIndex = 1 + ($blockIndex * self::COLUMNS_PER_BLOCK);
            $middleColumnIndex = $startColumnIndex + 1;
            $startColumn = Coordinate::stringFromColumnIndex($startColumnIndex);
            $middleColumn = Coordinate::stringFromColumnIndex($middleColumnIndex);

            $sheet->mergeCells("{$startColumn}" . self::TOTAL_ROW . ":{$middleColumn}" . self::TOTAL_ROW);
        }
    }

    private function tulisJudul(Worksheet $sheet, int $bulan, int $tahun): void
    {
        $periode = CarbonImmutable::create($tahun, $bulan, 1)->locale('id');
        $namaBulan = ucfirst($periode->translatedFormat('F'));

        $sheet->setCellValue('A1', 'Tagihan Koperasi Sejahtera');
        $sheet->setCellValue('A2', "Bulan {$namaBulan} {$tahun}");
    }

    private function tulisSemuaBlok(Worksheet $sheet, Collection $anggota, int $jumlahBlok): void
    {
        foreach (range(0, $jumlahBlok - 1) as $blockIndex) {
            $startColumnIndex = 1 + ($blockIndex * self::COLUMNS_PER_BLOCK);
            $numberColumn = Coordinate::stringFromColumnIndex($startColumnIndex);
            $nameColumn = Coordinate::stringFromColumnIndex($startColumnIndex + 1);
            $amountColumn = Coordinate::stringFromColumnIndex($startColumnIndex + 2);

            $this->tulisHeaderBlok($sheet, $numberColumn, $nameColumn, $amountColumn);
            $this->tulisDataBlok($sheet, $anggota, $blockIndex, $numberColumn, $nameColumn, $amountColumn);
            $this->tulisTotalBlok($sheet, $numberColumn, $amountColumn);
        }
    }

    private function tulisHeaderBlok(
        Worksheet $sheet,
        string $numberColumn,
        string $nameColumn,
        string $amountColumn
    ): void {
        $sheet->setCellValue("{$numberColumn}4", 'NO');
        $sheet->setCellValue("{$nameColumn}4", 'NAMA');
        $sheet->setCellValue("{$amountColumn}4", 'JUMLAH');
        $sheet->getStyle("{$numberColumn}4:{$amountColumn}4")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function tulisDataBlok(
        Worksheet $sheet,
        Collection $anggota,
        int $blockIndex,
        string $numberColumn,
        string $nameColumn,
        string $amountColumn
    ): void {
        foreach (range(0, self::DATA_PER_BLOCK - 1) as $rowOffset) {
            $row = self::FIRST_DATA_ROW + $rowOffset;
            $dataIndex = ($blockIndex * self::DATA_PER_BLOCK) + $rowOffset;
            $item = $anggota->get($dataIndex);

            $sheet->setCellValue("{$numberColumn}{$row}", null);
            $sheet->setCellValue("{$nameColumn}{$row}", null);
            $sheet->setCellValue("{$amountColumn}{$row}", null);

            if ($item) {
                $sheet->setCellValueExplicit("{$numberColumn}{$row}", $dataIndex + 1, DataType::TYPE_NUMERIC);
                $sheet->setCellValue("{$nameColumn}{$row}", $item['nama']);
                $sheet->setCellValue("{$amountColumn}{$row}", (float) $item['jumlah_tagihan']);
            }

            $sheet->getStyle("{$numberColumn}{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("{$amountColumn}{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getStyle("{$amountColumn}" . self::FIRST_DATA_ROW . ":{$amountColumn}" . self::LAST_DATA_ROW)
            ->getNumberFormat()
            ->setFormatCode('#,##0;[Red]-#,##0;-');
    }

    private function tulisTotalBlok(Worksheet $sheet, string $numberColumn, string $amountColumn): void
    {
        $sheet->setCellValue("{$numberColumn}" . self::TOTAL_ROW, 'JUMLAH');

        $sheet->setCellValue(
            "{$amountColumn}" . self::TOTAL_ROW,
            "=SUM({$amountColumn}" . self::FIRST_DATA_ROW . ":{$amountColumn}" . self::LAST_DATA_ROW . ')'
        );

        $sheet->getStyle("{$numberColumn}" . self::TOTAL_ROW)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("{$amountColumn}" . self::TOTAL_ROW)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("{$amountColumn}" . self::TOTAL_ROW)
            ->getNumberFormat()
            ->setFormatCode('#,##0;[Red]-#,##0;-');
    }

    private function tulisTandaTangan(Worksheet $source, Worksheet $target, int $lastColumnIndex): void
    {
        $signatureColumnIndex = max(5, $lastColumnIndex - 1);
        $signatureColumn = Coordinate::stringFromColumnIndex($signatureColumnIndex);

        foreach (range(self::DATE_ROW, self::TREASURER_ROW) as $row) {
            $target->duplicateStyle($source->getStyle("E{$row}"), "{$signatureColumn}{$row}");
        }

        $tanggalCetak = CarbonImmutable::now()->locale('id')->translatedFormat('d F Y');

        $target->setCellValue("{$signatureColumn}" . self::DATE_ROW, "Grogol, {$tanggalCetak}");
        $target->setCellValue("{$signatureColumn}" . self::POSITION_ROW, 'Bendahara');
        $target->setCellValue(
            "{$signatureColumn}" . self::TREASURER_ROW,
            config('koperasi.bendahara', 'SULASTRI')
        );
    }

    private function aturHalamanCetak(Worksheet $source, Worksheet $target, string $lastColumn): void
    {
        $target->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_LEGAL)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(1)
            ->setPrintArea("A1:{$lastColumn}" . self::TREASURER_ROW);

        $target->getPageMargins()
            ->setLeft($source->getPageMargins()->getLeft())
            ->setRight($source->getPageMargins()->getRight())
            ->setTop($source->getPageMargins()->getTop())
            ->setBottom($source->getPageMargins()->getBottom())
            ->setHeader($source->getPageMargins()->getHeader())
            ->setFooter($source->getPageMargins()->getFooter());
    }

    private function buatNamaSheet(int $bulan, int $tahun): string
    {
        return self::SHEET_NAMES[$bulan] . '-' . substr((string) $tahun, -2);
    }

    private function simpanFile(Spreadsheet $spreadsheet, int $tahun): string
    {
        $folder = storage_path('app/private/exports');

        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $path = $folder . DIRECTORY_SEPARATOR . 'laporan-tagihan-bulanan-' . $tahun . '-' . Str::uuid() . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(true);
        $writer->save($path);

        return $path;
    }
}
