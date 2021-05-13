<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Export;

use App\DTO\TasksExportDTO;
use App\Exception\UnsupportedExportFormatException;
use App\Service\Export\ExcelTasksExportService;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use ReflectionException;

class ExcelTasksExportServiceTest extends GeneralTasksExportServiceTest
{
    /**
     * @covers       \App\Service\Export\ExcelTasksExportService::supports
     * @dataProvider dataProviderForSupportsTesting
     * @param TasksExportDTO $exportDTO
     * @param bool $expected
     */
    public function testSupports(TasksExportDTO $exportDTO, bool $expected): void
    {
        $exportService = $this->getTestingService();

        static::assertSame($expected, $exportService->supports($exportDTO));
    }

    /**
     * @return array[]
     */
    public function dataProviderForSupportsTesting(): array
    {
        $pdfExport = new TasksExportDTO();
        $pdfExport->setType(self::TYPE_PDF);
        $csvExport = new TasksExportDTO();
        $csvExport->setType(self::TYPE_CSV);
        $xlsxExport = new TasksExportDTO();
        $xlsxExport->setType(self::TYPE_XLSX);

        return [
            self::TYPE_PDF => [$pdfExport, false],
            self::TYPE_CSV => [$csvExport, false],
            self::TYPE_XLSX => [$xlsxExport, true],
        ];
    }

    /**
     * @covers \App\Service\Export\ExcelTasksExportService::export
     * @throws ReflectionException
     * @throws UnsupportedExportFormatException
     * @throws Exception
     */
    public function testExport(): void
    {
        $exportDTO = $this->getTestingData(self::TYPE_XLSX);

        $this->performExpectations();

        $exportResponseDTO = $this->getTestingService()->export($exportDTO);

        static::assertSame('tasks_export.' . self::TYPE_XLSX, $exportResponseDTO->getFilename());
        static::assertStringContainsString($exportResponseDTO->getFilename(), $exportResponseDTO->getTempFile());
        static::assertStringContainsString('tmp', $exportResponseDTO->getTempFile());
    }

    /**
     * @return ExcelTasksExportService
     */
    protected function getTestingService(): ExcelTasksExportService
    {
        $service = new ExcelTasksExportService();
        $service->setTranslator($this->translatorMock);

        return $service;
    }
}