<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MarkdownConverter;
use App\Services\NoteRepository;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class ShowCategoryController extends Controller
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
        private readonly MarkdownConverter $markdownConverter,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(string $category): Response
    {
        $readme = config('notes.path')."/{$category}/README.md";

        abort_unless(file_exists($readme), 404);

        $displayName = $this->noteRepository->displayName($category);

        $cached = Cache::remember(
            'markdown:'.$readme.':'.filemtime($readme),
            now()->addWeek(),
            fn (): array => $this->markdownConverter->parse(file_get_contents($readme)),
        );

        return Inertia::render('Page', [
            'title' => $displayName,
            'html' => $cached['html'],
            'metadata' => $cached['metadata'],
        ]);
    }
}
