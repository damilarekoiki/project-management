<?php

namespace App\DTOs;

class TaskFilterDto
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?string $due_date = null,
    ) {}

    /**
     * Check if any filter is applied.
     */
    public function hasFilters(): bool
    {
        return $this->status !== null || $this->due_date !== null;
    }
}
