<?php

declare(strict_types=1);

namespace App\DTO;

class TasksExportResponseDTO
{
    /** @var string  */
    private string $filename;

    /** @var string  */
    private string $tempFile;

    /**
     * TasksExportResponseDTO constructor.
     * @param string $filename
     * @param string $tempFile
     */
    public function __construct(string $filename, string $tempFile)
    {
        $this->filename = $filename;
        $this->tempFile = $tempFile;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getTempFile(): string
    {
        return $this->tempFile;
    }

    /**
     * @param string $tempFile
     */
    public function setTempFile(string $tempFile): void
    {
        $this->tempFile = $tempFile;
    }
}