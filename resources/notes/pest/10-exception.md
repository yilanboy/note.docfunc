# 斷定是否會拋出例外 (Exception)

如果要測試一個流程在遭遇問題時是否會拋出例外 (Exception)，斷定中也有 `expectException()` 的方法可以使用

```php
it('can validate an email', function () {
    // 如果預計這個測試會拋出例外，我們可以使用這個方法來進行斷定
    // 注意這個方法必須放在測試中的最上方
    $this->expectException(InvalidArgumentException::class);

    $rule = new IsValidEmailAddress();

    $rule->passes('email', 123);
});
```

Pest 為這種情況提供一個更直覺的方法 `throws()`

```php
it('can validate an email', function () {
    $rule = new IsValidEmailAddress();

    $rule->passes('email', 123);
})
->throws(InvalidArgumentException::class);
```

除了斷定丟出哪一種例外，也可以對例外的錯誤訊息進行斷定，就算例外的類型對上了，只要訊息不對，斷定還是會失敗

```php
it('can validate an email', function () {
    $rule = new IsValidEmailAddress();

    $rule->passes('email', 123);
})
->throws(InvalidArgumentException::class, 'The value must be a string');
```
