<?php

declare(strict_types=1);

use App\Services\NoteRepository;

it('returns 200 with search index containing required note fields', function () {
    $response = $this->getJson('/search-index.json');

    $response->assertStatus(200);

    $data = $response->json();
    expect($data)->toBeArray()->not->toBeEmpty();

    $item = collect($data)->firstWhere('slug', 'search-target');
    expect($item)->not->toBeNull()
        ->and($item['category'])->toBe('testing')
        ->and($item['categoryName'])->toBe('Testing')
        ->and($item['title'])->toBe('SpecialKeyword Title')
        ->and($item['content'])->toContain('中文雙字斷詞機制')
        ->and($item['content'])->not->toContain('tags: [testing]');
});

it('returns an ETag header and Cache-Control', function () {
    $response = $this->getJson('/search-index.json');

    $response->assertStatus(200);

    $etag = $response->headers->get('ETag');
    expect($etag)->not->toBeNull()->not->toBeEmpty()
        ->and($response->headers->get('Cache-Control'))->toContain('must-revalidate');
});

it('returns 304 Not Modified when If-None-Match matches ETag', function () {
    $firstResponse = $this->getJson('/search-index.json');
    $firstResponse->assertStatus(200);

    $etag = $firstResponse->headers->get('ETag');
    expect($etag)->not->toBeNull();

    $secondResponse = $this->withHeaders([
        'If-None-Match' => $etag,
    ])->get('/search-index.json');

    $secondResponse->assertStatus(304);
    expect($secondResponse->getContent())->toBe('')
        ->and($secondResponse->headers->get('ETag'))->toBe($etag);
});

it('returns 200 when If-None-Match does not match', function () {
    $response = $this->withHeaders([
        'If-None-Match' => '"outdated-or-invalid-etag"',
    ])->getJson('/search-index.json');

    $response->assertStatus(200);
    expect($response->json())->toBeArray()->not->toBeEmpty();
});

it('extracts clean searchable content without frontmatter or markdown syntax', function () {
    $repo = app(NoteRepository::class);

    $markdown = <<<'MD'
    ---
    date: '2026-09-03'
    tags: [php, laravel]
    ---

    # Note Title

    Here is a [link](https://example.com) and an image ![sample image](/img.png).

    ```php
    echo "Hello World";
    ```

    ## Section Subtitle
    > Important note blockquote
    MD;

    $clean = $repo->searchableContent($markdown);

    expect($clean)
        ->not->toContain('date:')
        ->not->toContain('tags:')
        ->not->toContain('# Note Title')
        ->not->toContain('https://example.com')
        ->not->toContain('/img.png')
        ->toContain('link')
        ->toContain('sample image')
        ->toContain('echo "Hello World";')
        ->toContain('Section Subtitle')
        ->toContain('Important note blockquote');
});
