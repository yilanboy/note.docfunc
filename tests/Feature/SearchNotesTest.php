<?php

it('returns an empty array when no query is provided', function () {
    $response = $this->getJson('/search');

    $response->assertStatus(200)
        ->assertJson([]);
});

it('returns search results matching a query', function () {
    $response = $this->getJson('/search?q=Boost');

    $response->assertStatus(200)
        ->assertJsonFragment([
            'category' => 'laravel',
            'categoryName' => 'Laravel',
            'slug' => 'laravel-boost',
            'title' => 'Laravel Boost',
        ]);
});

it('prioritizes title matches over content matches', function () {
    $response = $this->getJson('/search?q=Boost');

    $response->assertStatus(200);
    $data = $response->json();

    expect($data)->not->toBeEmpty();
    // First result should have a high score because "Boost" is in the title
    expect($data[0]['title'])->toContain('Boost');
});
