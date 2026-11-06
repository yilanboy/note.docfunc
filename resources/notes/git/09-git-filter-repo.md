---
date: '2026-11-06'
tags: [git]
---

# 使用 git-filter-repo 徹底清理 Git 歷史紀錄（移除敏感檔案）

在開發過程中，如果不小心把敏感資訊（例如私鑰、密碼、Token、`.env` 檔案）或者龐大的二進位大檔案提交（Commit）進了 Git 倉庫，單純使用 `git rm` 刪除並發布新的 Commit **並無法解決安全隱患**。
因為 Git 是一個以內容定址的物件儲存庫，該檔案依然完整保存在歷史 Commit 的 Snapshot 中，任何人都能透過 `git checkout` 或 `git show` 翻出這筆敏感資料。

要徹底將檔案從專案歷史中抹除，我們需要進行「歷史重寫」（Rewrite History）。Git 官方過去曾提供 `git filter-branch`，但由於其效能極差且容易出錯，官方目前已正式棄用，並強烈推薦使用 **`git-filter-repo`**。

---

## 為什麼選擇 `git-filter-repo`？

- **速度極快**：比舊的 `git filter-branch` 快數十到數百倍。
- **安全性與保護機制**：預設要求乾淨的工作目錄，避免誤刪正在進行中的未追蹤檔案。
- **全面性**：重寫檔案結構的同時，會自動更新 Commit 訊息、Tag、以及處理孤立提交（Prune empty commits）。
- **官方推薦**：Git 官方手冊明確建議所有使用者遷移至 `git-filter-repo`。

---

## 安裝與執行方式

`git-filter-repo` 是一個基於 Python 的工具，您可以選擇以下任一種方式取得：

### 1. 使用 `uvx` 免安裝直接執行（最推薦）

如果您有安裝 Python 套件管理工具 `uv`，無需全局安裝即可隨用隨拋：

```bash
uvx git-filter-repo --version
```

### 2. 使用 Homebrew (macOS)

```bash
brew install git-filter-repo
```

### 3. 使用 pip

```bash
pip install git-filter-repo
```

---

## 實戰操作流程：以清除所有歷史 `.env` 為例

假設我們在過去的提交中誤將 Laravel 的環境變數設定檔案（`.env`）提交進版控，現在要徹底將所有 `.env` 抹除。

### 步驟 1：建立本地完整備份（重要）

重寫歷史是具破壞性的不可逆操作，**執行前務必完整備份整個專案資料夾**：

```bash
# 複製一份完整的專案副本至同層目錄
cp -r my-repo my-repo.backup
```

### 步驟 2：查詢目前包含該檔案的歷史 Commit

先確認哪些歷史 Commit 曾觸碰過目標檔案：

```bash
git log --all --oneline -- ".env"
```

### 步驟 3：執行 `git-filter-repo` 過濾目標檔案

切換到倉庫根目錄，執行過濾指令：

```bash
# --path: 指定目標檔案路徑
# --invert-paths: 排除符合條件的檔案，保留其餘檔案
# --force: 允許在非全新 clone 的乾淨 repo 內直接執行
uvx git-filter-repo \
  --path '.env' \
  --invert-paths \
  --force
```

> **注意與提示**：
> - `git-filter-repo` 會將符合條件的檔案在整個 Git 歷史中徹底**剔除**。
> - 若專案在子目錄（例如 Monorepo）也有 `.env`，可額外加上 `--use-base-name` 參數比對任意目錄下的同名檔案：`uvx git-filter-repo --path '.env' --use-base-name --invert-paths --force`。
> - 請避免隨意使用 `--path-glob '.env*'`，以免誤將公開範本檔（如 `.env.example`）一併刪除。

### 步驟 4：驗證歷史紀錄是否已完全抹除

1. 再次搜尋歷史紀錄，確認是否已無任何相關 Commit：

    ```bash
    git log --all --full-history -- ".env"
    ```

    _若完全沒有任何輸出，代表所有歷史 Commit 中的檔案已被乾淨移除。_

2. 檢查目前工作目錄的狀態：
    ```bash
    git status
    ```

### 步驟 5：重新加入 Remote 並強制推送（Force Push）

`git-filter-repo` 執行完成後，基於安全保護考量會**自動移除遠端儲存庫（origin）的連結**，防止使用者誤推。確認歷史無誤後，需手動重新加回遠端並強制覆蓋：

```bash
# 1. 重新加回 remote origin
git remote add origin git@github.com:your-org/my-repo.git

# 2. 強制推送所有分支與 tags
git push origin --force --all
git push origin --force --tags
```

---

## 常見實用情境與指令範例

### 1. 移除單一指定路徑的檔案

若只需移除特定的單一機敏檔案：

```bash
uvx git-filter-repo --path "config/credentials.json" --invert-paths --force
```

### 2. 移除整個目錄

若需要將整個歷史資料夾刪除（例如不小心提交的 `node_modules` 或 `build`）：

```bash
uvx git-filter-repo --path "build/" --invert-paths --force
```

### 3. 清理歷史大檔案（幫 Git 瘦身）

找出歷史紀錄中大於 50MB 的檔案並將其移除：

```bash
# 移除所有大於 50M 的檔案
uvx git-filter-repo --strip-blobs-bigger-than 50M --force
```

### 4. 替換 Commit 中的敏感字串（如密碼或 Token）

如果敏感密碼直接寫在程式碼文字中，可以使用 `--replace-text`：

1. 建立一個對應清單 `expressions.txt`：
    ```text
    # 格式為：要被替換的文字==>替換後的文字
    super_secret_api_key_12345==>REDACTED_API_KEY
    password123==>***
    ```
2. 執行替換：
    ```bash
    uvx git-filter-repo --replace-text expressions.txt --force
    ```

### 5. 拆分子目錄為獨立倉庫（Subdirectory Filter）

把現有專案的某個子資料夾獨立提出成一個全新的 Git 倉庫，且保留該子目錄所有的歷史：

```bash
uvx git-filter-repo --subdirectory-filter packages/my-lib --force
```

---

## 核心注意事項與資安守則

1. **Commit Hash 變更與團隊協作**：
    - 歷史重寫後，被修改 Commit 之後的所有 Commit SHA-1 都會改變。
    - 必須請團隊成員在 Force Push 後，**不要嘗試 git pull**（會將舊歷史重新 merge 回去），而是**重新 clone 專案**或使用 `git fetch && git reset --hard origin/main` 同步。
2. **金鑰輪替原則（Key Rotation）**：
    - **重寫 Git 歷史只能防止未來的洩漏與合規檢查失敗，不能視為「金鑰未曾洩漏」**。
    - 任何曾經被推送至遠端（特別是 GitHub/GitLab）的 Secret 或 Private Key，在資安規範上一律視為**已外洩**。
    - 重寫歷史的同時，請務必到外部系統（如 AWS、Akamai、第三方 API）**撤銷舊金鑰並生成全新的憑證**。

---

## 參考資料

- [GitHub - newren/git-filter-repo（官方原始碼倉庫）](https://github.com/newren/git-filter-repo)
- [git-filter-repo Documentation](https://htmlpreview.github.io/?https://github.com/newren/git-filter-repo/blob/docs/html/git-filter-repo.html)
- [Git 官方手冊 - git-filter-branch 替代方案推薦](https://git-scm.com/docs/git-filter-branch#_warning)
