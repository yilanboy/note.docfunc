<?php

declare(strict_types=1);

use App\Exceptions\MarkdownReadException;
use App\Services\MarkdownConverter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;

it('throws MarkdownReadException with file path when file cannot be read', function () {
    $path = '/non-existent/note.md';

    expect(fn () => app(MarkdownConverter::class)->parseFile($path))
        ->toThrow(MarkdownReadException::class, "Failed to read markdown file at {$path}");
});

it('reports MarkdownReadException once with path context and returns 500 without leaking path', function () {
    Cache::flush();
    Log::spy();

    $secretPath = '/secret/internal/path/README.md';

    $this->mock(MarkdownConverter::class, function (MockInterface $mock) use ($secretPath): void {
        $mock->shouldReceive('parseFile')
            ->once()
            ->andThrow(new MarkdownReadException($secretPath));
    });

    $response = $this->get('/');

    $response->assertStatus(500);

    expect($response->getContent())
        ->toBe('Unable to load markdown content.')
        ->not->toContain($secretPath);

    Log::shouldHaveReceived('error')
        ->once()
        ->with("Failed to read markdown file at {$secretPath}", [
            'path' => $secretPath,
        ]);
});
