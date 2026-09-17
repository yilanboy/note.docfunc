<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ResolveDescription;
use App\Services\MarkdownConverter;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Facades\Head;

class ShowHomeController extends Controller
{
    public function __construct(
        private readonly MarkdownConverter $markdownConverter,
        private readonly ResolveDescription $resolveDescription,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        $path = config('notes.path').'/README.md';

        abort_unless(file_exists($path), 404);

        $cached = Cache::remember(
            'markdown:'.$path.':'.filemtime($path),
            now()->addWeek(),
            fn (): array => $this->markdownConverter->parse(file_get_contents($path)),
        );

        $description = $this->resolveDescription->handle($cached['html']);

        Head::title(config('app.name'), exact: true)
            ->description($description)
            ->og(
                type: OgType::Website,
                title: config('app.name'),
                description: $description,
            );

        return Inertia::render('Page', [
            'title' => config('app.name'),
            'html' => $cached['html'],
            'metadata' => $cached['metadata'],
        ]);
    }
}
