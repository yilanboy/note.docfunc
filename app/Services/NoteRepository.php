<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class NoteRepository
{
    /**
     * Get all categories with their notes, ordered by category slug.
     *
     * @return array<int, array{slug: string, displayName: string, notes: array<int, array{slug: string, title: string, order: ?int}>}>
     */
    public function tree(): array
    {
        return Cache::remember(
            'notes:tree:'.$this->fingerprint(),
            now()->addWeek(),
            fn (): array => collect(glob(config('notes.path').'/*', GLOB_ONLYDIR))
                ->map(fn (string $directory): array => [
                    'slug' => basename($directory),
                    'displayName' => $this->displayName(basename($directory)),
                    'notes' => $this->notes(basename($directory)),
                ])
                ->filter(fn (array $category): bool => $category['notes'] !== [])
                ->sortBy('slug')
                ->values()
                ->all(),
        );
    }

    /**
     * Get the notes of a category, ordered by file name (numeric prefix first).
     *
     * @return array<int, array{slug: string, title: string, order: ?int}>
     */
    public function notes(string $category): array
    {
        return collect(glob(config('notes.path')."/{$category}/*.md"))
            ->reject(fn (string $path): bool => basename($path) === 'README.md')
            ->map(fn (string $path): array => [
                'slug' => $this->slug($path),
                'title' => $this->title($path),
                'order' => $this->order($path),
            ])
            ->values()
            ->all();
    }

    /**
     * Find a note file by its category and slug.
     *
     * The slug is matched against the scanned files of the category, so URL
     * input never touches the filesystem path directly.
     *
     * @return array{path: string, slug: string, title: string, order: ?int}|null
     */
    public function find(string $category, string $slug): ?array
    {
        foreach (glob(config('notes.path')."/{$category}/*.md") as $path) {
            if (basename($path) !== 'README.md' && $this->slug($path) === $slug) {
                return [
                    'path' => $path,
                    'slug' => $slug,
                    'title' => $this->title($path),
                    'order' => $this->order($path),
                ];
            }
        }

        return null;
    }

    /**
     * Resolve the display name of a category from config, falling back to headline case.
     */
    public function displayName(string $category): string
    {
        return config("notes.display_names.{$category}") ?? Str::headline($category);
    }

    /**
     * Build a cached search index of all notes.
     *
     * @return array<int, array{category: string, categoryName: string, slug: string, title: string, content: string}>
     */
    public function searchIndex(): array
    {
        return Cache::remember(
            'notes:search_index:'.$this->fingerprint(),
            now()->addWeek(),
            function (): array {
                $index = [];
                $files = glob(config('notes.path').'/*/*.md');

                foreach ($files as $path) {
                    if (basename($path) === 'README.md') {
                        continue;
                    }

                    $category = basename(dirname($path));

                    $index[] = [
                        'category' => $category,
                        'categoryName' => $this->displayName($category),
                        'slug' => $this->slug($path),
                        'title' => $this->title($path),
                        'content' => $this->searchableContent(file_get_contents($path)),
                    ];
                }

                return $index;
            }
        );
    }

    /**
     * Extract clean searchable text from Markdown content.
     */
    public function searchableContent(string $markdown): string
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
        $text = preg_replace('/^#+\s+/m', '', $text ?? '');
        $text = preg_replace('/^>\s+/m', '', $text ?? '');

        // 6. Normalize whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text ?? '');
        $text = preg_replace("/\n\s*\n/", "\n", $text ?? '');

        return trim($text ?? '');
    }

    /**
     * Build the slug of a note file: drop the extension and the numeric sort prefix.
     */
    private function slug(string $path): string
    {
        $name = basename($path, '.md');

        $slug = preg_replace('/^\d+[-.]?/', '', $name);

        return $slug === '' ? $name : $slug;
    }

    /**
     * Extract the numeric sort prefix of a note file, if present.
     */
    private function order(string $path): ?int
    {
        $name = basename($path, '.md');

        if (preg_match('/^(\d+)[-.]?/', $name, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Resolve the note title from its first H1, falling back to the slug.
     */
    private function title(string $path): string
    {
        $handle = fopen($path, 'r');

        try {
            while (($line = fgets($handle)) !== false) {
                if (str_starts_with($line, '# ')) {
                    return trim(mb_substr($line, 2));
                }
            }
        } finally {
            fclose($handle);
        }

        return Str::headline($this->slug($path));
    }

    /**
     * A cheap change detector for the whole notes directory, so the cached
     * tree is rebuilt whenever a note is added, renamed, or edited.
     */
    public function fingerprint(): string
    {
        $files = glob(config('notes.path').'/*/*.md');

        return count($files).':'.max([0, ...array_map(filemtime(...), $files)]);
    }
}
