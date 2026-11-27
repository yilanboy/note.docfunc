<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\MarkdownReadException;
use App\Services\MarkdownConverter;
use Illuminate\Support\Facades\Cache;

final readonly class LoadMarkdownPage
{
    public function __construct(
        private MarkdownConverter $markdownConverter,
        private ResolveDescription $resolveDescription,
    ) {}

    /**
     * Load, parse, and cache a Markdown page along with its resolved meta description.
     *
     * @return array{html: string, metadata: array<string, mixed>, description: string}|null
     *
     * @throws MarkdownReadException
     */
    public function handle(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        /** @var array{metadata: array<string, mixed>, content: string, html: string} $cached */
        $cached = Cache::remember(
            'markdown:'.$path.':'.(string) filemtime($path),
            now()->addWeek(),
            fn (): array => $this->markdownConverter->parseFile($path),
        );

        return [
            'html' => $cached['html'],
            'metadata' => $cached['metadata'],
            'description' => $this->resolveDescription->handle($cached['html']),
        ];
    }
}
