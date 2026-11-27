<?php

declare(strict_types=1);

use App\Actions\BuildSearchIndex;
use App\Services\NoteRepository;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;
use Tests\TestCase;

pest()->extend(TestCase::class);

it('builds search index entries with expected fields and excludes README files', function () {
    Cache::flush();

    $index = app(BuildSearchIndex::class)->handle();

    expect($index)->toBeArray()->not->toBeEmpty();

    $slugs = array_column($index, 'slug');
    expect($slugs)->not->toContain('README');

    $target = collect($index)->firstWhere('slug', 'search-target');

    expect($target)->toBe([
        'category' => 'testing',
        'categoryName' => 'Testing',
        'slug' => 'search-target',
        'title' => 'SpecialKeyword Title',
        'content' => "這是一篇專門用於測試搜尋引擎中文雙字斷詞機制的說明文件。\nSearch target primary note for testing query ranking and normalization.",
    ]);
});

it('caches the search index by repository fingerprint', function () {
    Cache::flush();

    $fixturePath = base_path('tests/Fixtures/notes/testing/04-search-target.md');
    $cacheKey = 'notes:search_index:test-fingerprint';

    $this->mock(NoteRepository::class, function (MockInterface $mock) use ($fixturePath): void {
        $mock->shouldReceive('fingerprint')
            ->twice()
            ->andReturn('test-fingerprint');

        $mock->shouldReceive('all')
            ->once()
            ->andReturn([
                [
                    'path' => $fixturePath,
                    'category' => 'testing',
                    'categoryName' => 'Testing',
                    'slug' => 'search-target',
                    'title' => 'SpecialKeyword Title',
                    'order' => 4,
                ],
            ]);
    });

    $action = app(BuildSearchIndex::class);

    $first = $action->handle();
    $second = $action->handle();

    expect(Cache::has($cacheKey))->toBeTrue()
        ->and($second)->toBe($first)
        ->and($first)->toHaveCount(1);
});
