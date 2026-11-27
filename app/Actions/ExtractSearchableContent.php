<?php

declare(strict_types=1);

namespace App\Actions;

final readonly class ExtractSearchableContent
{
    /**
     * Extract clean searchable text from Markdown content.
     */
    public function handle(string $markdown): string
    {
        // 1. Remove YAML frontmatter
        $text = preg_replace('/^---\s*[\r\n].*?[\r\n]---\s*[\r\n]/s', '', $markdown);

        // 2. Remove first H1 header (title is indexed separately)
        $text = preg_replace('/^#\s+[^\r\n]+[\r\n]+/m', '', $text ?? '', 1);

        // 3. Replace images ![alt](url) with alt
        $text = preg_replace('/!\[([^\]]*)\]\([^)]+\)/', '$1', $text ?? '');

        // 4. Replace links [text](url) with text
        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text ?? '');

        // 5. Remove markdown formatting characters like #, *, _, `, >, etc. but keep code/text
        $text = preg_replace('/```[a-zA-Z0-9_-]*/', '', $text ?? '');
        $text = str_replace(['```', '`', '**', '__', '~~'], '', $text ?? '');
        $text = preg_replace('/^#+\s+/m', '', $text);
        $text = preg_replace('/^>\s+/m', '', $text ?? '');

        // 6. Normalize whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text ?? '');
        $text = preg_replace("/\n\s*\n/", "\n", $text ?? '');

        return trim($text ?? '');
    }
}
