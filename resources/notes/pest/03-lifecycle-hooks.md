# Pest 提供的 Lifecycle Hook 方法

在 PHPUnit 中，我們可以使用 `setUp()` 方法，在測試開始前進行一些前置作業

而 Pest 提供更靈活的 lifecycle hook 方法，讓我們可以在測試的各個階段進行一些額外的操作

在測試中可以使用 `actingAs()` 方法模擬登入用戶的操作

```php
$this->actingAs($this->user)
```

等等，這個 `$this->user` 是怎麼來的 ?

在原本的測試寫法中，可以在建立一個 `protect` 方法 `setUp()`，並在其中設定 `$ths->user`

```php
protected function setUp(): void
{
    parent::setUp();

    $ths->user = User::factory()->create();
}
```

在 Pest 中你可以使用下面幾個 lifecycle hook 方法，讓你在測試的各個階段，進行一些額外操作

```php
beforeAll(fn () => var_dump('Before All'));
beforeEach(fn () => var_dump('Before Each'));
afterEach(fn () => var_dump('After Each'));
afterAll(fn () => var_dump('After All'));
```

嘗試在一個 Pest 檔案的測試上方貼上這段程式碼，並執行該檔案的測試

```php
beforeAll(fn () => var_dump('Before All'));
beforeEach(fn () => var_dump('Before Each'));
afterEach(fn () => var_dump('After Each'));
afterAll(fn () => var_dump('After All'));

test('can view contents', function () {
    // ...
});

test('can view user information', function () {
    // ...
});
```

你會發現印出來的訊息如下所示

```text
string(10) "Before All"
string(11) "Before Each"
string(10) "After Each"
string(11) "Before Each"
string(10) "After Each"
string(9) "After All"
```

很明顯 `beforeAll()` 會在該檔案全部測試執行前先執行，`beforeEach()` 與 `afterEach()` 則是在每個測試的前後執行，而 `afterAll()` 會在該檔案的測試全部結束之後執行

需要注意的是，`beforeAll()` 沒有辦法使用 Laravel 所提供的方法，因為 Laravel 此時還沒有完成啟動 (boot)

```php
// error: Target class [config] does not exist
beforeAll(fn () => var_dump(config('app.name')));

// correct
beforeEach(fn () => var_dump(config('app.name')));
```

所以剛剛的提問，`$this->user` 是怎麼來的？

我們可以使用 Pest 提供的 lifecycle hook 方法在 `beforeEach()` 中設定這個屬性

```php
beforeEach(function () {
    $ths->user = User::factory()->create()
});
```
