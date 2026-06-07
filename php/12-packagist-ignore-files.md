---
layout: default
parent: PHP
nav_order: 12
---

# 讓 Packagist / Composer 排除不必要的檔案

當你的 PHP 套件在被人用 `composer require` 安裝時，應該把說明文件（如 README），還有開發用的檔案從中排除。

## 核心觀念

Packagist 本身**不儲存程式碼**，它只儲存指向 Git repository 的 metadata。

當使用者執行 `composer require <你的名稱>/<你的套件>` 時，Composer 並不是去 clone 整個 Git repository，而是下載一個稱為 **dist** 的壓縮檔。對於託管在 GitHub 上的套件，這個壓縮檔是由 GitHub 用 `git archive` 產生的。

關鍵就在這裡：`git archive` 會遵守 `.gitattributes` 裡的 `export-ignore` 指令。被標記為 `export-ignore` 的檔案或資料夾，**不會**被包含進這個壓縮檔。

## 做法

在專案根目錄的 `.gitattributes` 中，為不需要發佈的檔案或資料夾加上 `export-ignore`。

這麼做的好處是：檔案**仍然保留在 Git repository 裡**（在 GitHub 上仍然看得到、README 引用的圖片仍然能正常顯示），但**不會**
被打包進使用者下載的 Composer dist 壓縮檔。換句話說，開發體驗與發佈體積可以兼顧。

## 以我的套件的 `.gitattributes` 範例

我的 PHP 套件 `yilanboy/preview` 的 `.gitattributes` 範例如下：

```gitattributes
* text=auto eol=lf

*.php diff=php

/.git*         export-ignore
/tests         export-ignore
/images        export-ignore
/examples      export-ignore
/.editorconfig export-ignore
/phpstan.neon  export-ignore
/phpunit.xml   export-ignore
/CHANGELOG.md  export-ignore
```

逐條說明：

| 設定                           | 用途                                                                                                        |
| ------------------------------ | ----------------------------------------------------------------------------------------------------------- |
| `* text=auto eol=lf`           | 統一所有檔案的換行符為 LF，與 `export-ignore` 無關，是換行正規化設定。                                      |
| `*.php diff=php`               | 讓 `git diff` 對 `.php` 檔案使用 PHP 專屬的 hunk header，方便閱讀 diff，與發佈無關。                        |
| `/.git* export-ignore`         | 用一個 glob 一次涵蓋所有以 `.git` 開頭的項目：`.github/`（CI 設定資料夾）、`.gitattributes`、`.gitignore`。 |
| `/tests export-ignore`         | 排除測試資料夾，使用者執行時不需要。                                                                        |
| `/images export-ignore`        | 排除 README 用的截圖資料夾（只是文件用途，執行時用不到）。                                                  |
| `/examples export-ignore`      | 排除範例程式碼資料夾。                                                                                      |
| `/.editorconfig export-ignore` | 排除編輯器設定。                                                                                            |
| `/phpstan.neon export-ignore`  | 排除靜態分析設定。                                                                                          |
| `/phpunit.xml export-ignore`   | 排除測試框架設定。                                                                                          |
| `/CHANGELOG.md export-ignore`  | 排除變更紀錄（屬於開發 / repository 文件，執行時不需要）。                                                  |

> 注意 `/.git*` 這條：`*` 是 glob，會一次比對到 `.github`、`.gitattributes`、`.gitignore`，所以不必每個分別寫一行。

## 哪些該排除、哪些該保留

**通常該排除（開發專用檔案）：**

- 測試：`tests/`
- 範例：`examples/`
- 文件用圖片：`images/`
- CI 設定：`.github/`
- 靜態分析與測試設定：`phpstan.neon`、`phpunit.xml`
- 編輯器設定：`.editorconfig`
- 變更紀錄：`CHANGELOG.md`

**必須保留（執行時需要的東西）：**

- `src/` — 套件的程式碼本體。
- `composer.json` — 套件定義，沒有它 Composer 無法安裝。

## 重要陷阱：`gitattributes` 不是 `gitignore`

兩者語意不同，檢查方式也不同，容易踩雷。

用 `git check-attr export-ignore -- <path>` 檢查時，若你檢查的是**資料夾「裡面」的某個檔案**，可能會回報 `unspecified`。這**不代表設定沒生效**，而是因為 `export-ignore` 是套在資料夾本身上。

正確的做法是直接對**資料夾本身**檢查：

```bash
# 對資料夾裡的檔案檢查 —— 可能回報 unspecified（容易誤判）
git check-attr export-ignore -- .github/workflows/ci.yml

# 對資料夾本身檢查 —— 正確會回報 set
git check-attr export-ignore -- .github
```

## 如何驗證

最權威的驗證方式不是逐一檢查屬性，而是**直接產生 archive 來看實際內容**。

### 方法一：檢查單一路徑的屬性

```bash
git check-attr export-ignore -- tests
git check-attr export-ignore -- fonts
```

被排除的路徑會回報 `export-ignore: set`，要保留的路徑則是 `export-ignore: unspecified`。

### 方法二：列出實際會被打包的檔案（推薦）

```bash
git archive --worktree-attributes HEAD | tar -tf -
```

`--worktree-attributes` 會讀取**工作目錄中當前的 `.gitattributes`**，所以即使你還沒 commit，也能立刻驗證修改後的效果。

> 提醒：`git archive HEAD`（**不加** `--worktree-attributes`）讀取的是已經 commit 進去的 `.gitattributes`。如果你剛改完
> `.gitattributes` 還沒 commit，一定要加上 `--worktree-attributes`，否則看不到最新修改的效果。
