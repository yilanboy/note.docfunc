---
date: '2026-10-09'
tags: [laravel, http, cache]
---

# 利用 HTTP ETag 避免重複拉取資源

在 Web 應用程式中，前端經常需要向伺服器拉取較大的資料集，例如全站搜尋索引（Search Index JSON）、字典檔或系統配置。這些資料雖然體積偏大，但變更頻率通常不高。

如果每次使用者觸發功能時都無條件下載完整的 JSON，即使後端已使用快取（如 Redis）加速，仍會消耗寶貴的網路傳輸頻寬與延遲。透過 **HTTP 條件式請求（Conditional Request）** 與 **ETag（實體標記，Entity Tag）**，當資源內容未變更時，伺服器只需回應 `304 Not Modified`（不含 Body），就能大幅節省傳輸量並提升回應速度。

---

## 什麼是 ETag 與條件式請求？

**ETag（Entity Tag）** 是 HTTP 回應標頭（Response Header）中的一個字串識別碼，代表特定版本資源的內容摘要或指紋（Fingerprint）。

### 運作流程

```
[ 用戶端 (Client) ]                              [ 伺服器 (Server) ]
        |                                                |
        |  1. 初次請求: GET /api/search-index             |
        |----------------------------------------------->|
        |                                                | 計算資源 Hash (如 md5)
        |  2. 回傳 200 OK + ETag: "abc123" + 完整內容      |
        |<-----------------------------------------------|
        | (快取內容與 ETag: "abc123")                     |
        |                                                |
        |  3. 再次請求: GET /api/search-index             |
        |     Header: If-None-Match: "abc123"            |
        |----------------------------------------------->|
        |                                                | 比對指紋是否一致:
        |                                                | 一致 -> 內容未變更
        |  4. 回傳 304 Not Modified (空 Body)             |
        |<-----------------------------------------------|
        | (直接使用本地快取資料)                           |
```

1. **初次請求**：用戶端請求資源，伺服器計算該內容的指紋（如雜湊值），透過 `ETag` 標頭回傳，並設定快取驗證原則。
2. **後續請求**：用戶端再次請求時，瀏覽器或客戶端會自動在標頭帶上 `If-None-Match: "<前次 ETag>"`。
3. **比對驗證**：伺服器若發現資源指紋與 `If-None-Match` 一致，直接回傳 `304 Not Modified` 且不包含任何回應主體（Body）。

---

## 強 ETag vs. 弱 ETag

ETag 分為兩種形式：

- **強 ETag（Strong ETag）**：例如 `"abc123"`。表示資源的每一個 Byte 都完全一致（Byte-for-byte identical）。
- **弱 ETag（Weak ETag）**：以 `W/` 開頭，例如 `W/"abc123"`。表示資源在**語意上等價（Semantically equivalent）**，但位元組層級可能略有差異。

### 為什麼後端必須同時支援弱 ETag？

許多反向代理伺服器（如 Nginx、Caddy、Cloudflare）在開啟 Gzip 或 Brotli 動態壓縮時，會將後端回傳的強 ETag 自動轉換為弱 ETag（因為壓縮後的 Byte 流不等於原始內容）。

當瀏覽器後續發出請求時，請求標頭很可能帶的是 `If-None-Match: W/"abc123"`。如果後端程式碼僅嚴格比對 `"abc123"`，就會造成判斷失敗，導致每次請求都被當成內容已變更而回傳 `200 OK` 下載完整內容。

因此，後端在比對 `If-None-Match` 時，務必**同時相容強 ETag 與弱 ETag**。

---

## 在 Laravel 中實作 ETag 快取

以下以提供「搜尋索引」的 Controller 為例，實作輕量指紋比對與 304 狀態碼回傳：

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\NoteRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchIndexController extends Controller
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
    ) {}

    /**
     * 回傳搜尋索引 JSON，並透過 HTTP ETag 實作條件式快取
     */
    public function __invoke(Request $request): Response
    {
        // 1. 取得輕量資源指紋並計算 ETag
        $etag = md5($this->noteRepository->fingerprint());

        // 2. 判斷客戶端 If-None-Match 是否相符（同時支援強 ETag 與弱 ETag）
        if (
            in_array('"'.$etag.'"', $request->getETags(), true) ||
            in_array('W/"'.$etag.'"', $request->getETags(), true)
        ) {
            return response('', Response::HTTP_NOT_MODIFIED, [
                'ETag' => '"'.$etag.'"',
                'Cache-Control' => 'public, max-age=0, must-revalidate',
            ]);
        }

        // 3. 資源已有更新或初次載入，生成完整資料並附上 ETag 標頭
        $response = response()->json($this->noteRepository->searchIndex());
        $response->setEtag($etag);
        $response->headers->set('Cache-Control', 'public, max-age=0, must-revalidate');

        return $response;
    }
}
```

### 關鍵細節解析

1. **`$request->getETags()`**：
   Symfony 的 Request 物件內建 `getETags()` 方法，能自動解析 `If-None-Match` 標頭中以逗號分隔的多個 ETag，並保留 `W/` 前綴與雙引號。
2. **`Cache-Control: public, max-age=0, must-revalidate`**：
   - `max-age=0`：告知瀏覽器快取過期時間為 0，表示每次使用快取前**必須向伺服器重新驗證**（Revalidate）。
   - `must-revalidate`：伺服器斷線或異常時，不允許回傳過期的陳舊快取（Stale Cache）。
   - 此設定能確保客戶端「永遠能取得最新狀態」，且「沒有更新時只消耗最微小的 304 驗證封包」。

---

## 前端配合機制

### 1. 瀏覽器原生 `fetch()` 快取

在一般瀏覽器環境下，使用標準的 `fetch()` 發送 GET 請求，瀏覽器本身的 HTTP 快取層就會自動管理 ETag 與 `If-None-Match`：

```javascript
// 瀏覽器會自動在 Request Header 帶入 If-None-Match
// 若伺服器回傳 304，瀏覽器會透明地從內部 HTTP 快取取出內容並回傳
const res = await fetch('/api/search-index');
const data = await res.json();
```

### 2. 單頁應用（SPA）/ 記憶體與 SessionStorage 快取

若前端希望在記憶體或 `sessionStorage` 自行管理快取狀態，可手動記錄 `ETag` 標頭並傳入 `If-None-Match`：

```typescript
let cachedData: SearchIndex[] | null = null;
let cachedETag: string | null = sessionStorage.getItem('search_etag');

async function getSearchIndex(): Promise<SearchIndex[]> {
  const headers: HeadersInit = {};
  if (cachedETag) {
    headers['If-None-Match'] = cachedETag;
  }

  const response = await fetch('/api/search-index', { headers });

  // 伺服器回傳 304，表示本地快取仍為最新，直接使用
  if (response.status === 304 && cachedData) {
    return cachedData;
  }

  // 內容已變更，更新 ETag 與快取
  if (response.ok) {
    cachedETag = response.headers.get('ETag');
    if (cachedETag) {
      sessionStorage.setItem('search_etag', cachedETag);
    }
    cachedData = await response.json();
    return cachedData;
  }

  throw new Error('無法取得搜尋索引');
}
```

---

## 補充：If-Match 與防止更新衝突（Lost Update Problem）

除了搭配 `If-None-Match` 進行讀取快取外，ETag 還有另一個極為重要的用途：搭配 **`If-Match`** 實作**樂觀併發控制（Optimistic Concurrency Control）**，防止**遺失更新問題（Lost Update Problem）**。

### 什麼是 Lost Update Problem？

想像以下多使用者編輯的情境：

1. **Alice** 與 **Bob** 同時打開同一篇文章進行編輯，此時該文章版本為 `ETag: "v1"`。
2. Alice 編輯速度較快，花了一分鐘後送出更新，伺服器儲存成功並將文章版本推進為 `ETag: "v2"`。
3. Bob 編輯了五分鐘後按下送出。在沒有併發控制的情況下，Bob 的提交會**無聲無息地覆蓋** Alice 剛剛寫入的內容，導致 Alice 的修改遺失。

### 利用 `If-Match` 解決衝突

客戶端在送出修改請求（如 `PUT`, `PATCH`, `DELETE`）時，將上次讀取到的版本透過 `If-Match` 帶給伺服器：

```http
PUT /api/articles/42 HTTP/1.1
If-Match: "v1"
Content-Type: application/json

{ "title": "Bob 修改的標題", "content": "..." }
```

- **伺服器驗證**：
  - 若目前伺服器上的版本仍為 `"v1"`，表示期間沒有其他人修改，允許更新並回傳 `200 OK` 及新的 `ETag: "v2"`。
  - 若目前伺服器上的版本已被 Alice 改為 `"v2"`，與 Bob 的 `If-Match: "v1"` 不符，伺服器立即拒絕請求，並回傳 **`412 Precondition Failed`**。
- **前端處理**：前端收到 `412` 錯誤時，可提示使用者「該資源已被他人修改」，並提供內容差異比對或讓使用者重新整理，避免直接覆蓋他人修改。

### 在 Laravel 中實作範例

```php
public function update(Request $request, Article $article): Response
{
    $currentEtag = '"' . md5((string) $article->updated_at->timestamp) . '"';

    // 檢查客戶端是否附帶 If-Match 條件
    if ($request->hasHeader('If-Match')) {
        $ifMatch = $request->header('If-Match');

        // If-Match: * 表示「只要資源存在即可」
        // 若指定特定 ETag 且與當前版本不符，中斷並回傳 412
        if ($ifMatch !== '*' && $ifMatch !== $currentEtag) {
            return response()->json([
                'message' => '資源已被修改，請重新整理取得最新版本後再試。',
            ], Response::HTTP_PRECONDITION_FAILED); // 412
        }
    }

    $article->update($request->validated());

    $newEtag = '"' . md5((string) $article->fresh()->updated_at->timestamp) . '"';

    return response()->json($article, Response::HTTP_OK, [
        'ETag' => $newEtag,
    ]);
}
```

> [!NOTE]
> 根據 RFC 9110 規範：
> 1. 用於狀態變更（如 `PUT` / `PATCH`）的 `If-Match` 比對時，原則上必須使用**強 ETag**，因為修改行為必須確保基於完全確定的位元組版本。
> 2. `If-Match: *` 具有特殊意義，代表「只要該資源目前存在即可執行」，常用於避免意外建立多個重複實體。

---

## 實務注意事項

1. **ETag 計算成本必須遠小於生成完整內容**：
   ETag 的好處建立在「能快速得知內容有無變更」。如果為了算 ETag 必須先執行耗時的全表查詢與資料序列化，就失去了節省後端 CPU 與查詢開銷的意義。建議透過輕量指紋（例如關聯檔案的最新 `filemtime`、資料庫 `MAX(updated_at)`、或版本號）來計算 ETag。
2. **反向代理壓縮設定**：
   若使用 Nginx，確認反向代理設定未強制抹除 `ETag` 標頭。
3. **避免 304 回應夾帶 Body**：
   根據 RFC 規範，HTTP 304 回應**不得包含 Message Body**。在 Laravel 中應以 `response('', 304, ...)` 回傳空字串。

---

## 參考資料

- [MDN Web Docs - ETag](https://developer.mozilla.org/zh-TW/docs/Web/HTTP/Reference/Headers/ETag)
