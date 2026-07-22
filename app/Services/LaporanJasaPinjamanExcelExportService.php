<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class LaporanJasaPinjamanExcelExportService
{
    private const DATA_START_ROW = 7;
    private const TEMPLATE_TOTAL_ROW = 63;

    public function __construct(private readonly LaporanJasaPinjamanService $laporanService)
    {
    }

    public function generate(int $tahun): string
    {
        $templatePath = storage_path('app/templates/laporan-jasa-pinjaman-template.xls');

        if (!is_file($templatePath)) {
            throw new RuntimeException('Template laporan jasa pinjaman tidak ditemukan.');
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $this->ambilSheetUtama($spreadsheet, 'JASA PINJAMAN');
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

        $dateRow = $totalRow + 2;
        $positionRow = $totalRow + 3;
        $nameRow = $totalRow + 6;

        $sheet->setCellValue('A2', 'LAPORAN KEUANGAN ANGGOTA KOPERASI SEJAHTERA');
        $sheet->setCellValue('A3', "PER DESEMBER {$tahun}");

        foreach (range(0, $jumlahBarisData - 1) as $offset) {
            $row = self::DATA_START_ROW + $offset;

            foreach (range('A', 'E') as $column) {
                $sheet->duplicateStyle($sheet->getStyle("{$column}".self::DATA_START_ROW), "{$column}{$row}");
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
            $sheet->setCellValue("C{$row}", $item['jasa_reguler']);
            $sheet->setCellValue("D{$row}", $item['jasa_sebrak']);
            $sheet->setCellValue("E{$row}", $item['jumlah_jasa']);
        }

        $dataEndRow = self::DATA_START_ROW + max(0, $rows->count() - 1);

        $sheet->setCellValue("A{$totalRow}", 'JUMLAH');
        $sheet->setCellValue("C{$totalRow}", $rows->isEmpty() ? 0 : "=SUM(C".self::DATA_START_ROW.":C{$dataEndRow})");
        $sheet->setCellValue("D{$totalRow}", $rows->isEmpty() ? 0 : "=SUM(D".self::DATA_START_ROW.":D{$dataEndRow})");
        $sheet->setCellValue("E{$totalRow}", $rows->isEmpty() ? 0 : "=SUM(E".self::DATA_START_ROW.":E{$dataEndRow})");

        if (!in_array("A{$totalRow}:B{$totalRow}", $sheet->getMergeCells(), true)) {
            $sheet->mergeCells("A{$totalRow}:B{$totalRow}");
        }

        $tanggalCetak = CarbonImmutable::now()->locale('id')->translatedFormat('d F Y');

        $sheet->setCellValue("D{$dateRow}", "Kediri, {$tanggalCetak}");
        $sheet->setCellValue("B{$positionRow}", 'Ketua,');
        $sheet->setCellValue("D{$positionRow}", 'Bendahara,');
        $sheet->setCellValue("B{$nameRow}", config('koperasi.ketua', 'PRASTOPO'));
        $sheet->setCellValue("D{$nameRow}", config('koperasi.bendahara', 'SULASTRI'));

        $sheet->getStyle("C".self::DATA_START_ROW.":E{$totalRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0;[Red]-#,##0;-');

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
            ->setPaperSize(PageSetup::PAPERSIZE_LETTER)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setPrintArea("A1:H{$nameRow}");
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

        $path = $folder.DIRECTORY_SEPARATOR.'laporan-jasa-pinjaman-'.$tahun.'-'.Str::uuid().'.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(true);
        $writer->save($path);

        return $path;
    }
}