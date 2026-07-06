<?php

test('smoke test', function () {
    $page = visit('/');

    $page->assertNoSmoke()
        ->assertNoJavascriptErrors();
});
