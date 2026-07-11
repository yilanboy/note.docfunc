<?php

arch()
    ->expect('App')
    ->toUseStrictTypes()
    ->not->toUse(['die', 'dd', 'dump']);

arch()->preset()->laravel();

it('should contains README.md under root directory', function () {
    expect(file_exists(base_path('README.md')))->toBeTrue();
});

it('must contains README.md under all categories', function () {
    foreach (glob(config('notes.path').'/*', GLOB_ONLYDIR) as $path) {
        expect(file_exists($path.'/README.md'))->toBeTrue();
    }
});

it('must contains number at the beginning of the note file name', function () {
    collect(glob(config('notes.path').'/*/*.md'))
        ->reject(fn (string $path): bool => basename($path) === 'README.md')
        ->each(function (string $path) {
            $number = explode('-', basename($path))[0];
            $match = (bool) preg_match('/^\d+/', $number);
            expect($match)->toBeTrue();
        });
});
