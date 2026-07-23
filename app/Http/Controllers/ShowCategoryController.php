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
        abort_unless(is_dir(config('notes.path')."/{$category}"), 404);

        $displayName = $this->noteRepository->displayName($category);

        $readme = config('notes.path')."/{$category}/README.md";

        if (file_exists($readme)) {
            $readmeContent = file_get_contents($readme);

            $html = Cache::remember(
                'markdown:'.$readme.':'.filemtime($readme),
                now()->addWeek(),
                fn (): string => $this->markdownConverter->convert($readmeContent),
            );
        } else {
            $html = $this->markdownConverter->convert("# {$displayName}");
        }

        return Inertia::render('Page', [
            'title' => $displayName,
            'html' => $html,
        ]);
    }
}
