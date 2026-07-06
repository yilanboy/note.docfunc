# 使用 Datasets 測試多組資料

如果你想要在一個流程測試中，測試多筆資料，你會怎麼做呢？

雖然我們可以把測試拆開，為每筆資料都寫一個測試，但因為測試邏輯與流程是一樣的，如果每一筆資料都寫一個測試，會讓程式碼顯得冗長且非常多餘

遇到這樣的情況，可以考慮使用 Datasets

我們可以在匿名函式中放入一個參數 `$email`，並呼叫 `with()` 方法代入一個有多筆信箱資料的陣列

```php
it('can store a contact', function ($firstName) {
    login()->post('/contacts', [
        'first_name' => $firstName, // 代入 Datasets 資料
        'last_name' => faker()->lastName,
        'email' => faker()->email,
        // ...
    ])
    ->assertRedirect('/contacts')
    ->assertSessionHas('success', 'Contact created');
})->with([
    'Allen',
    '"Lisa"',
]);
```

此時這個測試就會被執行兩次，而這兩次都會測試不同的信箱資料

執行測試之後也會顯示為兩個測試

```text
 PASS  Tests\Feature\Contacts\StoreTest
✓ it can store a contact with (Allen)
✓ it can store a contact with ('"Lisa"')
```

Datasets 也可以帶入多個參數

```php
it('can store a contact', function ($firstName, $email) {
    login()->post('/contacts', [
        'first_name' => $firstName,
        'last_name' => faker()->lastName,
        'email' => $email,
        // ...
    ])
    ->assertRedirect('/contacts')
    ->assertSessionHas('success', 'Contact created');
})->with([
    ['John', faker()->email],
    ['Hellen', '"test"@email.com'],
]);
```

可以使用參數拆包的方式，如果沒有在 Datasets 設定資料，就使用預設的資料

```php
it('can store a contact', function (array $data) {
    login()->post('/contacts', [
        ...[
            'first_name' => faker()->firstName,
            'last_name' => faker()->lastName,
            'email' => faker()->email,
            // ...
        ],
        ...$data
    ])
    ->assertRedirect('/contacts')
    ->assertSessionHas('success', 'Contact created');
})->with([
    [[]],
    [['email' => faker()->email, 'first_name' => 'John']],
    [['email' => '"test"@email.com', 'first_name' => 'Hellen']],
]);
```

可以在 Datasets 中加上 key 值

```php
it('can store a contact', function (array $data) {
    login()->post('/contacts', [
        ...[
            'first_name' => faker()->firstName,
            'last_name' => faker()->lastName,
            'email' => faker()->email,
            // ...
        ],
        ...$data
    ])
    ->assertRedirect('/contacts')
    ->assertSessionHas('success', 'Contact created');
})->with([
    'empty' => [[]],
    'data 1' => [['email' => faker()->email, 'first_name' => 'John']],
    'data 2' => [['email' => '"test"@email.com', 'first_name' => 'Hellen']],
]);
```

這樣測試結果就會顯示你所設定的 key 名稱

```text
 PASS  Tests\Feature\Contacts\StoreTest
✓ it can store a contact with data set "empty"
✓ it can store a contact with data set "data 1"
✓ it can store a contact with data set "data 2"
```

## 設定 Shared Datasets

可以使用 Laravel 中 `artisan` 的指令 `pest:dataset` 生成一個 Shared Datasets

```bash
php artisan pest:dataset Emails
```

這個指令會生成一個 `tests/Datasets/Emails.php`，我們可以在其中定義一個 Datasets

```php
dataset('valid emails', function () {
    return ['email A', 'email B']
});
```

之後就可以在測試中使用這個 Shared Datasets

```php
it('can store a contact', function ($email) {
    login()->post('/contacts', [
        'first_name' => faker()->firstName,
        'last_name' => faker()->lastName,
        'email' => $email,
        // ...
    ])
    ->assertRedirect('/contacts')
    ->assertSessionHas('success', 'Contact created');
})->with('valid emails'); // 使用 Shared Datasets 資料
```

## Combined Datasets

你可以組合多個 Datasets，測試更多種組合資料。

例如我想測試一個上傳圖片後顯示圖片規格的功能，除了測試圖片類型，例如 jpg、png 或是 webp，每個類型也想測試不同的大小尺寸

```php
use Illuminate\Support\Facades\Storage;

it('can show supported image formats and options', function ($path, $options) {
    Storage::fake()->put($path, file_get_contents(__DIR__ . "/../../fixtures/{$path}"))

    $response = $this->get(route('image', ['path' => $path, ...$options]));
    $response->assertOk();

    expect($response->streamedContent())->not->toBeEmpty()->toBeString();
})->with([
    ['example.jpg', ['w' => 10, 'h' => 10, 'fit' => 'crop']],
    ['example.jpg', ['w' => 40, 'h' => 40, 'fit' => 'crop']],
    ['example.jpg', ['w' => 50, 'h' => 50, 'fit' => 'crop']],
    ['example.png', ['w' => 10, 'h' => 10, 'fit' => 'crop']],
    ['example.png', ['w' => 40, 'h' => 40, 'fit' => 'crop']],
    ['example.png', ['w' => 50, 'h' => 50, 'fit' => 'crop']],
    ['example.webp', ['w' => 10, 'h' => 10, 'fit' => 'crop']],
    ['example.webp', ['w' => 40, 'h' => 40, 'fit' => 'crop']],
    ['example.webp', ['w' => 50, 'h' => 50, 'fit' => 'crop']],
]);
```

上面的寫法重複度有些高，為了測試 10、40 與 50 的尺寸，我們每個圖片都要特地寫三種尺寸，這時候就可以考慮使用組合 Datasets

```php
use Illuminate\Support\Facades\Storage;

it('can show supported image formats and options', function ($path, $options) {
    Storage::fake()->put($path, file_get_contents(__DIR__ . "/../../fixtures/{$path}"))

    $response = $this->get(route('image', ['path' => $path, ...$options]));
    $response->assertOk();

    expect($response->streamedContent())->not->toBeEmpty()->toBeString();
})->with([
    'example.jpg',
    'example.png',
    'example.webp',
])->with([
    ['w' => 40, 'h' => 40, 'fit' => 'crop'],
    ['w' => 50, 'h' => 50, 'fit' => 'crop'],
    ['w' => 10, 'h' => 10, 'fit' => 'crop'],
]);
```

顯示的測試結果如下

```text
 PASS  Tests\Feature\Image\ShowTest
✓ it can show supported image formats and options with ('example.jpg') / (array(40, 40, 'crop'))
✓ it can show supported image formats and options with ('example.jpg') / (array(50, 50, 'crop'))
✓ it can show supported image formats and options with ('example.jpg') / (array(10, 10, 'crop'))
✓ it can show supported image formats and options with ('example.png') / (array(40, 40, 'crop'))
✓ it can show supported image formats and options with ('example.png') / (array(50, 50, 'crop'))
✓ it can show supported image formats and options with ('example.png') / (array(10, 10, 'crop'))
✓ it can show supported image formats and options with ('example.webp') / (array(40, 40, 'crop'))
✓ it can show supported image formats and options with ('example.webp') / (array(50, 50, 'crop'))
✓ it can show supported image formats and options with ('example.webp') / (array(10, 10, 'crop'))
```
