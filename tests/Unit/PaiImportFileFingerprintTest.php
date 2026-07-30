<?php

namespace Tests\Unit;

use App\Services\PaiImportFileIdempotencyService;
use App\Services\PaiImportRetryPolicy;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;

class PaiImportFileFingerprintTest extends TestCase
{
    public function test_binary_fingerprint_depends_on_content_not_filename(): void
    {
        $first = tempnam(sys_get_temp_dir(), 'pai_first_');
        $second = tempnam(sys_get_temp_dir(), 'pai_second_');
        $different = tempnam(sys_get_temp_dir(), 'pai_other_');

        try {
            file_put_contents($first, 'same excel bytes');
            file_put_contents($second, 'same excel bytes');
            file_put_contents($different, 'different excel bytes');

            $service = new PaiImportFileIdempotencyService(new PaiImportRetryPolicy());
            $firstFingerprint = $service->fingerprint($first);
            $secondFingerprint = $service->fingerprint($second);
            $differentFingerprint = $service->fingerprint($different);

            $this->assertSame($firstFingerprint, $secondFingerprint);
            $this->assertNotSame($firstFingerprint['sha256'], $differentFingerprint['sha256']);
            $this->assertSame(64, strlen($firstFingerprint['sha256']));
        } finally {
            @unlink($first);
            @unlink($second);
            @unlink($different);
        }
    }

    public function test_semantic_fingerprint_ignores_workbook_metadata(): void
    {
        $first = tempnam(sys_get_temp_dir(), 'pai_first_') . '.xlsx';
        $second = tempnam(sys_get_temp_dir(), 'pai_second_') . '.xlsx';

        try {
            $this->writeWorkbook($first, 'first author');
            $this->writeWorkbook($second, 'second author');

            $service = new PaiImportFileIdempotencyService(new PaiImportRetryPolicy());
            $firstFingerprint = $service->fingerprint($first);
            $secondFingerprint = $service->fingerprint($second);

            $this->assertNotSame($firstFingerprint['sha256'], $secondFingerprint['sha256']);
            $this->assertSame($firstFingerprint['content_sha256'], $secondFingerprint['content_sha256']);
            $this->assertSame(64, strlen((string) $firstFingerprint['content_sha256']));
            $this->assertSame(3, $firstFingerprint['content_rows']);
            $this->assertSame(2, $firstFingerprint['content_columns']);
        } finally {
            @unlink($first);
            @unlink($second);
        }
    }

    private function writeWorkbook(string $path, string $creator): void
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator($creator);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'encabezado');
        $sheet->setCellValue('B1', 'valor');
        $sheet->setCellValue('A2', 'MUJER');
        $sheet->setCellValue('B2', 123);
        $sheet->setCellValue('A3', 'GESTANTE');

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();
    }
}
