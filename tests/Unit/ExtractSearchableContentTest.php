<?php

declare(strict_types=1);

use App\Actions\ExtractSearchableContent;

it('strips frontmatter, first h1, and markdown syntax while preserving readable content', function () {
    $action = new ExtractSearchableContent;

    $markdown = <<<'MD'
    ---
    date: '2026-09-03'
    tags: [php, laravel]
    ---

    # First Heading

    Here is **bold**, __underlined__, ~~struck~~, and `inline_code`.
    Read the [documentation](https://example.com) and see ![diagram alt](/diagram.png).

    ```php
    $value = 42;
    ```

    ## Subheading
    > Quoted guidance
    MD;

    $clean = $action->handle($markdown);

    expect($clean)
        ->not->toContain('date:')
        ->not->toContain('tags:')
        ->not->toContain('# First Heading')
        ->not->toContain('https://example.com')
        ->not->toContain('/diagram.png')
        ->not->toContain('**')
        ->not->toContain('__')
        ->not->toContain('~~')
        ->not->toContain('```')
        ->not->toContain('`')
        ->toContain('bold')
        ->toContain('underlined')
        ->toContain('struck')
        ->toContain('inline_code')
        ->toContain('documentation')
        ->toContain('diagram alt')
        ->toContain('$value = 42;')
        ->toContain('Subheading')
        ->toContain('Quoted guidance');
});

it('normalizes repeated spaces, tabs, and blank lines', function () {
    $action = new ExtractSearchableContent;

    $markdown = "Line   one\t\twith   tabs\n\n\n\nLine two   ";

    expect($action->handle($markdown))->toBe("Line one with tabs\nLine two");
});

it('returns an empty string for empty or frontmatter-only input', function () {
    $action = new ExtractSearchableContent;

    expect($action->handle(''))->toBeString()->toBeEmpty()
        ->and($action->handle("---\ndate: '2026-09-03'\n---\n"))->toBeString()->toBeEmpty();
});

it('preserves plain text input unchanged', function () {
    $action = new ExtractSearchableContent;

    expect($action->handle('Plain searchable text without markdown.'))
        ->toBe('Plain searchable text without markdown.');
});
