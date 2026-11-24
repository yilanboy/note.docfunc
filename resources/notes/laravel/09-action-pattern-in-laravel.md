---
date: '2026-06-07'
updated: '2026-11-24'
tags: [laravel]
---

# Laravel 中的 Action Pattern

本篇整理自 Nuno Maduro（Laravel 核心團隊成員）的演講 [The Action Pattern Is Key to Clean Code](https://www.youtube.com/watch?v=k_gMfdpSXQE)，並結合 Laravel 社群中具有高度共識的架構實踐（包含 **Brent Roose / Spatie** 的《*Laravel Beyond CRUD*》、**Freek Van der Herten** 的《*Refactoring to Actions*》、**Nuno Maduro** 的 starter kits 與 `essentials`，以及 Laracasts 等社群討論），彙整 Action Pattern 的核心原則、流派慣例與邊界判斷。

> **名詞釐清**：Laravel 官方文件中的「Single Action Controller」是指只有 `__invoke()` 方法的 Controller；而社群在談軟體架構時的 **Action Pattern（Action Class）** 則是指封裝單一業務邏輯的純 PHP 類別。本篇筆記討論的是後者（**Action Class**）。

---

## 什麼是 Action Pattern？

- **歷史脈絡**：Action Pattern 起源於 Taylor Otwell 早期稱之為「Self-handling Command Bus」的概念，它是 Command Bus Pattern（包含 Command 資料載荷與 Handler 處理邏輯）的簡化版。
- **核心概念**：傳統 Command Bus 會將資料與處理邏輯拆成兩個類別，但在實際開發中兩者通常是一對一關係。因此，Action Pattern 將 Command 與 Handler 合併為單一類別，專門負責系統中的**單一業務操作**（例如 `CreateUserAction`、`UpdateUserAction`）。

---

## 基本用法：Controller 搭配 Action

透過 Laravel 的依賴注入（Dependency Injection），我們可以直接在 Controller 方法中注入 Action 類別，並搭配 Form Request 處理 HTTP 請求的驗證。

```php
namespace App\Http\Controllers;

use App\Actions\UpdateUserAction;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class ProfileController extends Controller
{
    public function update(
        UpdateUserRequest $request,
        UpdateUserAction $updateUser,
        #[CurrentUser] User $user
    ): RedirectResponse {
        // 僅將驗證過的資料傳入 Action，不傳入 HTTP Request 相關物件
        $updateUser->handle($user, $request->validated());

        return redirect()->back()->with('success', 'Profile updated!');
    }
}
```

> **關於 `#[CurrentUser]` 屬性**：
> 這是 Laravel 提供的實用 Attribute，可以自動注入當前登入的使用者模型，避免在方法中寫出繁瑣的 `$request->user()` 或 `Auth::user()`，讓方法簽名更直觀。

---

## 核心設計原則

### 1. 單一對外入口（One Public Method）

除了 `__construct()` 之外，一個 Action **只能有一個 `public` 執行方法**（常見命名為 `handle()`、`execute()` 或 `__invoke()`）。

- 為了提升可讀性，可以在類別內部拆出 `private` 輔助方法。
- 一旦某段子邏輯需要被**外部其他類別**呼叫，就代表它本身是一個獨立動作，應該抽成另一個 Action。
- 這樣做的根本目的，是防止類別隨著需求迭代慢慢膨脹成什麼都塞的「上帝 Service（God Class）」。

### 2. 嚴格區分建構子與執行參數

Brent Roose 在《*Laravel Beyond CRUD*》中特別強調這項邊界：

- **`__construct()` 只放依賴（Dependencies）**：由 Laravel Service Container 自動注入（例如其他 Actions、Services、Repositories、API Client 等）。
- **`handle()` / `execute()` 只放執行期資料（Runtime Data）**：例如 Eloquent Model、DTO 或純量參數。

這樣區分後，Container 隨時能解析 Action，也方便組合子 Action 並在測試時輕鬆 Mock 依賴。

### 3. 無狀態（Stateless）且不可變

Action 代表「動作／流程」，不是資料容器，不應該在成員屬性上保存會變動的請求狀態。

現代 PHP 慣例（如 Nuno Maduro 所推崇的寫法）會將 Action 宣告為 `readonly class`（甚至 `final readonly class`），強制依賴不可變並避免繼承濫用，改以**組合（Composition）**代替繼承。

### 4. 與入口層解耦（Context-Independent）與驗證邊界

Action 應該是「無上下文」的，不應該接收或依賴 `Request` / `FormRequest`，也不該耦合 HTTP Session、Response（如重新導向）或 Console I/O。它只接收驗證完畢的陣列（或 DTO）、Model 與純量參數。

- **為什麼重要？** 這能確保同一支 Action 可以在 Controller、Artisan Command、Queue Job、Event Listener 與測試之間無痛重用。
- **如何劃分邊界？** 以「刪除帳號」為例：
    - **Action 的範疇**：刪除使用者的歷史紀錄、刪除資料庫中的 User 資料、發送道別電子郵件。
    - **Controller 的範疇**：登出使用者（`Auth::logout()`）、失效 Session（`$request->session()->invalidate()`）與重新生成 CSRF Token（屬於 HTTP 層級的關注點）。
- **資料驗證的邊界**：Action 本身**不應該**負責輸入驗證，而是假定資料進入 Action 前已經過完整驗證。驗證規則（如信箱格式、唯一性、字串長度）應全部留在 Form Request 中；若驗證失敗，請求在進入 Controller 前就會被攔截。

### 5. 不要強行共用 `ActionInterface` 或 Base Class

每個 Action 的輸入參數與回傳型別都不同，硬套一個通用介面反而會破壞靜態分析（如 PHPStan）的型別推導與強型別檢查。讓 Action 保持單純的 Plain Old PHP Object（POPO）即可。

### 6. 避免用 `public static` 呼叫

社群普遍不建議將 Action 寫成靜態工具方法（或過度依賴 `MyAction::run()` 魔法呼叫），因為這會破壞依賴注入與單元測試的隔離性。

---

## 參數設計與資料庫事務

### 1. 參數設計：Array Shapes 還是 DTO？

在傳遞執行期資料給 Action 時，通常有兩種常見做法，可依團隊對型別嚴格度與結構簡潔度的偏好選擇：

1. **DTO（Data Transfer Object）**
   - **優點**：強型別、在 IDE 中有極佳的程式碼提示與補全。
   - **缺點**：每個 Action 往往需要對應一個額外的 DTO 類別，容易使專案檔案數量大幅增加。
2. **Array Shapes（陣列形狀）**
   - **優點**：輕量，不需建立額外檔案；藉由 PHPStan 支援的 `@param array{...}` PHPDoc 註解，同樣能獲得靜態分析與型別檢查。
   - **寫法範例**：
     ```php
     /**
      * @param array{name: string, email: string} $data
      */
     public function handle(User $user, array $data): void
     ```

### 2. 資料庫事務與 Action 組合（Composition）

複雜的業務流程可以由高階 Action（Orchestrator）透過建構子注入多個小 Action 來完成，閱讀高階 Action 的 `handle()` 就能像看目錄一樣掌握整個業務步驟。當操作涉及多個資料表寫入或副作用時，通常由**最外層負責該用例的 Action** 包上 `DB::transaction()`，確保操作的原子性（Atomicity）。

```php
namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class UpdateUserAction
{
    // 透過建構子注入另一個 Action 用於建立活動紀錄
    public function __construct(
        private CreateActivityAction $createActivity
    ) {}

    /**
     * @param array{name: string, email: string} $data
     */
    public function handle(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update($data);

            // 呼叫組合的子 Action
            $this->createActivity->handle($user, 'profile_updated');

            return $user;
        });
    }
}
```

> - **巢狀事務**：Laravel 支援 Nested Transactions，因此在 Action 組合呼叫時，資料庫事務能在不同層級間正常運作，無需擔心衝突。
> - **避免過度嵌套**：Brent Roose 也提醒，Action 組合不宜過度深層嵌套，過深的依賴鏈會提高耦合度與閱讀成本。

---

## 主流流派、命名與目錄慣例

社群在命名、修飾詞與目錄結構上主要有兩大流派，**最重要的共識是：選一套風格並在專案內保持 100% 一致**。

| 項目 | Spatie / Brent Roose 流派 | Nuno Maduro / Laravel 原生流派 |
| :--- | :--- | :--- |
| **對外方法名** | `execute()` | `handle()`（與 Job / Listener / Command 一致） |
| **類別命名** | 動詞＋名詞＋`Action` 後綴（如 `CreateOrderAction`） | 動詞＋名詞，不加後綴（如 `CreateOrder`） |
| **類別修飾詞** | `class` / `readonly class` | `final readonly class` / `readonly class` |
| **目錄位置** | `app/Actions/{Domain}/` 或 `Domain/{Module}/Actions/` | 平坦結構 `app/Actions/` |

關於目錄結構，許多開發者在專案變大時會傾向建立多層的領域子資料夾（例如 `app/Actions/User/...`）；但 Nuno Maduro 更推崇**平坦的資料夾結構**，將所有 Action 放在 `app/Actions/` 底下。即使專案成長到超過 100 個 Action，在單一資料夾內透過檔名直接搜尋，往往比在繁複的目錄樹之間切換更直觀。

---

## 實務邊界與模式對比

### 1. Action vs. Service 怎麼選？

社群裡有一句很常被引用的口訣：

> **「Action 是你的應用程式『做什麼』；Service 是你的應用程式『用什麼』。」**

| 維度 | Action | Service |
| :--- | :--- | :--- |
| **職責** | 單一業務行為／用例（Use Case） | 一組相關功能或外部整合（第三方 API、金流、CDN、驅動切換） |
| **命名** | 動詞開頭（如 `CreateOrder`、`GenerateQuizSession`） | 名詞（如 `StripeGateway`、`CloudflareCdnService`） |
| **方法數** | 嚴格 1 個 `public` 方法 | 多個相關 `public` 方法（如 `charge()`、`refund()`） |
| **常見依賴方向** | 由上層呼叫並編排流程（`Controller -> Action -> Service`） | 執行底層或外部操作，不反向呼叫 Action |

**常見邊界誤區**：假設有一個放在 `app/Actions/` 底下的 `PurgeCdnCache`，對外同時暴露了 `purgeAudio()`、`purgePrefixes()`、`purgeFiles()` 三個 `public` 方法——依照上述準則，它其實本質上屬於封裝外部 CDN API 的 **Service**，應該移往 `app/Services/`，或是將每個動作拆成各自單一入口的 Action。

### 2. Action vs. Queue Job（非同步執行）

- **不要把業務邏輯直接寫死在 Job 裡**：因為 Laravel Job 的依賴注入發生在 `handle()` 而非 `__construct()`，很難像 Action 一樣互相組裝與重用。
- **推薦做法**：寫一個很薄的 Job，在其 `handle()` 中解析並呼叫對應的 Action；或者使用 `spatie/laravel-queueable-action` 套件。
- **關於 `lorisleiva/laravel-actions`**：這類讓同一個類別同時兼任 Controller + Job + Command + Action 的工具在社群中評價較兩極。中小型專案寫起來很快，但許多開發者認為這會把 HTTP / CLI 細節重新耦合進業務層，反而違背單一職責原則。

### 3. Action Pattern vs. Events & Observers

| 特性 | Action Pattern | Events & Observers |
| :--- | :--- | :--- |
| **明確度** | **高**（Explicit）。在呼叫端一目了然執行了什麼業務操作。 | **低**（Implicit）。事件派發後，背後觸發了哪些 Listeners 不易追蹤。 |
| **副作用控制** | **容易**。所有步驟皆明確編排於執行方法中。 | **困難**。執行 Migration 或背景 Task 時，可能意外觸發 Observer 而發出通知或修改資料。 |

### 4. Actions（寫入）vs. Queries（讀取）

- **Actions** 通常用於**寫入或變更系統狀態（Write Operations）**。
- 對於**讀取與查詢（Read Operations）**（例如取得產品列表），業界做法較多元：
  - 部分開發者會寫成 `ListProductsAction`。
  - 部分開發者會獨立建立 `app/Queries/` 資料夾（如 `ListProductsQuery.php`）。
  - 部分開發者則偏好直接在 Controller 中寫 inline query（例如 `Product::latest()->get()`）。
  - 這部分目前尚無統一標準，可視專案查詢複雜度彈性調整。

---

## 實務檢查清單（Checklist）

寫完一個 Action 後，可以用這五個問題快速自我檢查：

1. `public` 方法是不是只有一個 `handle()` / `execute()`（加上 `__construct()`）？
2. `__construct()` 裡是不是只有注入依賴，沒有混入執行期業務資料？
3. 執行方法裡是不是沒有 `Request` / `Response` / `Session` 等入口層物件？
4. 類別能不能標記成 `readonly`（不持有可變狀態）？
5. 如果未來要改從 Artisan Command 或 Queue Job 呼叫它，能不能直接一行 `$action->handle(...)` 無痛重用？
