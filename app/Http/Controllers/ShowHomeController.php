<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\LoadMarkdownPage;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Facades\Head;

class ShowHomeController extends Controller
{
    public function __construct(
        private readonly LoadMarkdownPage $loadMarkdownPage,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        $page = $this->loadMarkdownPage->handle(config('notes.path').'/README.md');

        abort_unless($page !== null, 404);

        Head::title(config('app.name'), exact: true)
            ->description($page['description'])
            ->og(
                type: OgType::Website,
                title: config('app.name'),
                description: $page['description'],
            );

        return Inertia::render('Page', [
            'title' => config('app.name'),
            'html' => $page['html'],
            'metadata' => $page['metadata'],
        ]);
    }
}
