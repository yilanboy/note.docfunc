<?php

use App\Services\MarkdownConverter;
use Illuminate\Support\Carbon;

test('every note category must have a README.md', function () {
    $categories = collect(glob(resource_path('notes').'/*', GLOB_ONLYDIR))
        ->merge(glob(config('notes.path').'/*', GLOB_ONLYDIR))
        ->unique();

    expect($categories)->not->toBeEmpty();

    foreach ($categories as $categoryDir) {
        $category = basename($categoryDir);
        $readmePath = $categoryDir.'/README.md';

        expect(file_exists($readmePath))
            ->toBeTrue("分類 [{$category}] 缺少 README.md");
    }
});

test('all markdown notes and readmes have valid frontmatter metadata', function () {
    $notes = collect(glob(resource_path('notes').'/**/*.md'))
        ->merge(glob(resource_path('notes').'/*.md'))
        ->merge(glob(config('notes.path').'/**/*.md'))
        ->merge(glob(config('notes.path').'/*.md'))
        ->unique();

    expect($notes)->not->toBeEmpty();

    foreach ($notes as $file) {
        $relative = str_replace(base_path().'/', '', $file);
        $parsed = app(MarkdownConverter::class)->parse(file_get_contents($file));

        expect(str_starts_with(file_get_contents($file), '---'))
            // 1. 確保包含 Frontmatter header
            ->toBeTrue("檔案 [{$relative}] 缺少 YAML Frontmatter header (---)")
            ->and(array_key_exists('date', $parsed['metadata']))
            ->toBeTrue("檔案 [{$relative}] 必須包含 'date' 欄位")
            ->and(isset($parsed['metadata']['date']) && Carbon::hasFormat($parsed['metadata']['date'], 'Y-m-d'))
            // 2. date 必填且符合 YYYY-MM-DD 格式
            ->toBeTrue("檔案 [{$relative}] 的 date 格式必須為 YYYY-MM-DD");

        if (isset($parsed['metadata']['updated'])) {
            expect(Carbon::hasFormat($parsed['metadata']['updated'], 'Y-m-d'))
                ->toBeTrue("檔案 [{$relative}] 的 updated 格式必須為 YYYY-MM-DD");
        }

        // 3. tags 若有填寫，必須為陣列格式
        if (isset($parsed['metadata']['tags'])) {
            expect($parsed['metadata']['tags'])
                ->toBeArray("檔案 [{$relative}] 的 'tags' 必須是陣列格式")
                ->each->toBeString("檔案 [{$relative}] 的標籤必須是字串");
        }
    }
});
