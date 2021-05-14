<?php

declare(strict_types=1);

namespace App\Service\Export;

use App\DTO\TasksExportDTO;
use App\DTO\TasksExportResponseDTO;
use App\Exception\UnsupportedExportFormatException;
use App\Traits\TasksExportTrait;
use App\Traits\TranslatorInterfaceAwareTrait;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PdfTasksExportService implements TasksExportServiceInterface
{
    use TasksExportTrait;
    use TranslatorInterfaceAwareTrait;

    /** @var string[] */
    private const HEADERS = [
        'ID',
        'Date',
        'Title',
        'Comment',
        'Time spent',
    ];

    private Environment $templateEngine;

    public function __construct(Environment $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /*
     * Supports export if PDF type requested
     */
    public function supports(TasksExportDTO $tasksExportDTO): bool
    {
        return $tasksExportDTO->isPdfExport();
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws UnsupportedExportFormatException
     */
    public function export(TasksExportDTO $tasksExportDTO): TasksExportResponseDTO
    {
        /* prepare export file content */
        $content = $this->createFilledTemplate($tasksExportDTO);
        /* create temporary file with .xlsx extension */
        $filename = $this->createFilename(self::TYPE_PDF);
        $tempFile = $this->createTempFile($filename);
        /* write content to file */
        file_put_contents($tempFile, $content);

        return $this->generateResponseDTO($filename, $tempFile);
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createFilledTemplate(TasksExportDTO $tasksExportDTO): ?string
    {
        /* create Options for PDF filler */
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        /* create PDF filler object */
        $domPdf = new Dompdf($options);
        /* fill view with export data */
        $content = $this->templateEngine->render(
            'export/tasks_export.html.twig',
            [
                'headings' => self::HEADERS,
                'tasks' => $tasksExportDTO->getTasks(),
                'totalSpent' => $tasksExportDTO->getTotalTimeSpent(),
            ]
        );
        /* load content to PDF file, set paper size and draw it */
        $domPdf->loadHtml($content);
        $domPdf->setPaper('A4');
        $domPdf->render();

        return $domPdf->output();
    }
}
