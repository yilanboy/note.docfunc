---
date: "2026-11-16"
tags: [omarchy]
---

# Omarchy 指令與外觀設定

本筆記記錄日常使用的 `omarchy` CLI 指令與桌面外觀設定技巧，方便日後查閱與擴充。

---

## 頂部 Bar 背景設定（`omarchy bar background`）

可以透過 `omarchy bar background` 指令快速調整頂部狀態列（Bar）的背景樣式與顏色呈現：

```bash
# 依序循環切換背景樣式（none -> glassy -> opaque）
omarchy bar background cycle

# 設定為不透明背景
omarchy bar background opaque

# 設定為霧面半透明（模糊顯示 bar 後方的內容）
omarchy bar background glassy

# 設定為透明（無背景）
omarchy bar background none

# 不帶參數：顯示目前的 bar 背景設定
omarchy bar background
```

| 參數     | 說明                                       |
| :------- | :----------------------------------------- |
| `cycle`  | 依序在 `none`、`glassy`、`opaque` 之間切換 |
| `opaque` | 不透明實色背景                             |
| `glassy` | 霧面毛玻璃半透明效果，會模糊後方視窗內容   |
| `none`   | 移除背景（完全透明）                       |

---

## 更換桌面背景（Background）

### 透過 Omarchy 選單新增與切換

1. 打開 **Omarchy 選單**，選擇 **Install -> Style -> Background**（系統會開啟當前主題的使用者背景資料夾 `~/.config/omarchy/backgrounds/<主題名稱>/`）。
2. 將想要當作背景的圖片放入該資料夾中。
3. 放入後，即可在 **Background** 選單中看到並選擇套用新的背景圖片。

### 常用背景相關 CLI 指令

若習慣直接在終端機操作，也可使用 `omarchy theme bg` 系列指令：

```bash
# 開啟當前主題的使用者背景資料夾
omarchy theme bg install

# 開啟背景選擇器（Background Switcher）
omarchy theme bg-switcher

# 直接指定圖片或影片檔案設為背景
omarchy theme bg set <path-to-media>

# 切換至下一張背景
omarchy theme bg next

# 查看目前的背景設定
omarchy theme bg current
```
