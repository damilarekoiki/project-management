<?php

namespace App\DTOs;

use Carbon\CarbonImmutable;

class TaskFilterDto
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?CarbonImmutable $due_date = null,
    ) {}

    /**
     * Check if any filter is applied.
     */
    public function hasFilters(): bool
    {
        return filled($this->status) || filled($this->due_date);
    }
}
