<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Str;

class ResolveDescription
{
    /**
     * Resolve a plain text description for document head and Open Graph from HTML.
     */
    public function handle(string $html): string
    {
        $text = strip_tags(preg_replace('/<h[1-6][^>]*>.*?<\/h[1-6]>/si', '', $html) ?? '');
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? '';

        return $text !== '' ? Str::limit($text, 160) : (string) config('app.name');
    }
}
