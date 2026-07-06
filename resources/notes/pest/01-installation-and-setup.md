# 安裝與設定 Pest

透過 composer 安裝 Pest

```bash
composer require pestphp/pest --dev --with-all-dependencies
```

如果你是使用 Laravel 專案，需要再安裝 `pest-plugin-laravel` 與執行 artisan 指令 `pest:install` 生成一個 Pest 的設定檔案 `tests/Pest.php`

```bash
composer require pestphp/pest-plugin-laravel --dev

php artisan pest:install
```

Pest 是建構在 PHPUnit 上的，**因此原本 PHPUnit 的測試寫法，Pest 完全兼容，並不需要修改舊的測試**

以下為原本 PHPUnit 的測試寫法

```php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Models\User;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_guest_can_visit_someone_information_page()
    {
        $user = User::factory()->create();

        $response = $this->get(route('users.index', $user->id))

        $response->assertStatus(200);
    }
}
```

如果改用 Pest 的測試寫法

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('the application returns a successful response', function () {
    get('/')->assertStatus(200);
});

test('guest can visit someone information page', function () {
    $user = User::factory()->create();

    get(route('users.index', $user->id))->assertStatus(200)
});
```

在 Laravel 專案中你一樣可以使用 artisan 指令啟動測試

```bash
php artisan test
```
