---
date: '2026-09-21'
tags: [omarchy]
---

# Omarchy 初始化設定指南

本筆記彙整新安裝 **Omarchy**（基於 Arch Linux + Hyprland / Wayland）後的初始化環境配置，包含中文字型安裝、繁體中文輸入法（Fcitx5 + 新酷音）、鍵盤按鍵映射（Alt 與 Super 對調、Caps Lock 轉 Ctrl）、Foot 終端機字體與顯示調整，以及 Mise 套件管理工具的清理技巧。

---

## 1. 系統特點與環境說明

Omarchy 是一套鍵盤導向的桌面環境配置，底層採用 Hyprland (Wayland) 與 `uwsm` 會話管理：

* **輸入法預整合**：系統在使用者登入時會自動在背景常駐執行 **Fcitx5**，並已設定好 Wayland、Qt、GTK 相關的輸入法環境變數，不需要手動在 `hyprland.conf` 添加 `exec-once`。
* **終端機**：預設採用極輕量且支援 GPU 加速的 **Foot** 終端機。
* **工具鏈管理**：系統整合 **Mise** 作為多語言執行環境與 CLI 工具管理工具。

---

## 2. 安裝中文字體（解決缺字與豆腐方塊）

新系統若缺少中文字型，終端機與各應用程式可能會顯示為空白方塊或缺字。建議安裝 Google Noto CJK 與開源思源字型：

```bash
# 1. 安裝 Google Noto CJK 字型與 Emoji（涵蓋繁體中文、簡體中文、日韓文與圖示）
sudo pacman -S --needed noto-fonts-cjk noto-fonts-emoji

# 2. （可選）安裝思源黑體 / 思源宋體繁體中文版
sudo pacman -S --needed adobe-source-han-sans-tw-fonts adobe-source-han-serif-tw-fonts

# 3. 重新整理系統字型快取
fc-cache -fv
```

---

## 3. 繁體中文輸入法安裝與設定 (Fcitx5 + Chewing)

因為 Omarchy 已經預先常駐 Fcitx5 服務，只需安裝輸入法引擎與設定工具即可啟用。

### 步驟 A：安裝輸入法引擎與配置工具

以台灣最常見的「新酷音注音」為例：

```bash
sudo pacman -S --needed fcitx5-chewing fcitx5-configtool
```

> **其他輸入法選項**：
> - **Rime（中州韻）**：支援注音、倉頡、拼音等多種方案：
>   ```bash
>   sudo pacman -S --needed fcitx5-rime rime-data fcitx5-configtool
>   ```
> - **倉頡 / 速成**：
>   ```bash
>   sudo pacman -S --needed fcitx5-table-extra fcitx5-configtool
>   ```

### 步驟 B：啟用輸入法

#### 方法 1：圖形介面設定（推薦）

1. 在終端機執行或透過應用程式啟動器開啟設定工具：
   ```bash
   fcitx5-configtool
   ```
2. 切換至 **「輸入法 (Input Method)」** 分頁。
3. 取消勾選下方的 **「僅顯示目前語言 (Only Show Current Language)」**。
4. 搜尋 **Chewing**（新酷音）。
5. 選取後點擊 **`>`** 按鈕加入左側清單（放置在 `Keyboard - English (US)` 下方）。
6. 點擊右下角 **「套用 (Apply)」**。

#### 方法 2：命令列直接寫入設定檔

如果偏好直接透過終端機配置：

```bash
# 1. 將 chewing 加入 profile
cat << 'EOF' >> ~/.config/fcitx5/profile

[Groups/0/Items/1]
Name=chewing
EOF

# 2. 重新載入 Fcitx5 服務
fcitx5 -r -d
```

### 步驟 C：切換與常用快捷鍵

* **切換輸入法**：預設為 `Ctrl + Space`。
* **調整快捷鍵**：可在 `fcitx5-configtool` 中的 **「全域選項 (Global Options)」->「切換輸入法」** 自訂（如改為 `Super + Space`）。

### 常見問題排除

1. **按 `Ctrl + Space` 無法切換**：
   - 確認 Fcitx5 是否正在運作：`pgrep fcitx5`。
   - 若未啟動，手動在背景執行：`fcitx5 -d`。
2. **部分應用程式無法輸入中文**：
   - 檢查 `~/.config/fcitx5/profile` 清單中是否已正確加入 `chewing`。
   - 登出桌面會話（Logout）並重新登入，確保 Wayland/Qt/GTK 的 IM 通訊重新對齊。

---

## 4. 鍵盤鍵位映射與修飾鍵對調 (Hyprland / XKB)

為了符合慣用的鍵盤佈局（如 Mac 鍵位習慣），可在 Hyprland 的 XKB 設定中將 **Alt 與 Super (Win/Cmd) 對調**，並將 **Caps Lock 映射為 Ctrl**。

### 設定檔位置

* **路徑**：`~/.config/hypr/input.lua`
* **快捷開啟**：按下 `Super + Space` 打開 Omarchy 選單，選擇 **Setup** -> **Input**。

### 設定內容

編輯 `~/.config/hypr/input.lua`，修改 `kb_options`（多個選項以逗號 `,` 分隔）：

```lua
hl.config({
    input = {
        -- 1. ctrl:nocaps: Caps Lock 轉為 Ctrl
        -- 2. altwin:swap_alt_win: Alt 與 Super 鍵位置對調
        kb_options = "ctrl:nocaps,altwin:swap_alt_win",
    },
})
```

### 常用參數對照表

| 參數 | 作用 | 說明 |
| :--- | :--- | :--- |
| `ctrl:nocaps` | Caps Lock 變 Ctrl | 將 Caps Lock 映射為額外的 Ctrl（最推薦） |
| `ctrl:swapcaps` | Caps 與 Ctrl 對調 | 左下角原 Ctrl 變為 Caps Lock，Caps Lock 變 Ctrl |
| `altwin:swap_alt_win` | 全域 Alt / Super 對調 | 左右兩側的 Alt 與 Super 同時對調（適合 Mac 鍵位） |
| `altwin:swap_lalt_lwin` | 僅左側 Alt / Super 對調 | 僅對調左側按鍵，右側鍵位維持預設 |

### 套用與注意事項

1. **套用變更**：儲存檔案後通常會自動熱重載；若未生效可執行：
   ```bash
   hyprctl reload
   ```
2. **避免衝突**：若設定檔原本含有 `compose:caps`，請移除以避免與 `ctrl:nocaps` 發生衝突。
3. **快捷鍵影響**：此設定是在底層（XKB）對調，因此 Omarchy 系統中所有依賴 `Super` 的快捷鍵（例如視窗切換、選單呼叫）將直接跟隨對調後的實體鍵（即 Mac 鍵盤上的 Command 位置）。

---

## 5. Foot 終端機字體與顯示設定

Omarchy 預設使用 Foot 作為終端模擬器，可透過設定檔或系統指令進行外觀與字型調整。

### 設定檔修改

* **檔案路徑**：`~/.config/foot/foot.ini`

編輯 `~/.config/foot/foot.ini` 中的 `[main]` 區塊：

```ini
[main]
# 主要英文字型 + Fallback 中文字型支援
font=JetBrainsMono Nerd Font:size=16, Noto Sans CJK TC:size=16

# （可選）粗體與斜體字型
font-bold=JetBrainsMono Nerd Font:weight=bold:size=16
font-italic=JetBrainsMono Nerd Font:slant=italic:size=16
```

常見調整：
* **更換字型大小**：修改 `:size=` 數值（單位為 pt）。
* **更換主要字型**：改用其他 Nerd Font（如 `FiraCode Nerd Font:size=15`）。
* **生效方式**：儲存後開啟新終端機視窗（`Super + Enter`）即可見到變更。

### 即時臨時縮放快捷鍵

於終端機視窗內可即時調整大小（僅影響當前視窗，關閉後恢復設定檔預設）：

| 操作 | 快捷鍵 |
| :--- | :--- |
| **放大字體** | `Ctrl` + `+`（或 `Ctrl` + `Shift` + `=`） |
| **縮小字體** | `Ctrl` + `-` |
| **重置大小** | `Ctrl` + `0` |

### Omarchy 系統層級調整方式

除了手動編輯 `foot.ini`，也可利用 Omarchy 內建指令或選單進行全局連動調整：

* **全局文字大小（連動 Shell、GTK 應用與終端機）**：
  ```bash
  omarchy display text size 16
  ```
* **更換全局系統等寬字體**：
  ```bash
  # 查詢可用字型清單
  omarchy font list

  # 套用指定字型
  omarchy font set <字體名稱>
  ```
* **選單介面操作**：
  按下 `Super + Space`：
  - 前往 **Style > Font** 選擇字型。
  - 前往 **Display** 調整文字縮放比例。

> **注意**：執行 `omarchy font set` 或透過選單更換字型主題時，系統可能會覆寫 `foot.ini`。若發現字體大小跑掉，可重新確認該檔案中的 `:size` 設定。

---

## 6. Mise 工具管理與 Agent 徹底移除

Omarchy 內建整合 Mise 來管理 CLI 工具。在使用過程中，若直接使用 `mise uninstall` 移除工具（例如 AI CLI Agent `gemini`），常會發生在 `mise ls` 中持續殘留 `(missing)` 狀態的問題。

### 問題原因

`mise uninstall <tool>` 指令**只會刪除**已下載的二進位檔案本體，**不會自動清除**設定檔中的工具宣告：
* 全域設定檔：`~/.config/mise/config.toml`
* 專案設定檔：`mise.toml`

因此，Mise 在比對設定檔時發現工具已宣告卻找不到本體，便會標記為 `(missing)`。

### 徹底移除方法

#### 方法 A：使用命令列（推薦）

使用 `mise unuse`（或縮寫 `mise rm`），這會同時移除二進位檔案與設定檔宣告：

* **全域工具（Global）：**
  ```bash
  mise unuse -g gemini
  # 或使用別名
  mise rm -g gemini
  ```

* **單一專案工具（Local）：**
  在專案目錄下執行：
  ```bash
  mise unuse gemini
  # 或
  mise rm gemini
  ```

#### 方法 B：手動編輯設定檔

1. 開啟全域設定檔：
   ```bash
   nano ~/.config/mise/config.toml
   ```
2. 在 `[tools]` 區塊中，刪除該工具的宣告行：
   ```toml
   [tools]
   # 刪除不需要的工具，例如：
   gemini = { version = "latest", allow_builds = ["@github/keytar", "node-pty"] }
   ```
3. 存檔後退出。

### 驗證清除結果

執行以下指令確認工具已完全移除：

```bash
# 1. 確認清單中不再顯示該工具
mise ls gemini

# 2. 確認路徑解析已找不到該執行檔
mise which gemini
```
