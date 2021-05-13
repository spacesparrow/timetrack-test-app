<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Export;

use App\DTO\TasksExportDTO;
use App\Exception\UnsupportedExportFormatException;
use App\Service\Export\PdfTasksExportService;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PdfTasksExportServiceTest extends GeneralTasksExportServiceTest
{
    /** @var Environment|MockObject */
    private $templateEngineMock;

    /**
     * @covers \App\Service\Export\PdfTasksExportService::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        $exportService = $this->getTestingService();

        static::assertInstanceOf(
            Environment::class,
            $this->getNonPublicPropertyValue($exportService, 'templateEngine')
        );
    }

    /**
     * @covers       \App\Service\Export\PdfTasksExportService::supports
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
            self::TYPE_PDF => [$pdfExport, true],
            self::TYPE_CSV => [$csvExport, false],
            self::TYPE_XLSX => [$xlsxExport, false],
        ];
    }

    /**
     * @covers \App\Service\Export\PdfTasksExportService::export
     * @covers \App\Service\Export\PdfTasksExportService::createFilledTemplate
     * @throws UnsupportedExportFormatException
     * @throws ReflectionException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function testExport(): void
    {
        $exportDTO = $this->getTestingData(self::TYPE_PDF);

        $exportResponseDTO = $this->getTestingService()->export($exportDTO);

        static::assertSame('tasks_export.' . self::TYPE_PDF, $exportResponseDTO->getFilename());
        static::assertStringContainsString($exportResponseDTO->getFilename(), $exportResponseDTO->getTempFile());
        static::assertStringContainsString('tmp', $exportResponseDTO->getTempFile());
    }

    /**
     * @return PdfTasksExportService
     */
    protected function getTestingService(): PdfTasksExportService
    {
        $service = new PdfTasksExportService($this->templateEngineMock);
        $service->setTranslator($this->translatorMock);

        return $service;
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->templateEngineMock = $this->createMock(Environment::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        unset($this->templateEngineMock);

        parent::tearDown();
    }
}