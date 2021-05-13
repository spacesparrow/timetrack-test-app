<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Export;

use App\DTO\TasksExportDTO;
use App\Exception\UnsupportedExportFormatException;
use App\Service\Export\CsvTasksExportService;
use ReflectionException;

class CsvTasksExportServiceTest extends GeneralTasksExportServiceTest
{
    /**
     * @covers       \App\Service\Export\CsvTasksExportService::supports
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
            self::TYPE_CSV => [$csvExport, true],
            self::TYPE_XLSX => [$xlsxExport, false],
        ];
    }

    /**
     * @covers \App\Service\Export\CsvTasksExportService::export
     * @throws ReflectionException
     * @throws UnsupportedExportFormatException
     */
    public function testExport(): void
    {
        $exportDTO = $this->getTestingData(self::TYPE_CSV);

        $this->performExpectations();

        $exportResponseDTO = $this->getTestingService()->export($exportDTO);

        static::assertSame('tasks_export.' . self::TYPE_CSV, $exportResponseDTO->getFilename());
        static::assertStringContainsString($exportResponseDTO->getFilename(), $exportResponseDTO->getTempFile());
        static::assertStringContainsString('tmp', $exportResponseDTO->getTempFile());
    }

    /**
     * @return CsvTasksExportService
     */
    protected function getTestingService(): CsvTasksExportService
    {
        $service = new CsvTasksExportService();
        $service->setTranslator($this->translatorMock);

        return $service;
    }
}