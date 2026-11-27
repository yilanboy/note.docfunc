<?php

declare(strict_types=1);

use App\Actions\GenerateNoteOgImage;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

pest()->extend(TestCase::class);

it('generates valid webp bytes and mime type for a note', function () {
    Cache::flush();

    $path = base_path('tests/Fixtures/notes/testing/04-search-target.md');

    $image = app(GenerateNoteOgImage::class)->handle($path, 'SpecialKeyword Title');

    expect($image['mimeType'])->toBe('image/webp')
        ->and($image['bytes'])->toStartWith('RIFF')
        ->and($image['bytes'])->toContain('WEBP');
});

it('caches generated og image bytes by file path and mtime', function () {
    Cache::flush();

    $path = base_path('tests/Fixtures/notes/testing/04-search-target.md');
    $cacheKey = 'og:note:'.$path.':'.filemtime($path);
    $action = app(GenerateNoteOgImage::class);

    $first = $action->handle($path, 'SpecialKeyword Title');

    expect(Cache::has($cacheKey))->toBeTrue()
        ->and(Cache::get($cacheKey))->toBe($first['bytes']);

    Cache::put($cacheKey, 'cached-webp-bytes', now()->addMinute());

    $second = $action->handle($path, 'Different Title');

    expect($second)->toBe([
        'bytes' => 'cached-webp-bytes',
        'mimeType' => 'image/webp',
    ]);
});
