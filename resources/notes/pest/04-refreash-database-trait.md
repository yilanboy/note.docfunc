# 使用 RefreshDatabase Trait 重置資料庫

通常在測試時，我們很常使用 `RefreshDatabase` 這個 Trait 來重置整個資料庫，方便進行測試

在 Pest 中，因為已經沒有 `class` 宣告，我們無法使用 trait 的方式引入 `RefreshDatabase`，所以我們必須換個方式，使用 uses()

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can view contents', function () {
    // ...
});
```

如果你的每個測試都需要 `uses(RefreshDatabase::class)`，你可以考慮在 `tests/TestCase.php` 中引入 `RefreshDatabase`

```php
namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    use RefreshDatabase;
}
```

為了有更好的測試速度，你可以使用 `LazilyRefreshDatabase`，當測試動到資料庫時，才會重置資料庫

```php
namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    use LazilyRefreshDatabase;
}
```
