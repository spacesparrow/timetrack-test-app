<?php

declare(strict_types=1);

namespace App\DTO;

class PaginatedRequestDTO
{
    private ?int $page = null;

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): void
    {
        $this->page = $page;
    }
}
