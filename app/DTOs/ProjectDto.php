<?php

namespace App\DTOs;

class ProjectDto
{
    public function __construct(
        public readonly int $creator_id,
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?string $deadline = null,
    ) {}
}
