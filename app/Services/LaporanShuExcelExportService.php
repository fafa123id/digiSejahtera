<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class LaporanShuExcelExportService
{
    private const DATA_START_ROW = 8;
    private const TEMPLATE_TOTAL_ROW = 64;

    public function __construct(private readonly LaporanShuService $laporanService) {}

    public function generate(int $tahun): string
    {
        $templatePath = storage_path('app/templates/laporan-shu-template.xls');

        if (!is_file($templatePath)) {
            throw new RuntimeException('Template laporan SHU tidak ditemukan.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $this->ambilSheetUtama($spreadsheet, 'SHU');
        $data = $this->laporanService->buatData($tahun);

        $this->tulisSheet($sheet, $data['rows'], $tahun);

        $spreadsheet->setActiveSheetIndex(0);
        $sheet->setSelectedCell('A1');
        $sheet->setTopLeftCell('A1');
        $sheet->setPaneTopLeftCell('A1');

        return $this->simpanFile($spreadsheet, $tahun);
    }

    private function tulisSheet(Worksheet $sheet, Collection $rows, int $tahun): void
    {
        $jumlahBarisData = max(1, $rows->count());
        $totalRow = self::DATA_START_ROW + $jumlahBarisData + 1;
        $delta = $totalRow - self::TEMPLATE_TOTAL_ROW;

        if ($delta > 0) {
            $sheet->insertNewRowBefore(self::TEMPLATE_TOTAL_ROW, $delta);
        }

        if ($delta < 0) {
            $sheet->removeRow($totalRow, abs($delta));
        }

        $sheet->setCellValue('A2', 'LAPORAN KEUANGAN ANGGOTA KOPERASI SEJAHTERA');
        $sheet->setCellValue('A3', "PER DESEMBER {$tahun}");

        foreach (range(0, $jumlahBarisData - 1) as $offset) {
            $row = self::DATA_START_ROW + $offset;

            foreach (range('A', 'N') as $column) {
                $sheet->duplicateStyle($sheet->getStyle("{$column}" . self::DATA_START_ROW), "{$column}{$row}");
                $sheet->setCellValue("{$column}{$row}", null);
            }

            $sheet->getRowDimension($row)->setRowHeight(
                $sheet->getRowDimension(self::DATA_START_ROW)->getRowHeight()
            );
        }

        foreach ($rows as $index => $item) {
            $row = self::DATA_START_ROW + $index;

            $sheet->setCellValueExplicit("A{$row}", $index + 1, DataType::TYPE_NUMERIC);
            $sheet->setCellValue("B{$row}", $item['nama']);
            $sheet->setCellValue("C{$row}", $item['simpanan_pokok']);
            $sheet->setCellValue("D{$row}", $item['simpanan_wajib']);
            $sheet->setCellValue("E{$row}", $item['simpanan_sukarela']);
            $sheet->setCellValue("F{$row}", $item['simpanan_hari_raya']);
            $sheet->setCellValue("G{$row}", $item['simpanan_rekreasi']);
            $sheet->setCellValue("H{$row}", $item['jumlah_simpanan']);
            $sheet->setCellValue("I{$row}", $item['pinjaman_reguler']);
            $sheet->setCellValue("J{$row}", $item['pinjaman_sebrak']);
            $sheet->setCellValue("K{$row}", $item['jumlah_pinjaman']);
            $sheet->setCellValue("L{$row}", $item['shu_simpanan']);
            $sheet->setCellValue("M{$row}", $item['shu_pinjaman']);
            $sheet->setCellValue("N{$row}", $item['jumlah_shu']);
        }

        $dataEndRow = self::DATA_START_ROW + max(0, $rows->count() - 1);

        $sheet->setCellValue("A{$totalRow}", 'JUMLAH');

        foreach (range('C', 'N') as $column) {
            $sheet->setCellValue(
                "{$column}{$totalRow}",
                $rows->isEmpty() ? 0 : "=SUM({$column}" . self::DATA_START_ROW . ":{$column}{$dataEndRow})"
            );
        }

        if (!in_array("A{$totalRow}:B{$totalRow}", $sheet->getMergeCells(), true)) {
            $sheet->mergeCells("A{$totalRow}:B{$totalRow}");
        }

        $sheet->getStyle("C" . self::DATA_START_ROW . ":N{$totalRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0;[Red]-#,##0;-');

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_LEGAL)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setPrintArea("A1:R{$totalRow}");
    }

    private function ambilSheetUtama(Spreadsheet $spreadsheet, string $nama): Worksheet
    {
        $sheet = $spreadsheet->getSheet(0);

        for ($index = $spreadsheet->getSheetCount() - 1; $index >= 1; $index--) {
            $spreadsheet->removeSheetByIndex($index);
        }

        $sheet->setTitle($nama);

        return $sheet;
    }

    private function simpanFile(Spreadsheet $spreadsheet, int $tahun): string
    {
        $folder = storage_path('app/private/exports');

        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $path = $folder . DIRECTORY_SEPARATOR . 'laporan-shu-' . $tahun . '-' . Str::uuid() . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(true);
        $writer->save($path);

        return $path;
    }
}
