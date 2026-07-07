<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\NoteRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchNotesController extends Controller
{
    public function __construct(private readonly NoteRepository $noteRepository) {}

    /**
     * Search notes and return JSON.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->query('q', '');

        if (! is_string($query)) {
            $query = '';
        }

        $results = $this->noteRepository->search($query);

        return response()->json($results);
    }
}
