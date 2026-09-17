<?php

use App\Services\NoteRepository;

beforeEach(function () {
    config(['notes.path' => resource_path('notes')]);
});

it('can visit all category pages and returns 200', function () {
    $paths = glob(resource_path('notes').'/*', GLOB_ONLYDIR);

    foreach ($paths as $path) {
        $category = basename($path);
        $response = $this->get(route('notes.category', ['category' => $category]));

        $response->assertStatus(200);
    }
});

it('can visit all note pages and returns 200', function () {
    $repository = app(NoteRepository::class);
    $tree = $repository->tree();

    expect($tree)->not->toBeEmpty();

    foreach ($tree as $category) {
        foreach ($category['notes'] as $note) {
            $response = $this->get("/{$category['slug']}/{$note['slug']}");

            $response->assertStatus(200);
        }
    }
});

it('extracts note order from numeric file prefix', function () {
    $repository = app(NoteRepository::class);
    $tree = $repository->tree();

    $awsCategory = collect($tree)->firstWhere('slug', 'aws');
    expect($awsCategory)->not->toBeNull();

    $awsCli = collect($awsCategory['notes'])->firstWhere('slug', 'aws-cli');
    expect($awsCli)->not->toBeNull()
        ->and($awsCli['order'])->toBe(1);

    $egressGateway = collect($awsCategory['notes'])->firstWhere('slug', 'egress-only-gateway-introduction');
    expect($egressGateway)->not->toBeNull()
        ->and($egressGateway['order'])->toBe(2);
});

it('renders open graph and head metadata for home page', function () {
    $appName = (string) config('app.name');
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('<meta data-inertia="og:type" property="og:type" content="website">', false)
        ->assertSee('<meta data-inertia="og:site_name" property="og:site_name" content="'.$appName.'">', false)
        ->assertSee('<meta data-inertia="twitter:card" name="twitter:card" content="summary_large_image">', false)
        ->assertSee('<link data-inertia="canonical" rel="canonical"', false);
});

it('renders open graph metadata for note pages', function () {
    $appName = (string) config('app.name');
    $response = $this->get('/aws/aws-cli');

    $response->assertStatus(200)
        ->assertSee('<meta data-inertia="og:type" property="og:type" content="article">', false)
        ->assertSee('<meta data-inertia="og:title" property="og:title" content="AWS CLI">', false)
        ->assertSee('<meta data-inertia="twitter:title" name="twitter:title" content="AWS CLI - '.$appName.'">', false)
        ->assertSee('property="og:image" content="'.route('notes.note.og', ['category' => 'aws', 'note' => 'aws-cli']).'"', false)
        ->assertSee('<meta data-inertia="twitter:image" name="twitter:image" content="'.route('notes.note.og', ['category' => 'aws', 'note' => 'aws-cli']).'">', false);
});

it('generates og image for note pages', function () {
    $response = $this->get('/aws/aws-cli/og.webp');

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'image/webp');

    expect($response->getContent())
        ->toStartWith('RIFF')
        ->toContain('WEBP');
});

it('returns 404 for non-existent note og image', function () {
    $response = $this->get('/aws/non-existent/og.webp');

    $response->assertStatus(404);
});
