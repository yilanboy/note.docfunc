<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\NoteRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchIndexController extends Controller
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
    ) {}

    /**
     * Return search index JSON with HTTP ETag caching.
     */
    public function __invoke(Request $request): Response
    {
        $etag = md5($this->noteRepository->fingerprint());

        if (
            in_array('"'.$etag.'"', $request->getETags(), true) ||
            in_array('W/"'.$etag.'"', $request->getETags(), true)
        ) {
            return response('', Response::HTTP_NOT_MODIFIED, [
                'ETag' => '"'.$etag.'"',
                'Cache-Control' => 'public, max-age=0, must-revalidate',
            ]);
        }

        $response = response()->json($this->noteRepository->searchIndex());
        $response->setEtag($etag);
        $response->headers->set('Cache-Control', 'public, max-age=0, must-revalidate');

        return $response;
    }
}
