<?php

test('smoke test', function () {
    $page = visit('/');

    $page->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('it renders note metadata with date and tags', function () {
    $page = visit('/testing/code-blocks');

    $page->assertNoJavascriptErrors()
        ->assertSee('建立於 2026-09-03')
        ->assertSee('# testing');
});

test('it renders note metadata with updated date', function () {
    $page = visit('/testing/mermaid');

    $page->assertNoJavascriptErrors()
        ->assertSee('建立於 2026-09-03')
        ->assertSee('更新於 2026-09-04')
        ->assertSee('# testing');
});

test('it renders category metadata from category readme', function () {
    $page = visit('/testing');

    $page->assertNoJavascriptErrors()
        ->assertSee('建立於 2026-09-03')
        ->assertSee('# testing');
});
