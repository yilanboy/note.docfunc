<?php

declare(strict_types=1);

use App\Actions\LoadMarkdownPage;
use App\Exceptions\MarkdownReadException;
use App\Services\MarkdownConverter;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;
use Tests\TestCase;

pest()->extend(TestCase::class);

it('returns html, metadata, and resolved description for a markdown file', function () {
    Cache::flush();

    $path = base_path('tests/Fixtures/notes/testing/04-search-target.md');

    $page = app(LoadMarkdownPage::class)->handle($path);

    expect($page)->not->toBeNull()
        ->and($page['html'])->toContain('<h1>SpecialKeyword Title</h1>')
        ->and($page['html'])->toContain('中文雙字斷詞機制')
        ->and($page['metadata'])->toBe([
            'date' => '2026-09-03',
            'tags' => ['testing'],
        ])
        ->and($page['description'])->toStartWith('這是一篇專門用於測試搜尋引擎中文雙字斷詞機制的說明文件。');
});

it('returns null when the markdown file does not exist', function () {
    $path = base_path('tests/Fixtures/notes/does-not-exist.md');

    $page = app(LoadMarkdownPage::class)->handle($path);

    expect($page)->toBeNull();
});

it('caches parsed markdown by path and mtime', function () {
    Cache::flush();

    $path = base_path('tests/Fixtures/notes/README.md');
    $cacheKey = 'markdown:'.$path.':'.filemtime($path);

    $this->mock(MarkdownConverter::class, function (MockInterface $mock) use ($path): void {
        $mock->shouldReceive('parseFile')
            ->once()
            ->with($path)
            ->andReturn([
                'metadata' => ['date' => '2026-09-03'],
                'content' => 'Cached body',
                'html' => '<h1>Title</h1><p>Cached body paragraph.</p>',
            ]);
    });

    $action = app(LoadMarkdownPage::class);

    $first = $action->handle($path);
    $second = $action->handle($path);

    expect(Cache::has($cacheKey))->toBeTrue()
        ->and($first)->toBe([
            'html' => '<h1>Title</h1><p>Cached body paragraph.</p>',
            'metadata' => ['date' => '2026-09-03'],
            'description' => 'Cached body paragraph.',
        ])
        ->and($second)->toBe($first);
});

it('propagates MarkdownReadException when parsing fails', function () {
    Cache::flush();

    $path = base_path('tests/Fixtures/notes/README.md');

    $this->mock(MarkdownConverter::class, function (MockInterface $mock) use ($path): void {
        $mock->shouldReceive('parseFile')
            ->once()
            ->with($path)
            ->andThrow(new MarkdownReadException($path));
    });

    expect(fn () => app(LoadMarkdownPage::class)->handle($path))
        ->toThrow(MarkdownReadException::class, "Failed to read markdown file at {$path}");
});
