# 使用 Skip 根據情況跳過測試

Pest 中可以使用 `skip()` 方法跳過測試

```php
it('can validate an email', function () {
    $rule = new IsValidEmailAddress();

    expect($rule->passes('email', 'allen@hello.com'))->toBeTrue();
})
// skip 接受一個字串用來顯示為什麼跳過的訊息
->skip('we don\'t need this test anymore');
```

這樣執行測試時，該測試就會被跳過，並在測試結果上顯示為什麼跳過的訊息

```text
 WARN  Tests\Feature\ExampleTest
- it can validate an email → we don't need this test anymore
```

`skip()` 可以傳入一個判斷式，可以根據情況來決定要不要執行這個測試

```php
it('can validate an email', function () {
    $rule = new IsValidEmailAddress();

    expect($rule->passes('email', 'allen@hello.com'))->toBeTrue();
})
// 可以用來根據環境來決定是否跳過測試
->skip(getenv('SKIP_TESTS') ?? false, 'we don\'t need this test in this condition');
```

注意 `skip()` 的判斷是在 Laravel 啟動之前，所以沒有辦法直接使用 Laravel 提供的方法

```php
it('can validate an email', function () {
    $rule = new IsValidEmailAddress();

    expect($rule->passes('email', 'allen@hello.com'))->toBeTrue();
})
// error
->skip(config('app.name') === 'foo', 'we don\'t need this test in this condition');
```

想使用 Laravel 提供的方法，就必須**將其放入匿名函式中**

```php
it('can validate an email', function () {
    $rule = new IsValidEmailAddress();

    expect($rule->passes('email', 'allen@hello.com'))->toBeTrue();
})
// make config() be executed after laravel booted
->skip(fn () => config('app.name') === 'foo', 'we don\'t need this test in this condition');
```
