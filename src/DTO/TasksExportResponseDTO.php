<?php

declare(strict_types=1);

namespace App\DTO;

/*
 * Store filename and path to temporary file where content stored
 */
class TasksExportResponseDTO
{
    private string $filename;

    private string $tempFile;

    /**
     * TasksExportResponseDTO constructor.
     */
    public function __construct(string $filename, string $tempFile)
    {
        $this->filename = $filename;
        $this->tempFile = $tempFile;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function getTempFile(): string
    {
        return $this->tempFile;
    }

    public function setTempFile(string $tempFile): void
    {
        $this->tempFile = $tempFile;
    }
}
