<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ResolveDescription;
use App\Services\MarkdownConverter;
use App\Services\NoteRepository;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Facades\Head;

class ShowNoteController extends Controller
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
        private readonly MarkdownConverter $markdownConverter,
        private readonly ResolveDescription $resolveDescription,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(string $category, string $note): Response
    {
        $found = $this->noteRepository->find($category, $note);

        abort_unless($found !== null, 404);

        $cached = Cache::remember(
            'markdown:'.$found['path'].':'.filemtime($found['path']),
            now()->addWeek(),
            fn (): array => $this->markdownConverter->parse(file_get_contents($found['path'])),
        );

        $description = $this->resolveDescription->handle($cached['html']);

        Head::title($found['title'])
            ->description($description)
            ->og(
                type: OgType::Article,
                title: $found['title'],
                description: $description,
            );

        Head::ogImage(route('notes.note.og', ['category' => $category, 'note' => $note]));

        return Inertia::render('Page', [
            'title' => $found['title'],
            'html' => $cached['html'],
            'metadata' => $cached['metadata'],
        ]);
    }
}
