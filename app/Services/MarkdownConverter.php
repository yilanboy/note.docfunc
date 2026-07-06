<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

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
     * Convert a Markdown file into HTML, cached until the file changes.
     */
    public function convertFile(string $path): string
    {
        return $this->convert(file_get_contents($path));
    }
}
