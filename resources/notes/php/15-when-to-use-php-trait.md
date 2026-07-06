# 什麼時候該用 PHP Trait？

## 一句話判準

> **當多個「沒有共同父類別」的類別，需要共用一段「會存取各自 `$this`（狀態或契約）」的行為時，才用 Trait。**

兩個條件要**同時**成立：

1. **需要 `$this`** —— 方法會讀取/修改宿主的屬性，或呼叫宿主實作的 abstract method。
2. **無法用繼承** —— 這些類別屬於不同的繼承樹，沒有合理的共同父類別可以放這段邏輯。

只要方法**不碰 `$this`**，它就不該放在 Trait 裡。

## 最關鍵的測試：這段邏輯有用到 `$this` 嗎？

這是決定該不該用 Trait 的最強訊號。

「用到 `$this`」有三種形式，符合其一即可，**不限於「修改」狀態**：

| 形式                 | 說明                             | 例子                                |
| -------------------- | -------------------------------- | ----------------------------------- |
| 讀取屬性             | 方法依賴宿主的欄位值（唯讀也算） | `return $this->price * $this->qty;` |
| 修改屬性             | 方法改變宿主的狀態               | `$this->dirty = true;`              |
| 呼叫 abstract method | Trait 定義骨架，要求宿主補實作   | `$this->resolveKey()`               |

> ⚠️ 常見誤解：「Trait 的方法要修改宿主狀態」。**不一定**。很多正確的 Trait 是唯讀的，
> 重點是**耦合到宿主**，不是 write。

如果方法只是「輸入參數 → 輸出結果」，完全不碰 `$this`，那它是**純函式**，
應該放 static utility class 或被注入，而不是塞進 Trait。

## 共用邏輯 ≠ 該用 Trait

「兩個類別都會用到」只證明「需要共用」，但共用的手段有很多種：

| 手段                      | 適用情境                                             |
| ------------------------- | ---------------------------------------------------- |
| **Static utility class**  | 純函式，不依賴任何物件狀態（如顏色轉換、字串格式化） |
| **組合 / 依賴注入（DI）** | 需要可替換、可 mock、有自己生命週期的協作者          |
| **繼承**                  | 類別之間有「is-a」關係，且有合理的共同父類別         |
| **介面（interface）**     | 只需要約定「能做什麼」，不共用實作                   |
| **Trait**                 | 跨繼承樹、需要存取各自 `$this`、又想避免重複實作     |

選 Trait 前先問：「換成 static utility 或 DI 會不會更乾淨？」通常會。

## Trait 的代價（為什麼別濫用）

1. **污染公開 API** —— Trait 的 `public` 方法會變成**每個**宿主類別的公開方法。
   一個只想表示「純色背景」的 value object，卻因此多出 `nameToHex()` 之類的方法。
2. **隱藏耦合** —— 看類別簽章看不出它從哪裡來了哪些行為，要翻 `use` 才知道。
3. **無法替換 / mock** —— Trait 是編譯期「複製貼上」，不是可注入的依賴，難以在測試中抽換。
4. **命名衝突** —— 多個 Trait 有同名方法時要手動 `insteadof` / `as` 解決。
5. **static / instance 混用容易亂** —— Trait 內 static 與 instance 方法混雜時，
   宿主端呼叫方式不一致。

## 適合 Trait 的真實例子

- **`Illuminate\Support\Traits\Macroable`** —— 讓任意類別都能在執行期註冊自訂方法，
  方法存取 `$this` 並操作該物件，且使用者類別分屬不同繼承樹。
- **Laravel Eloquent 的 `SoftDeletes`** —— 操作 model 自身的 `deleted_at` 欄位與查詢，
  高度依賴宿主（`$this`）狀態，且 model 已繼承 `Model`，無法再多繼承一層。
- **「自帶狀態」的 Trait** —— Trait 可以宣告自己的屬性，例如一個 `HasEvents` trait
  持有 `$listeners` 並提供 `on()` / `fire()`，被多種不相關的類別重複使用。

## 反例：本專案的 `ColorConverter`

`ColorConverter` 的方法（`isValidHex` / `hexToRgb` / `nameToHex` / `toHex`）
**沒有一個碰宿主的 `$this`** —— 它們純粹是「字串進、字串/陣列出」的函式。

- ❌ 當成 Trait → 把顏色工具的方法漏進 `Solid`、`Gradient` 等 value object 的公開 API。
- ✅ 改成 `final class` + `static` 方法 → `ColorConverter::toHex(...)`，
  共用一樣方便，不污染任何類別的介面，也最符合「純函式」的語意。

**結論**：純函式用 static utility；需要存取各自 `$this` 又跨繼承樹時，才用 Trait。
