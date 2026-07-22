<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class LaporanSimpananHariRayaExcelExportService
{
    private const DATA_PER_PAGE = 28;

    public function __construct(private readonly LaporanSimpananHariRayaService $laporanService) {}

    public function generate(int $tahun, int $bulan): string
    {
        $templatePath = storage_path('app/templates/shr-template.xlsx');

        if (!is_file($templatePath)) {
            throw new RuntimeException('Template laporan Simpanan Hari Raya tidak ditemukan.');
        }

        $sourceSpreadsheet = IOFactory::load($templatePath);
        $spreadsheet = IOFactory::load($templatePath);
        $data = $this->laporanService->buatData($tahun, $bulan);
        $tanggalCetak = CarbonImmutable::now()->locale('id');

        $sourceIslam = $this->ambilSheet($sourceSpreadsheet, 'ISLAM');
        $sourceNonIslam = $this->ambilSheet($sourceSpreadsheet, 'NON');
        $sheetIslam = $this->ambilSheet($spreadsheet, 'ISLAM');
        $sheetNonIslam = $this->ambilSheet($spreadsheet, 'NON');

        $bulanIslam = ucfirst($data['tanggal_idul_fitri']->locale('id')->translatedFormat('F'));

        $this->tulisSheet(
            source: $sourceIslam,
            target: $sheetIslam,
            anggota: $data['islam'],
            judul: "SIMPANAN HARI RAYA (Sampai Bulan {$bulanIslam} {$tahun})",
            tahun: $tahun,
            tanggalCetak: $tanggalCetak
        );

        $this->tulisSheet(
            source: $sourceNonIslam,
            target: $sheetNonIslam,
            anggota: $data['nonislam'],
            judul: "SIMPANAN HARI RAYA (Sampai Bulan Desember {$tahun})",
            tahun: $tahun,
            tanggalCetak: $tanggalCetak
        );

        $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($sheetIslam));
        $sheetIslam->setSelectedCell('A1');
        $sheetIslam->setTopLeftCell('A1');
        $sheetIslam->setPaneTopLeftCell('A1');

        return $this->simpanFile($spreadsheet, $tahun);
    }

    private function tulisSheet(
        Worksheet $source,
        Worksheet $target,
        Collection $anggota,
        string $judul,
        int $tahun,
        CarbonImmutable $tanggalCetak
    ): void {
        $headerSourceRow = $this->cariBarisHeader($source);
        $dataSourceRow = $headerSourceRow + 2;
        $totalSourceRow = $this->cariBarisTotal($source);
        $tanggalSourceRow = $this->cariBarisTanggal($source);
        $jabatanSourceRow = $this->cariBarisJabatan($source);
        $namaSourceRow = $jabatanSourceRow + 4;

        $this->bersihkanAreaDinamis($target);

        $target->setCellValue('A1', $judul);
        $target->setCellValue('A4', "TAHUN {$tahun}");

        $row = 6;
        $this->tulisHeader($source, $target, $headerSourceRow, $row);
        $row++;

        foreach ($anggota as $index => $item) {
            if ($index > 0 && $index % self::DATA_PER_PAGE === 0) {
                $target->setBreak("A{$row}", Worksheet::BREAK_ROW);
                $this->tulisHeader($source, $target, $headerSourceRow, $row);
                $row++;
            }

            $this->salinBaris($source, $dataSourceRow, $target, $row);

            $nomor = $index + 1;

            $target->setCellValueExplicit("A{$row}", $nomor, DataType::TYPE_NUMERIC);
            $target->setCellValue("B{$row}", $item['nama']);
            $target->setCellValue("C{$row}", (float) $item['jumlah']);
            $target->setCellValue("D{$row}", null);
            $target->setCellValue("E{$row}", null);

            if ($nomor % 2 === 1) {
                $target->setCellValueExplicit("D{$row}", $nomor, DataType::TYPE_NUMERIC);
            } else {
                $target->setCellValueExplicit("E{$row}", $nomor, DataType::TYPE_NUMERIC);
            }

            $row++;
        }

        $totalRow = $row;
        $this->salinBaris($source, $totalSourceRow, $target, $totalRow);

        $dataStartRow = 7;
        $dataEndRow = max(7, $totalRow - 1);

        $target->setCellValue("A{$totalRow}", null);
        $target->setCellValue("B{$totalRow}", 'JUMLAH');
        $target->setCellValue("C{$totalRow}", $anggota->isEmpty() ? 0 : "=SUM(C{$dataStartRow}:C{$dataEndRow})");
        $target->setCellValue("D{$totalRow}", null);
        $target->setCellValue("E{$totalRow}", null);

        $tanggalRow = $totalRow + 4;
        $jabatanRow = $tanggalRow + 1;
        $namaRow = $jabatanRow + 4;

        $this->salinBaris($source, $tanggalSourceRow, $target, $tanggalRow);

        foreach (range(0, 4) as $offset) {
            $this->salinBaris($source, $jabatanSourceRow + $offset, $target, $jabatanRow + $offset);
        }

        $tanggalCetakFormatted = $tanggalCetak->translatedFormat('d F Y');

        $target->setCellValue("D{$tanggalRow}", "Grogol, {$tanggalCetakFormatted}");
        $target->setCellValue("B{$jabatanRow}", 'Ketua');
        $target->setCellValue("D{$jabatanRow}", 'Bendahara');
        $target->setCellValue("B{$namaRow}", config('koperasi.ketua', 'PRASTOPO,S.Pd'));
        $target->setCellValue("D{$namaRow}", config('koperasi.bendahara', 'SULASTRI,S.Pd'));

        $target->getPageSetup()
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setPrintArea("A1:E{$namaRow}");

        $target->setSelectedCell('A1');
        $target->setTopLeftCell('A1');
        $target->setPaneTopLeftCell('A1');
    }

    private function bersihkanAreaDinamis(Worksheet $sheet): void
    {
        foreach ($sheet->getMergeCells() as $range) {
            $boundaries = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::rangeBoundaries($range);

            if ($boundaries[0][1] >= 6) {
                $sheet->unmergeCells($range);
            }
        }

        $highestRow = $sheet->getHighestRow();

        if ($highestRow > 5) {
            $sheet->removeRow(6, $highestRow - 5);
        }
    }

    private function tulisHeader(Worksheet $source, Worksheet $target, int $sourceRow, int $targetRow): void
    {
        $this->salinBaris($source, $sourceRow, $target, $targetRow);

        $target->mergeCells("D{$targetRow}:E{$targetRow}");
        $target->setCellValue("A{$targetRow}", 'NO.');
        $target->setCellValue("B{$targetRow}", 'NAMA');
        $target->setCellValue("C{$targetRow}", 'JUMLAH');
        $target->setCellValue("D{$targetRow}", 'TANDA TANGAN');
    }

    private function salinBaris(Worksheet $source, int $sourceRow, Worksheet $target, int $targetRow): void
    {
        $target->getRowDimension($targetRow)->setRowHeight($source->getRowDimension($sourceRow)->getRowHeight());

        foreach (range('A', 'E') as $column) {
            $sourceCoordinate = "{$column}{$sourceRow}";
            $targetCoordinate = "{$column}{$targetRow}";

            $target->duplicateStyle($source->getStyle($sourceCoordinate), $targetCoordinate);
            $target->setCellValue($targetCoordinate, null);
        }
    }

    private function cariBarisHeader(Worksheet $sheet): int
    {
        foreach (range(1, $sheet->getHighestRow()) as $row) {
            if ($sheet->getCell("A{$row}")->getValue() === 'NO.') {
                return $row;
            }
        }

        throw new RuntimeException("Header sheet {$sheet->getTitle()} tidak ditemukan.");
    }

    private function cariBarisTotal(Worksheet $sheet): int
    {
        foreach (range(1, $sheet->getHighestRow()) as $row) {
            $value = (string) $sheet->getCell("C{$row}")->getValue();

            if (str_starts_with($value, '=SUM(')) {
                return $row;
            }
        }

        throw new RuntimeException("Baris total sheet {$sheet->getTitle()} tidak ditemukan.");
    }

    private function cariBarisTanggal(Worksheet $sheet): int
    {
        foreach (range(1, $sheet->getHighestRow()) as $row) {
            $value = strtolower((string) $sheet->getCell("D{$row}")->getValue());

            if (str_contains($value, 'grogol')) {
                return $row;
            }
        }

        throw new RuntimeException("Baris tanggal sheet {$sheet->getTitle()} tidak ditemukan.");
    }

    private function cariBarisJabatan(Worksheet $sheet): int
    {
        foreach (range(1, $sheet->getHighestRow()) as $row) {
            if ($sheet->getCell("B{$row}")->getValue() === 'Ketua') {
                return $row;
            }
        }

        throw new RuntimeException("Baris jabatan sheet {$sheet->getTitle()} tidak ditemukan.");
    }

    private function ambilSheet(Spreadsheet $spreadsheet, string $nama): Worksheet
    {
        $sheet = $spreadsheet->getSheetByName($nama);

        if (!$sheet) {
            throw new RuntimeException("Sheet {$nama} tidak ditemukan pada template.");
        }

        return $sheet;
    }

    private function simpanFile(Spreadsheet $spreadsheet, int $tahun): string
    {
        $folder = storage_path('app/private/exports');

        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $path = $folder . DIRECTORY_SEPARATOR . 'laporan-simpanan-hari-raya-' . $tahun . '-' . Str::uuid() . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(true);
        $writer->save($path);

        return $path;
    }
}
