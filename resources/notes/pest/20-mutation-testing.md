---
date: '2026-11-13'
tags: [pest]
---

# Mutation Testing

Pest 在 v3 加入了變異測試，用來提升測試的可靠性與穩固性。

變異測試（Mutation Testing）是一種透過對程式碼進行微小變異，來檢驗測試品質與有效度的方法。
如果對程式碼的變異沒有導致你的測試失敗，就代表你的測試有地方沒有涵蓋到，這時候我們就可以把漏掉的地方補起來。

> [!NOTE]
> 執行變異測試需要搭配程式碼覆蓋率驅動，環境必須安裝並啟用 **Xdebug 3.0+** 或 **PCOV**。

## 基本用法

想要進行 Mutation Testing 相當簡單，只需要在測試檔案上方加入 `mutates()` 或 `covers()` 即可。

```php
<?php

covers(TodoController::class); // or mutates(TodoController::class);

it('list todos', function () {
    $this->getJson('/todos')->assertStatus(200);
});
```

`covers()` 與 `mutates()` 在變異測試時的行為完全相同；差異在於 `covers()` 還會同時影響 `--coverage` 的覆蓋率報告（只統計指定的類別），而 `mutates()` 僅針對變異測試生效。

接著在執行測試時，加上 `--mutate` 參數啟用變異測試：

```bash
php artisan test --mutate
```

啟用變異測試時，Pest 會嘗試修改 `TodoController` 中的程式碼，例如替換回傳值、修改常數或呼叫的方法等。
如果你的測試在**變異之後仍然通過**，就代表你的測試可能有地方遺漏、沒有測試到，這時候就要看看哪邊的測試案例需要補足囉。

## 指定多個類別

如果你的測試涵蓋不只一個類別，也可以在 `covers()` 中使用陣列（或以逗號分隔多個參數）傳遞多個類別。

```php
<?php

use App\Http\Controllers\TodoController;
use App\Actions\GetTodos;

covers([TodoController::class, GetTodos::class]);

it('list todos', function () {
    $this->getJson('/todos')->assertStatus(200);
});
```

## 常用參數

### `--covered-only`

需要注意的一點是，Pest 的變異測試指定對象最小是以類別為單位（無法單獨指定某個方法），所以你可能會遇到一個情況，導致變異測試失敗。

假設我有兩個測試檔案：

- `TodosTest` 測試了 `TodoController` 中的 `index` 方法。
- `TodoShowTest` 測試了 `TodoController` 中的 `show` 方法。

那麼當你在 `TodosTest` 中對 `TodoController` 執行變異測試時，大概率會失敗。
**因為變異測試會對 `show` 方法進行變異，但是 `TodosTest` 並不會執行到 `show` 方法**。

為了解決這個問題，在執行變異測試時可以加上 `--covered-only` 參數，這樣就只會對有被測試覆蓋到的部分進行變異測試。

```bash
php artisan test --mutate --covered-only
```

### `--parallel`

變異測試會針對每個變異重複執行測試，耗時通常較長，建議加上 `--parallel` 以平行執行來加速：

```bash
php artisan test --mutate --parallel
```

### `--min`

與 `--coverage --min` 類似，可以使用 `--min` 設定最低的 Mutation Score 門檻。若變異測試分數低於指定百分比，指令就會回傳失敗（適合用於 CI 把關）：

```bash
php artisan test --mutate --min=80
```

## 參考資料

- [Pest - Mutation Testing](https://pestphp.com/docs/mutation-testing)
