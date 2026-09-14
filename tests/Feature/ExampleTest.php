<?php

use Inertia\Testing\AssertableInertia as Assert;

it('returns a successful response for home page with metadata', function () {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Page')
            ->has('metadata.date')
            ->has('metadata.tags')
        );
});

it('returns category page with metadata', function () {
    $this->get('/testing')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Page')
            ->has('metadata.date')
            ->has('metadata.tags')
        );
});

it('can visit fixture notes with metadata', function () {
    $this->get('/testing/code-blocks')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Page')
            ->has('metadata.date')
            ->has('metadata.tags')
        );
    $this->get('/testing/mermaid')->assertStatus(200);
    $this->get('/testing/images')->assertStatus(200);
});
