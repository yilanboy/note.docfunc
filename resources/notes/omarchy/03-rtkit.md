---
date: "2026-11-27"
tags: [omarchy]
---

# RTKit 介紹與 RLIMIT_RTTIME 排程限制排錯

本筆記說明 **rtkit（RealtimeKit）** 在 Arch Linux / Omarchy 桌面環境中的用途、它與 PipeWire 及 `xdg-desktop-portal` 之間的運作機制，以及未安裝 `rtkit` 時為何會導致從 Omarchy 選單啟動的應用程式（例如 **PhpStorm** 及其子行程 **Google Antigravity ACP**、**Zed** 及其 **Agy / ACP**、以及各種 Electron / Chromium 應用）繼承 `RLIMIT_RTTIME = 0`，進而觸發 Linux Kernel 無聲 `SIGKILL`（`exit code 137`）。在目前的 Omarchy x86_64 穩定版中，這是**務必手動確認並補裝的基礎套件**。

---

## 什麼是 rtkit（RealtimeKit）？

**`rtkit`**（RealtimeKit）是一個極輕量（安裝大小約 90 KB）的系統服務（`rtkit-daemon.service`），在 Arch Linux 上由 PipeWire 團隊於官方套件庫維護（`extra/rtkit`）。

它的核心角色是作為**即時排程（Realtime Scheduling）的 D-Bus 守門員**，讓沒有 `root` 權限的一般使用者程式，能夠安全地向系統申請即時排程優先權（如 `SCHED_FIFO` / `SCHED_RR`）。

### 白話理解：為什麼沒有它會這麼嚴重？

對不太熟悉 Linux 底層的人來說，可以用「**高速公路 VIP 快速通道**」來理解：

1. **平常大家公平排隊**：電腦的 CPU 就像高速公路，平常所有程式（瀏覽器、編輯器、終端機）都照順序排隊使用運算資源。
2. **音訊處理需要救護車通道**：播音樂、語音通話（PipeWire）對時間非常敏感，只要系統一忙搶不到 CPU 就會爆音或斷線，因此它需要走「VIP 快速通道」（即時排程）。
3. **`rtkit` 是發「限時通行證」的交通警察**：Linux 核心很小心，為了避免有程式寫壞而霸佔 VIP 通道把整台電腦卡死，平常不准一般程式隨便開特權。`rtkit` 就是負責審核並發放「**每次最多 200 毫秒（`200000` µs）**」限時通行證的管理員，超時就會強制收回，既流暢又安全。
4. **少了 `rtkit` 會發生什麼災難？**
   - 當 Omarchy 的桌面選單（`quickshell`）啟動時，會透過 `xdg-desktop-portal` 問系統自己有多少 VIP 通道配額。
   - 因為沒有裝 `rtkit`，管理員不在崗位上，系統只好回報「**配額 = `0` 微秒**」。
   - 選單把「`0` 微秒」當成自己的硬規定，而你之後透過 `Super + Space` 選單打開的所有程式（**PhpStorm**、**Zed**、VS Code、瀏覽器等）以及它們生出來的子行程（例如 **Agy**），都會原封不動繼承這條「`0` 微秒」規則。
   - 只要這些程式在執行過程中碰到相關的計時或排程機制，Linux 核心的保護機制看到額度是 `0`，就會直接「一刀砍死」（送出 `SIGKILL`，即 `exit code 137`）——而且**不會跳任何系統錯誤視窗或留下崩潰紀錄**，看起來就像程式莫名其妙突然閃退或失敗。

---

## 為什麼 Linux / Omarchy 桌面環境需要 rtkit？

| 面向 | 說明 |
| :--- | :--- |
| **低延遲音訊處理** | PipeWire / PulseAudio 等音訊服務對時間非常敏感。若系統在高負載（例如編譯程式）時音訊執行緒搶不到 CPU，就會產生爆音、破音或延遲；透過即時排程可確保音訊流暢。 |
| **系統安全把關** | Linux 預設不允許一般使用者任意開啟即時排程，避免即時執行緒進入無窮迴圈導致整個系統卡死。 |
| **`RLIMIT_RTTIME` 與 Watchdog** | `rtkit` 要求申請方先設定 **`RLIMIT_RTTIME`**（預設上限為 `200000` µs，即 200ms），並搭配 watchdog 監控。若即時執行緒佔用 CPU 過久未交出控制權，會被降級或中止，防止系統當機。 |
| **避免選單子行程繼承 `0` 額度** | 有了 `rtkit`，`xdg-desktop-portal` 才能回報正常的即時排程預算，避免 `quickshell` 及其啟動的所有桌面應用被套上 `RLIMIT_RTTIME = 0`。 |

---

## Omarchy 各版本預設安裝狀態（必裝檢查）

雖然 `rtkit` 對桌面穩定度非常關鍵，但在 Arch Linux 上它僅被列為 `pipewire` 與 `xdg-desktop-portal` 的「選用相依套件（Optional Dependency）」，不會自動被拉進系統。目前各版本與平台的狀態如下：

| 版本 / 平台 | 預設是否安裝 `rtkit` | 說明 |
| :--- | :--- | :--- |
| **x86_64 穩定版（`v4.5.0` / `4.5.0-3`）** | **否（務必手動補裝）** | 本機 `/usr/share/omarchy/install/omarchy-base.packages` 與 `omarchy` 套件相依清單中皆未包含 `rtkit`。上游雖已修復此問題，但如 Issue `#18741` 回報，`v4.5.0` 穩定版切出分支時漏掉了該修正。 |
| **aarch64 平台（ARM / Apple Silicon 等）** | **是** | 已明確列入 `install/omarchy-aarch64.packages`（及對應的系統遷移腳本），開機即預設安裝。 |
| **上游主線（後續更新版本）** | **已納入修正** | 上游已於 Issue `#16348` 追蹤此問題，並透過 PR `#16824`（*Give PipeWire realtime with rtkit*）將 `rtkit` 納入 x86 基礎套件清單，後續穩定版發布後預計預設內建。 |

> **建議**：剛安裝完或升級 Omarchy 後，都先執行 `pacman -Q rtkit` 檢查一次；若顯示未安裝，請立即補裝。

---

## 常見問題：未安裝 rtkit 導致 RLIMIT_RTTIME = 0 與 exit code 137

在未安裝 `rtkit` 的 Omarchy 系統上，常會出現「從終端機啟動正常，但從 Omarchy 選單啟動卻發生程式或子行程無故被殺掉（`exit code 137`）」的詭異問題。受影響的是**所有透過 Omarchy 選單啟動的應用程式**，而不僅限於單一軟體：

- **PhpStorm** 啟用 Google Antigravity ACP 時發生 `AcpTransportClosedDuringInitializeException: ... exited with exit code 137`
- **Zed** 啟動 **Agy**（或其他 ACP / Agent 子行程）時無故中止或無法連線
- **Chromium / Electron 類應用**（如 Chrome、VS Code、Claude Desktop 等）在高負載或初始化時無聲閃退

### 完整因果鏈

1. **Portal 回報配額為 `0`**：Omarchy 的選單與狀態列（`quickshell`）載入 PipeWire 模組（`module-rt`）時，會透過 `xdg-desktop-portal` 詢問即時排程配額；在缺少 `rtkit` 的情況下，portal 會回報配額為 `0`。
2. **硬限制被設成 `0 / 0 us`**：`module-rt` 會將 `quickshell` 的 `Max realtime timeout`（`RLIMIT_RTTIME`）硬限制套用為 **`0 / 0 us`**。
3. **從選單啟動的程式與子行程全面繼承限制**：透過 `Super + Space`（Omarchy 選單）啟動的應用程式（如 PhpStorm、Zed）會繼承 `quickshell` 的行程限制（`RLIMIT_RTTIME = 0`），並再往下傳給它們啟動的子行程。
   - 相對地，從終端機（如 Ghostty）或 Hyprland 快捷鍵直接啟動的程式，`RLIMIT_RTTIME` 則是 `unlimited`，所以才會有「終端機開都正常、選單開就出事」的現象。
4. **觸發 Kernel Watchdog 無聲 `SIGKILL`**：當主程式或子行程（例如 `agy_acp_server.par`、Zed 的 Agy 等）初始化建立執行緒或觸及 CPU timer 路徑時，會直接踩到 Linux Kernel 的 realtime watchdog，立刻收到 `SIGKILL`（`128 + 9 = 137`）而中止。
   - 因為這是 `RLIMIT_RTTIME` 觸發的 `SIGKILL`，**不會出現在 OOM killer、`dmesg` 或 `coredumpctl` 紀錄中**，表面上只看得到 `exit code 137`。

---

## 安裝與啟用步驟

安裝 `rtkit` 後，重新啟動 `xdg-desktop-portal` 與 Omarchy shell，讓新的行程限制生效：

```bash
# 1. 安裝 rtkit
omarchy pkg add rtkit
# （或使用 sudo pacman -S rtkit）

# 2. 重啟 portal 與 Omarchy shell
systemctl --user restart xdg-desktop-portal.service
omarchy restart shell
```

> `rtkit-daemon.service` 會由 D-Bus 自動按需喚醒（也可視需要以 `sudo systemctl enable --now rtkit-daemon.service` 設為開機啟動）。套用完成後，請**完全關閉原本從選單開啟的應用程式（如 PhpStorm、Zed）再重新啟動**，才不會繼續沿用舊的 `0 / 0 us` 限制。

---

## 驗證與除錯指令

### 1. 檢查套件與服務狀態

```bash
# 確認已安裝 rtkit
pacman -Q rtkit

# 查看 rtkit-daemon 服務狀態
systemctl status rtkit-daemon.service --no-pager
```

### 2. 檢查行程的 `RLIMIT_RTTIME`

正常情況下，`quickshell` 與由它啟動的程式應顯示 `200000` us（或 `unlimited`），不應再是 `0`：

```bash
# 檢查 quickshell 目前的 Max realtime timeout
grep 'realtime timeout' /proc/$(pgrep -x quickshell | head -n1)/limits

# 檢查特定程式（以 PhpStorm、Zed 為例）
grep 'realtime timeout' /proc/$(pgrep -f '/opt/phpstorm/bin/phpstorm' | head -n1)/limits
grep 'realtime timeout' /proc/$(pgrep -x zed-editor | head -n1)/limits
```

### 3. 本機快速重現 / 臨時繞過

- **重現問題**：在終端機中以 `ulimit -R 0` 模擬 `RLIMIT_RTTIME = 0`，即可驗證程式是否因 realtime timeout 被 `SIGKILL`：
  ```bash
  (ulimit -R 0; ulimit -R; <your-command>)
  ```
- **臨時繞過**：若暫時無法重啟桌面 shell，可先完全關閉該應用程式，直接從終端機（`ulimit -R` 為 `unlimited`）啟動即可避開繼承 `0` 限制：
  ```bash
  nohup phpstorm >/dev/null 2>&1 & disown
  ```
