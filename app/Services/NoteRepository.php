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
            fn(): array => collect(glob(config('notes.path').'/*', GLOB_ONLYDIR))
                ->map(fn(string $directory): array => [
                    'slug' => basename($directory),
                    'displayName' => $this->displayName(basename($directory)),
                    'notes' => $this->notes(basename($directory)),
                ])
                ->filter(fn(array $category): bool => $category['notes'] !== [])
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
            ->reject(fn(string $path): bool => basename($path) === 'README.md')
            ->map(fn(string $path): array => [
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
