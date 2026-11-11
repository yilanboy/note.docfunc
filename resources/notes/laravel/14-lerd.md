---
date: "2026-11-11"
tags: [laravel, lerd, omarchy]
---

# Lerd 安裝筆記 (Omarchy)

[Lerd](https://lerd.sh/) 是一款基於 rootless Podman 的本地 PHP 開發環境，專為 Linux、macOS 與 Windows 設計。免去手動配置 Nginx、PHP-FPM、SSL 憑證與 DNS 的繁瑣步驟，自動支援 `*.test` 網域與 HTTPS。

本篇筆記以 **Omarchy** (Arch + Hyprland) 環境為基準撰寫。

---

## 1. 安裝 Lerd

在 Omarchy 下使用官方 Shell Script 進行安裝：

```bash
curl -fsSL https://lerd.sh/install.sh | bash
```

Lerd 建議 Runtime 使用 crun，可以透過 `pacman` 安裝：

```bash
sudo pacman -S crun
```

如果想要透過 `lerd secure` 幫網站啟用 TLS，Lerd 需要 `nss` 套件中的 `certutil` 工具，用於在瀏覽器中安裝 TLS 憑證：

```bash
sudo pacman -S nss
```

上述步驟完成後就可以使用 `lerd doctor` 檢查 Lerd 安裝是否成功：

```bash
lerd doctor
```

### 安裝過程重點

- **Sudo 權限**：僅在安裝初期要求一次 `sudo`（用於綁定 80/443 port 的 sysctl、設定 systemd linger 以及設定 `systemd-resolved` 的 `.test` 網域名稱解析），後續日常使用完全不需 `sudo`。
- **環境設定**：安裝程式會將二進位檔安裝至 `~/.local/bin/lerd` 並自動加入 PATH。
- 安裝完成後，請重新載入 Shell 或開啟新終端機使 PATH 生效。

---

## 2. Omarchy 注意事項

Omarchy 預裝了 Arch Linux 與 Hyprland 桌面環境，具備較新的 Podman（>= 4.5），非常適合搭配 Lerd：

- **系統托盤與 Glance 外掛**：在 Omarchy 上安裝時，Lerd 預設會關閉系統匣圖示（`lerd tray off`），並自動整合 Omarchy 頂部狀態列的 Glance 外掛（顯示站點、服務、容器記憶體等資訊）。
- **服務管理**：所有容器皆作為 Podman Quadlet 運行，背景 Worker 則透過 systemd user 服務管理，設定檔集中在 `~/.config/lerd` 與 `~/.local/share/lerd`，不污染系統全域目錄。

---

## 3. 專案綁定與啟動

進入 Laravel 或 PHP 專案目錄後執行：

```bash
cd ~/code/my-project
lerd link
```

綁定後即可直接透過 `https://my-project.test` 造訪專案。

---

## 4. 前端 Package Manager 注意事項

Lerd 預設會根據專案前端檔案（如 `package.json` 中的 `packageManager` 或 lockfile）偵測包管理器：

- **npm**：Lerd 內建提供了 host 端的執行封裝（路徑為 `~/.local/share/lerd/bin/npm`）。
- **pnpm**：目前 Lerd 預設尚未原生內建 pnpm 的 shim 封裝，官方預期會在未來版本完善支援。目前若在 host 執行建議直接透過系統或 Corepack 安裝的 pnpm。

---

## 5. Pest Browser Testing (Playwright) 設定

Pest v4 後加入的瀏覽器測試（`pestphp/pest-plugin-browser`）使用 Playwright 本地驅動 Chromium。在 Lerd 中，測試程序跑在 PHP-FPM 容器內，因此需要特別配置：

### 安裝步驟

```bash
# 1. 安裝 Pest Browser Plugin 與 Playwright
lerd composer require --dev pestphp/pest-plugin-browser
lerd npm install playwright

# 2. 安裝 Lerd 瀏覽器支援環境
lerd pest:browser install
```

`lerd pest:browser install` 會自動將 Alpine 原生 Chromium 及 Xvfb 烘焙入 FPM 容器鏡像中，並將 Lerd 本地根憑證匯入 Chromium。

### 相關指令

```bash
# 診斷瀏覽器測試環境
lerd pest:browser doctor

# 執行測試
lerd test
lerd test --parallel

# 移除瀏覽器環境
lerd pest:browser remove
```

---

## 6. 疑難雜症專區 (Troubleshooting)

### Q1: Pest Browser 測試截圖上的中文字全部變成空白？

- **現象**：測試失敗產生的截圖 PNG 中，畫面元件正常，但所有中文文字皆呈透明或空白，只有英數字與圖示可見。
- **原因**：Lerd 的 PHP 容器鏡像（Alpine Linux）預設僅裝有英文字型（`Open Sans`），沒有 CJK 中文字型，導致容器內的 Chromium 在無頭模式下無法繪製中文字形（但 DOM 中的字串依然存在，因此純文字比對仍可能過關）。
- **解法**：在 Lerd 容器中加入 Alpine 的 Noto CJK 字型套件：

    ```bash
    lerd php:pkg add font-noto-cjk
    ```

### Q2: Pest Browser 測試在同一個測試內有多個 `visit()` 導致斷言失敗？

- **現象**：在同一個 `test(...)` 內先後 `visit('/page-a')` 與 `visit('/page-b')`，後續斷言找不到元素，檢查截圖發現畫面仍停留在前一個頁面。
- **原因**：Pest Browser Plugin 在同一測試內重複呼叫 `visit()` 時，不會重新乾淨初始化 Playwright Context 與 Laravel 測試 Session / 登入態，導致後續頁面狀態錯亂。
- **解法**：
    1. 將不同頁面的測試**拆分成獨立的測試案例**，每個案例只包含單一 `visit()`。
    2. 若要測試同一頁面流程內的換頁導覽，應使用 `->click(...)` 點擊畫面上的連結或按鈕進行跳轉，而非多次呼叫 `visit()`。

## 參考資料

- [Lerd 官方文檔](https://lerd.sh/getting-started/)
