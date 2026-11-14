<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class MarkdownReadException extends Exception
{
    public function __construct(
        public readonly string $path,
    ) {
        parent::__construct("Failed to read markdown file at $path");
    }
}
