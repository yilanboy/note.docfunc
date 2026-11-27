<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GenerateNoteOgImage;
use App\Services\NoteRepository;
use Illuminate\Http\Response;

class ShowNoteOgImageController extends Controller
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
        private readonly GenerateNoteOgImage $generateNoteOgImage,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(string $category, string $note): Response
    {
        $found = $this->noteRepository->find($category, $note);

        abort_unless($found !== null, 404);

        $image = $this->generateNoteOgImage->handle($found['path'], $found['title']);

        return response($image['bytes'], 200, [
            'Content-Type' => $image['mimeType'],
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
