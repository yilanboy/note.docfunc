<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\LoadMarkdownPage;
use App\Services\NoteRepository;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Facades\Head;

class ShowCategoryController extends Controller
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
        private readonly LoadMarkdownPage $loadMarkdownPage,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(string $category): Response
    {
        $page = $this->loadMarkdownPage->handle(config('notes.path')."/{$category}/README.md");

        abort_unless($page !== null, 404);

        $displayName = $this->noteRepository->displayName($category);

        Head::title($displayName)
            ->description($page['description'])
            ->og(
                type: OgType::Website,
                title: $displayName,
                description: $page['description'],
            );

        return Inertia::render('Page', [
            'title' => $displayName,
            'html' => $page['html'],
            'metadata' => $page['metadata'],
        ]);
    }
}
