<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\Cache;
use Yilanboy\Preview\Canvas\Enums\Format;
use Yilanboy\Preview\Canvas\Enums\GradientDirection;
use Yilanboy\Preview\Canvas\Gradient;
use Yilanboy\Preview\Generator;
use Yilanboy\Preview\Text\Enums\Font;
use Yilanboy\Preview\Text\Enums\FontSize;
use Yilanboy\Preview\Text\TextBlock;

final readonly class GenerateNoteOgImage
{
    /**
     * Generate and cache the WebP Open Graph image bytes and MIME type for a note.
     *
     * @return array{bytes: string, mimeType: string}
     */
    public function handle(string $path, string $title): array
    {
        $format = Format::WEBP;

        /** @var string $bytes */
        $bytes = Cache::remember(
            'og:note:'.$path.':'.(string) filemtime($path),
            now()->addWeek(),
            static fn (): string => (new Generator)
                ->format($format)
                ->background(new Gradient(from: '#00bc7d', to: '#00b8db', direction: GradientDirection::Diagonal))
                ->title(new TextBlock(
                    text: $title,
                    color: 'white',
                    fontSize: FontSize::Medium,
                    font: Font::NotoSansTCMedium,
                ))
                ->bytes(),
        );

        return [
            'bytes' => $bytes,
            'mimeType' => $format->mimeType(),
        ];
    }
}
