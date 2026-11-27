<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\LoadMarkdownPage;
use App\Services\NoteRepository;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Facades\Head;

class ShowNoteController extends Controller
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
        private readonly LoadMarkdownPage $loadMarkdownPage,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(string $category, string $note): Response
    {
        $found = $this->noteRepository->find($category, $note);

        abort_unless($found !== null, 404);

        $page = $this->loadMarkdownPage->handle($found['path']);

        abort_unless($page !== null, 404);

        Head::title($found['title'])
            ->description($page['description'])
            ->og(
                type: OgType::Article,
                title: $found['title'],
                description: $page['description'],
            );

        Head::ogImage(route('notes.note.og', ['category' => $category, 'note' => $note]));

        return Inertia::render('Page', [
            'title' => $found['title'],
            'html' => $page['html'],
            'metadata' => $page['metadata'],
        ]);
    }
}
