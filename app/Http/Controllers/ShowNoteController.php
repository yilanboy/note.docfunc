<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MarkdownConverter;
use App\Services\NoteRepository;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class ShowNoteController extends Controller
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
        private readonly MarkdownConverter $markdownConverter,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(string $category, string $note): Response
    {
        $found = $this->noteRepository->find($category, $note);

        abort_unless($found !== null, 404);

        $html = Cache::remember(
            'markdown:'.$found['path'].':'.filemtime($found['path']),
            now()->addWeek(),
            fn (): string => $this->markdownConverter->convert(file_get_contents($found['path'])),
        );

        return Inertia::render('Page', [
            'title' => $found['title'],
            'html' => $html,
        ]);
    }
}
