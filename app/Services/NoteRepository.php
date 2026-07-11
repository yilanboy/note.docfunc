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
     * @return array<int, array{slug: string, displayName: string, notes: array<int, array{slug: string, title: string}>}>
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
     * @return array<int, array{slug: string, title: string}>
     */
    public function notes(string $category): array
    {
        return collect(glob(config('notes.path')."/{$category}/*.md"))
            ->reject(fn (string $path): bool => basename($path) === 'README.md')
            ->map(fn (string $path): array => [
                'slug' => $this->slug($path),
                'title' => $this->title($path),
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
     * @return array{path: string, slug: string, title: string}|null
     */
    public function find(string $category, string $slug): ?array
    {
        foreach (glob(config('notes.path')."/{$category}/*.md") as $path) {
            if (basename($path) !== 'README.md' && $this->slug($path) === $slug) {
                return [
                    'path' => $path,
                    'slug' => $slug,
                    'title' => $this->title($path),
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
     * Search notes by keyword in title and content.
     *
     * @return array<int, array{category: string, categoryName: string, slug: string, title: string, snippet: string}>
     */
    public function search(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $terms = array_filter(explode(' ', $query));
        if (empty($terms)) {
            return [];
        }

        $results = [];
        $searchIndex = $this->getSearchIndex();

        foreach ($searchIndex as $note) {
            $score = 0;
            $titleMatched = true;
            $contentMatched = true;

            // Check if all terms exist in the title (logical AND)
            foreach ($terms as $term) {
                if (str_contains($note['title'], $term) === false) {
                    $titleMatched = false;
                    break;
                }
            }

            // Check if all terms exist in the content (logical AND)
            foreach ($terms as $term) {
                if (str_contains($note['content'], $term) === false) {
                    $contentMatched = false;
                    break;
                }
            }

            if ($titleMatched) {
                $score += 10; // Prioritize title matches heavily
            }

            if ($contentMatched) {
                $score += 1;
            }

            if ($titleMatched || $contentMatched) {
                $results[] = [
                    'category' => $note['category'],
                    'categoryName' => $note['categoryName'],
                    'slug' => $note['slug'],
                    'title' => $note['title'],
                    'snippet' => $this->generateSnippet($note['content'], $query),
                    'score' => $score,
                ];
            }
        }

        // Sort by search score descending
        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_values($results);
    }

    /**
     * Build a cached search index of all notes.
     *
     * @return array<int, array{category: string, categoryName: string, slug: string, title: string, content: string}>
     */
    private function getSearchIndex(): array
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
                    $content = file_get_contents($path);

                    $index[] = [
                        'category' => $category,
                        'categoryName' => $this->displayName($category),
                        'slug' => $this->slug($path),
                        'title' => $this->title($path),
                        'content' => $content,
                    ];
                }

                return $index;
            }
        );
    }

    /**
     * Extract a text snippet around the search query.
     */
    private function generateSnippet(string $content, string $query): string
    {
        // Strip Markdown headers/formatting characters for a clean text preview
        $clean = preg_replace('/[#*`_\-]/', '', $content);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean);

        $pos = stripos($clean, $query);
        if ($pos === false) {
            return mb_substr($clean, 0, 120).'...';
        }

        $start = max(0, $pos - 40);
        $length = min(mb_strlen($clean) - $start, 120);
        $snippet = mb_substr($clean, $start, $length);

        if ($start > 0) {
            $snippet = '...'.$snippet;
        }
        if ($start + $length < mb_strlen($clean)) {
            $snippet .= '...';
        }

        return $snippet;
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
    private function fingerprint(): string
    {
        $files = glob(config('notes.path').'/*/*.md');

        return count($files).':'.max([0, ...array_map(filemtime(...), $files)]);
    }
}
