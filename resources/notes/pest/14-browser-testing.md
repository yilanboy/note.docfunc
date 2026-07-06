# 瀏覽器測試 (Browser Testing)

Pest 4 整合了 [Playwright](https://playwright.dev/) 開始支援瀏覽器測試 (Browser Testing)，讓你可以使用 Pest 來撰寫瀏覽器測試。

## 安裝 Playwright

想要使用 Pest 開始寫瀏覽器測試，你需要先安裝對應的套件與 Playwright。

```bash
# 安裝 pest 的 browser testing 套件
composer require pestphp/pest-plugin-browser --dev

# 安裝 playwright
npm install playwright@latest

# 使用 playwright 下載瀏覽器驅動程式（browser driver）
npx playwright install
```

## 建立瀏覽器測試的資料夾

我想區分瀏覽器測試與之前寫的功能測試，所以在 `tests` 資料夾底下新增一個 `Browser` 資料夾，用來存放瀏覽器測試的 PHP 檔案。

```text
tests/
├── Browser/
│   ├── LinkTest.php
│   ├── SmokeTest.php
│   └── TagTest.php
├── Feature/
│   ├── ArchTest.php
│   ├── LinkTest.php
│   ├── PageTest.php
│   └── TagTest.php
├── Pest.php
├── TestCase.php
└── Unit/
    └── ExampleTest.php
```

新增資料夾之後，我們還需要修改 PHPUnit 的設定。在 `phpunit.xml` 中新增 Browser 的 Test Suit，並指定 PHP 測試檔案存放的位置。這樣才能讓 Pest 知道這些測試是需要被執行的。

```xml
<testsuites>
    <!-- ... -->

    <testsuite name="Browser">
        <directory>tests/Browser</directory>
    </testsuite>
</testsuites>
```

新增 Test Suit 之後，我們就可以在執行測試時指定要執行哪一個 Test Suit。假設我只想執行瀏覽器測試，可以使用 `--testsuit` 參數來指定 `Browser`。

```bash
php artisan test --testsuite Browser
```

最後一個步驟，我們需要在 `Pest.php` 中幫瀏覽器測試繼承 Laravel 提供的 `TestCase` 抽象類別。

```php
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Browser');
```

接下來就可以開始寫瀏覽器測試啦！

## 開始寫第一個瀏覽器測試

新增一個最簡單的冒煙測試。

```php
it('should be no smoke', function () {
    // 訪問 /login 頁面
    $page = visit('/login');

    // 不能有 JavaScript 錯誤或是使用 console.log() 印出訊息
    $page->assertNoSmoke();
});
```

執行瀏覽器測試並查看結果。

> ⚠️ 請確認執行測試前，已經打包了最新的前端程式碼。

```bash
php artisan test --testsuite Browser --filter "should be no smoke"
```

```text
PASS  Tests\Browser\SmokeTest
✓ it should be no smoke          1.02s

Tests:    1 passed (2 assertions)
Duration: 1.50s
```

我們可以調皮的加上一個 `console.log("Oops!")`，並重新跑測試看看。測試沒有意外的因為 `console.log()` 而失敗。

```text
Test [it_should_be_no_smoke] failed with the message: Expected no console logs on the page initially with the url [http://127.0.0.1:58198/login], but found 1: Oops!
Failed asserting that an array is empty.

The following console logs were found:
- Oops!

Press any key to continue...
```

感覺跟平常寫的測試沒有差別，但實際上 Pest 悄悄的在背後開了一個無頭（Headless）瀏覽器來執行你的測試。

如果你想看 Pest 操作瀏覽器的過程，可以使用 `--debug` 參數。這樣就能看到 Pest 開啟一個正常的瀏覽器跑測試。

```bash
php artisan test --testsuite Browser --filter "should be no smoke" --debug
```

但因為這個測試實在是太簡短了，所以瀏覽器在開啟跑完測試後，馬上就會立刻關閉，整個過程不到一秒鐘，看不太到操作的過程 😂。

來寫一個稍微複雜一點的流程，例如登入功能。

```php
test('user can login', function () {
    $page = visit('/login');

    // 找到含有 name="email" 屬性的輸入框，並輸入 allen@email.com
    $page->type('email', 'allen@email.com');
    // 找到含有 name="password" 屬性的輸入框，並輸入 allen@email.com
    $page->type('password', 'Password101');

    // 找到文字為 "Sign in" 的按鈕，並點擊
    $page->click('Sign in');

    // 斷定登入後，畫面上會出現 "Dashboard"
    $page->assertSee('Dashboard');
});
```

可以看到瀏覽器測試的語法糖相當直覺，而且最棒的部分是，**測試過程是會自動等待的**，意思是 Pest 點擊按鈕登入後，不會立刻去判斷畫面上是否出現 Dashboard，而是會等待一段時間，等頁面跳轉完畢還有畫面完全準備好之後，才會去判斷測試結果，避免結果還沒出來之前就直接判斷測試失敗。

當然等待也不會一直持續下去，超過一定時間後，測試就會被判斷為失敗。

> 有使用過 Selenium 這個熱門 e2e 測試框架的朋友應該會曉得，很多時候為了等待頁面準備好，我們需要在 Selenium 中明確設定等待時間。

關於 `click()` 定位元素的部分，除了可以使用文字，你也可以使用 CSS 選擇器。使用起來相當直覺。

```php
// 點擊第一個文字為 "Login" 的按鈕
$page->click('Login');

// 點擊第一個 class 帶有 "btn-primary" 的按鈕
$page->click('.btn-primary');

// 點擊 id 為 "submit-button" 的按鈕
$page->click('#submit-button');
```

## 畫面測試

在 Laracon US 上，Nuno 大還秀了另外一個很酷的功能，也就是畫面測試。這個功能可以用來判斷畫面是否符合你的預期。

簡單寫一個登入頁面的畫面測試。

```php
test('login visual test', function () {
    $page = visit('/login');

    $page->assertScreenshotMatches();
});
```

測試在第一次執行時，Pest 會告訴你這個測試並未完成，但是它已經幫你對登入的畫面進行了快照（Snapshot）。

```text
WARN  Tests\Browser\LoginTest
… login visual test → Snapshot created at [tests/.pest/snapshots/Browser/LoginTest/login_visual_test.snap]  1.31s

Tests:    1 incomplete (2 assertions)
Duration: 1.79s
```

第二次執行測試，測試就會通過了。

```text
PASS  Tests\Browser\LoginTest
✓ login visual test          1.17s

Tests:    1 passed (3 assertions)
Duration: 1.57s
```

這個時候一樣是調皮時間，我們偷偷的把登入畫面的標題改成 Bug Title，並重新執行測試，理所當然的，測試不會通過。

```text
FAIL  Tests\Browser\LoginTest
⨯ login visual test                                             1.96s
─────────────────────────────────────────────────────────────────────
FAILED  Tests\Browser\LoginTest > login visual test
Screenshot does not match the last one.
- Expected? Update the snapshots with [--update-snapshots].
- Not expected? Re-run the test with [--diff] to see the differences.
```

Pest 會告訴你它發現畫面與上次的快照有差異，並詢問這是否是預期的情況，如果是的話，我們可以使用 `--update-snapshots` 更新快照。

但如果我們不知道畫面哪邊出現差異時，就可以使用 `--diff` 執行測試，瀏覽器會開啟一個頁面來顯示快照對比圖，並醒目的提示畫面哪邊有差異。

![pest-4-visual-test-diff-mode](https://blobs.docfunc.com/images/2025_09_14_17_14_56_e439009b2a57.png)

左上角可以選擇畫面比對的模式，預設為 Diff 模式，你也可以使用 Slide 模式，利用左右移動滑桿的方式來查看畫面的差異，真的是超級酷的！

![pest-4-visual-test-slide-mode](https://blobs.docfunc.com/images/2025_09_14_17_18_32_855bf5d998c9.png)

本文僅簡單介紹了 Pest 4 的瀏覽器測試功能，其實 Nuno 大還帶來了更多精彩的新功能。如果你還想深入了解，不妨直接前往官方文件探索完整的 Pest 4 更新。

所以各位…

還在等什麼？快開始用 PHP 來寫瀏覽器測試 吧！🚀

## 參考資料

- [Pest 4 - Browser Testing](https://pestphp.com/docs/browser-testing)
