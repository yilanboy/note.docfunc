# 如何判斷一個依賴是否可以接受

> 程式不可能完全沒有依賴。問題從來不是「有沒有依賴」，而是「這個依賴**健不健康**」。

本筆記以我的 PHP 套件 `preview` 的 `Surveyor → Tokenizer` 為範例，整理一套可重複套用的判斷標準。

## 範例情境

`Surveyor` 負責文字排版，其中一步是換行(`wrapText`)。換行的前提是先把字串切成「不可分割的最小單位」——中日韓逐字、拉丁逐詞。這件切詞的事由 `Tokenizer` 負責。

```php
// src/Text/Surveyor.php
final readonly class Surveyor
{
    public function __construct(private Tokenizer $tokenizer = new Tokenizer) {}

    public function wrapText(string $text, int $fontSize, string $fontPath, int $maxWidth): array
    {
        $lines = [];
        $current = '';
        $words = $this->tokenizer->splitStringToArray($text); // ← 依賴點

        foreach ($words as $word) {
            $proposed = $current.$word;

            if ($this->calculateTextBlockWidth($proposed, $fontSize, $fontPath) < $maxWidth) {
                $current = $proposed;

                continue;
            }

            $lines[] = trim($current);
            $current = $word;
        }

        $lines[] = trim($current);

        return $lines;
    }
}
```

```php
// src/Text/Tokenizer.php
final readonly class Tokenizer
{
    /** @return array<string> */
    public function splitStringToArray(string $input): array
    {
        $matches = null;

        preg_match_all('/[\p{Han}\p{Hiragana}\p{Katakana}]|[a-zA-Z0-9]+|\s|[^\p{Han}\p{Hiragana}\p{Katakana}\s\w]/u', $input, $matches);

        return $matches[0];
    }
}
```

依賴關係:

```
Surveyor.wrapText() ──需要──> Tokenizer.splitStringToArray()
```

這是「**我要完成我的本分，本來就需要這件事**」的依賴，不是硬塞進來的。方向天然合理。

## 五個判斷問題

### 1. 方向對不對?(高層 → 低層、依賴穩定的一方)

`Surveyor`(排版的協調者，較高層)依賴 `Tokenizer`(純字串處理的工具，較低層)。高層依賴低層、依賴抽象穩定的東西，是正確方向。

- ✅ 健康:`Surveyor` → `Tokenizer`
- 🚩 危險:若哪天 `Tokenizer` 反過來 `use Surveyor`，就形成循環依賴，代表職責切錯了。

### 2. 會不會循環?

`Tokenizer` 完全不認識 `Surveyor`，單向依賴。✅

### 3. 穩定度:我依賴的東西比我穩定嗎?

「斷詞規則」(中日韓逐字、拉丁逐詞)幾乎不會變；`Surveyor` 的排版邏輯反而比較常動。**依賴一個比自己穩定的東西是好的**——不會因為它常改而被連累。`Tokenizer` 的對外介面只有一個 `splitStringToArray(string): array`，小而穩。✅

### 4. 換掉它會不會很痛?(耦合強度)

`Surveyor` 只透過建構子拿到 `Tokenizer`，且只呼叫一個方法。要換成「支援泰文斷詞的 Tokenizer」時，改一個注入點即可。**耦合點越小、越集中，依賴越健康。** ✅

### 5. 它有沒有讓我更難被測試?

反而更好測:

```php
// tests/Unit/Text/TokenizerTest.php
it('can wrap the Chinese sentence into character', function () {
    $tokenizer = new Tokenizer;

    expect($tokenizer->splitStringToArray('你好世界！'))
        ->toBe(['你', '好', '世', '界', '！']);
});
```

- `Tokenizer` 是純函式，可直接、單獨測試。
- `Surveyor` 的換行測試不必再關心斷詞細節。

**好的依賴會讓兩邊都更好測；壞的依賴會逼你 mock 一堆東西才能跑一個測試。** ✅

## 一句話的判準

> 這個依賴是**我為了做好本分而真正需要的能力**，方向是**高層往低層 / 依賴穩定的一方**，而且**耦合點小、不循環、不傷測試**——滿足這些，就是健康的依賴。

反過來，壞依賴通常長這樣:為了「方便」拿到不相干的東西、方向顛倒、一改就牽動一片、或逼你為了測 A 必須先把 B 整個架起來。

## 延伸:何時該抽象成介面?

目前 `Surveyor` 依賴的是**具體類別** `Tokenizer`，而非介面。在這個專案規模下完全 OK——只有一種斷詞策略，不必預先抽 `TokenizerInterface`(YAGNI)。

訊號出現時再動:當需要「第二種斷詞規則」(支援更多語言、或讓使用者自訂),就把 `Tokenizer` 抽成介面、讓 `Surveyor` 依賴介面而非實作(依賴反轉)。

**原則:出現第二個實作時才抽象，而不是預先抽象。** 這與 `CLAUDE.md` 的 Simplicity First 一致。
