<?php

declare(strict_types=1);

namespace App\Service\Export;

use App\DTO\TasksExportDTO;
use App\DTO\TasksExportResponseDTO;
use App\Exception\UnsupportedExportFormatException;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PdfTasksExportService extends BaseTasksExportService implements TasksExportServiceInterface
{
    /** @var Environment */
    private Environment $templateEngine;

    public function __construct(Environment $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * @param TasksExportDTO $tasksExportDTO
     * @return bool
     */
    public function supports(TasksExportDTO $tasksExportDTO): bool
    {
        return $tasksExportDTO->isPdfExport();
    }

    /**
     * @param TasksExportDTO $tasksExportDTO
     * @return TasksExportResponseDTO
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws UnsupportedExportFormatException
     */
    public function export(TasksExportDTO $tasksExportDTO): TasksExportResponseDTO
    {
        $content = $this->createFilledTemplate($tasksExportDTO);
        $filename = $this->createFilename(self::TYPE_PDF);
        $tempFile = $this->createTempFile($filename);
        file_put_contents($tempFile, $content);

        return $this->generateResponseDTO($filename, $tempFile);
    }

    /**
     * @param TasksExportDTO $tasksExportDTO
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function createFilledTemplate(TasksExportDTO $tasksExportDTO): ?string
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $domPdf = new Dompdf($options);
        $content = $this->templateEngine->render(
            'export/tasks_export.html.twig',
            [
                'tasks' => $tasksExportDTO->getTasks(),
                'totalSpent' => $tasksExportDTO->getTotalTimeSpent()
            ]
        );
        $domPdf->loadHtml($content);
        $domPdf->setPaper('A4');
        $domPdf->render();

        return $domPdf->output();
    }
}