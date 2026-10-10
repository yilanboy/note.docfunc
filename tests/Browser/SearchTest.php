<?php

declare(strict_types=1);

test('it opens search modal on search button and performs client-side search', function () {
    $page = visit('/');

    $page->wait(0.5)
        ->assertNoJavascriptErrors()
        ->click('button[aria-label="Search notes"]')
        ->waitForText('Search Note Titles & Contents')
        ->assertPresent('input[placeholder*="Search notes"]')
        ->type('input[placeholder*="Search notes"]', 'SpecialKeyword')
        ->waitForText('SpecialKeyword Title')
        ->assertPresent('[data-index="0"] mark');
});

test('it searches chinese terms with highlight and navigates on click', function () {
    $page = visit('/');

    $page->wait(0.5)
        ->assertNoJavascriptErrors()
        ->click('button[aria-label="Search notes"]')
        ->waitForText('Search Note Titles & Contents')
        ->assertPresent('input[placeholder*="Search notes"]')
        ->type('input[placeholder*="Search notes"]', '雙字機制')
        ->waitForText('SpecialKeyword Title')
        ->assertPresent('[data-index="0"] mark')
        ->click('button[data-index="0"]')
        ->waitForText('中文雙字斷詞機制')
        ->assertPathIs('/testing/search-target')
        ->assertNoJavascriptErrors();
});

test('it closes search modal on escape key', function () {
    $page = visit('/');

    $page->wait(0.5)
        ->assertNoJavascriptErrors()
        ->click('button[aria-label="Search notes"]')
        ->waitForText('Search Note Titles & Contents')
        ->assertPresent('input[placeholder*="Search notes"]')
        ->keys('input[placeholder*="Search notes"]', 'Escape')
        ->assertNotPresent('input[placeholder*="Search notes"]');
});
