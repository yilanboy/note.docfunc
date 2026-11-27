<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\MarkdownReadException;
use App\Services\NoteRepository;
use Illuminate\Support\Facades\Cache;

final readonly class BuildSearchIndex
{
    public function __construct(
        private NoteRepository $noteRepository,
        private ExtractSearchableContent $extractSearchableContent,
    ) {}

    /**
     * Build a cached search index of all notes.
     *
     * @return list<array{category: string, categoryName: string, slug: string, title: string, content: string}>
     *
     * @throws MarkdownReadException
     */
    public function handle(): array
    {
        /** @var list<array{category: string, categoryName: string, slug: string, title: string, content: string}> */
        return Cache::remember(
            'notes:search_index:'.$this->noteRepository->fingerprint(),
            now()->addWeek(),
            function (): array {
                $index = [];

                foreach ($this->noteRepository->all() as $note) {
                    $contents = file_get_contents($note['path']);

                    if ($contents === false) {
                        throw new MarkdownReadException($note['path']);
                    }

                    $index[] = [
                        'category' => $note['category'],
                        'categoryName' => $note['categoryName'],
                        'slug' => $note['slug'],
                        'title' => $note['title'],
                        'content' => $this->extractSearchableContent->handle($contents),
                    ];
                }

                return $index;
            },
        );
    }
}
