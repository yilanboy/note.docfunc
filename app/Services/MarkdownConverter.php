<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\MarkdownReadException;
use Illuminate\Support\Str;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class MarkdownConverter
{
    /**
     * Convert GitHub flavored Markdown into sanitized HTML.
     *
     * Code blocks are emitted as plain `<pre><code class="language-x">` and
     * highlighted client-side with Shiki (see resources/js/shared/highlight.ts).
     */
    public function convert(string $markdown): string
    {
        return Str::of($markdown)
            ->markdown([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
                'max_nesting_level' => 10,
                'external_link' => [
                    'internal_hosts' => parse_url(config('app.url'), PHP_URL_HOST),
                    'open_in_new_window' => true,
                    'nofollow' => 'external',
                    'noopener' => 'external',
                    'noreferrer' => 'external',
                ],
            ])
            ->toString();
    }

    /**
     * Parse a Markdown string into metadata, content, and HTML.
     *
     * @return array{metadata: array<string, mixed>, content: string, html: string}
     */
    public function parse(string $markdown): array
    {
        $document = YamlFrontMatter::parse($markdown);

        /** @var array<string, mixed> $metadata */
        $metadata = $document->matter();

        return [
            'metadata' => $metadata,
            'content' => $document->body(),
            'html' => $this->convert($document->body()),
        ];
    }

    /**
     * Parse a Markdown file into metadata, content, and HTML.
     *
     * @return array{metadata: array<string, mixed>, content: string, html: string}
     *
     * @throws MarkdownReadException
     */
    public function parseFile(string $path): array
    {
        throw_if(! is_file($path) || ! is_readable($path), MarkdownReadException::class, $path);

        $contents = file_get_contents($path);

        throw_if($contents === false, MarkdownReadException::class, $path);

        return $this->parse($contents);
    }
}
