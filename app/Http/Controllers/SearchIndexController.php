<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\BuildSearchIndex;
use App\Services\NoteRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchIndexController extends Controller
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
        private readonly BuildSearchIndex $buildSearchIndex,
    ) {}

    /**
     * Return search index JSON with HTTP ETag caching.
     */
    public function __invoke(Request $request): Response
    {
        $etag = hash('xxh128', $this->noteRepository->fingerprint());

        if (
            in_array('"'.$etag.'"', $request->getETags(), true)
            || in_array('W/"'.$etag.'"', $request->getETags(), true)
        ) {
            return response('', Response::HTTP_NOT_MODIFIED, [
                'ETag' => '"'.$etag.'"',
                'Cache-Control' => 'public, max-age=0, must-revalidate',
            ]);
        }

        $response = response()->json($this->buildSearchIndex->handle());
        $response->setEtag($etag);
        $response->headers->set('Cache-Control', 'public, max-age=0, must-revalidate');

        return $response;
    }
}
