# 使用 Faker 產生假資料

Faker 可以用來生產假資料，除了常被用做 database 的 seeding 之外，測試中也同樣很常被使用

Pest 有提供一個 faker plugin，讓我們可以在 Pest 中使用 Faker，首先使用下面的方式安裝

```bash
composer require pestphp/pest-plugin-faker --dev
```

Faker 在 Pest 中的使用範例如下，假設測試能否通過 Post 請求新增一個聯絡人

```php
use function Pest\Faker\faker;

it('can store a contact', function () {
    login()->post('/contacts', [
        'first_name' => faker()->firstName,
        'last_name' => faker()->lastName,
        'email' => faker()->email,
        'phone' => faker()->e163PhoneNumber,
        'address' => 'No. 22, Fake Rd',
        'city' => 'Fake City',
        'region' => 'Fake Region',
        'country' => faker()->randomElement(['us', 'tw']),
        'postal_code' => faker()->postcode,
    ])
    ->assertRedirect('/contacts')
    ->assertSessionHas('success', 'Contact created');
});
```

如果不想額外裝套件，可以使用 `WithFaker` 這個 trait

```php
use Illuminate\Foundation\Testing\WithFaker;

uses(WithFaker::class);

it('can store a contact', function () {
    login()->post('/contacts', [
        'first_name' => $this->faker->firstName,
        'last_name' => $this->faker->lastName,
        // ...
    ])
    ->assertRedirect('/contacts')
    ->assertSessionHas('success', 'Contact created');
});
```
