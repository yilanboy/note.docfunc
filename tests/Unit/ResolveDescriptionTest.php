<?php

declare(strict_types=1);

use App\Actions\ResolveDescription;
use Tests\TestCase;

pest()->extend(TestCase::class);

it('extracts description from html content', function () {
    $action = app(ResolveDescription::class);

    $html = '<h1>Title Heading</h1><h2>Subtitle</h2><p>This is the first paragraph with some details.</p>';

    $description = $action->handle($html);

    expect($description)->toBe('This is the first paragraph with some details.');
});

it('limits stripped html text to 160 characters plus ellipsis', function () {
    $action = app(ResolveDescription::class);

    $longText = str_repeat('a', 200);
    $html = "<p>{$longText}</p>";

    $description = $action->handle($html);

    expect($description)
        ->toStartWith(str_repeat('a', 160))
        ->toEndWith('...');
});

it('falls back to app name when html has no body text', function () {
    config(['app.name' => 'DocFunc Note']);

    $action = app(ResolveDescription::class);

    $html = '<h1>Only Heading</h1><h2>Another Heading</h2>';

    $description = $action->handle($html);

    expect($description)->toBe('DocFunc Note');
});

it('can be invoked as a callable', function () {
    $action = app(ResolveDescription::class);

    $description = $action('<p>Callable test paragraph</p>');

    expect($description)->toBe('Callable test paragraph');
});
