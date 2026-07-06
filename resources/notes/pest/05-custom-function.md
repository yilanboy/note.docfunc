# 定義自己的方法

剛剛提到的我們可以使用 `actingAs()` 在測試中模擬某個用戶登入

這個方法很常被拿來使用，但每次使用前都要先建立一個 user 並傳入 `actingAs()`，久而久之，你的測試可能很常重複下面這段程式碼

```php
$user = User::factory()->create();

$this->actingAs($user);
```

我們可以在 `tests/Pest.php` 中自己定義一個 `login()` 方法，並將上面的流程放進去

```php
use App\Models\User;

function login($user = null) {
    return test()->actingAs($user ?? User::factory()->create());
}
```

之後我們就可以在測試中重複使用剛剛定義的登入方法，貫徹 DRY 原則

```php
test('can view contents', function () {
    login()->get('/contents')->assertStatus(200);
});
```
