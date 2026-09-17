<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\NoteRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Yilanboy\Preview\Canvas\Enums\Format;
use Yilanboy\Preview\Canvas\Enums\GradientDirection;
use Yilanboy\Preview\Canvas\Gradient;
use Yilanboy\Preview\Generator;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\FontSize;
use Yilanboy\Preview\Text\TextBlock;

class ShowNoteOgImageController extends Controller
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(string $category, string $note): Response
    {
        $found = $this->noteRepository->find($category, $note);

        abort_unless($found !== null, 404);

        $format = Format::WEBP;

        $image = Cache::remember(
            'og:note:'.$found['path'].':'.filemtime($found['path']),
            now()->addWeek(),
            fn (): string => (new Generator)
                ->format($format)
                ->background(new Gradient(from: '#00bc7d', to: '#00b8db', direction: GradientDirection::Diagonal))
                ->title(new TextBlock(
                    text: $found['title'],
                    color: 'white',
                    fontSize: FontSize::Medium,
                    font: Font::NotoSansTCMedium
                ))
                ->bytes(),
        );

        return response($image, 200, [
            'Content-Type' => $format->mimeType(),
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
