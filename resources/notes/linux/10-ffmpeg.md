---
date: '2026-11-09'
tags: [linux, ffmpeg, audio, web-audio]
---

# FFmpeg 音訊響度標準化與 Web Audio 播放器音量控制

在開發多媒體或語音學習應用（例如單字學習、測驗闖關平台）時，經常會遇到「**答對/答錯音效震耳欲聾，但單字例句的發音讀音卻聽不清楚**」的音量失衡問題。

這類問題通常是由兩個因素疊加而成：
1. **音訊來源的響度標準不一**：音效檔與語音生成檔（TTS）在製作時未經過統一的響度標準化（Loudness Normalization）。
2. **前端播放器的增益衰減設計偏差**：前端播放器為了遷就過大聲的音效，全域調降了增益係數（例如 `gain = 0.5`），導致原本音量適中的語音被連帶衰減至難以聽辨。

本文紀錄如何使用 Linux 下的 `ffmpeg` 工具進行科學化的音訊響度檢測與標準化，並深入解析前端 Web Audio API 調控音量的底層物理與數學原理。

---

## 核心概念：峰值（Peak dBFS）與感知響度（LUFS）

在調整音量前，必須先釐清「最大峰值」與「感知響度」的根本差異：

| 指標 | 單位 | 說明 | 限制與特性 |
| :--- | :--- | :--- | :--- |
| **最大峰值 (Peak Volume)** | **dBFS** (Decibels relative to Full Scale) | 數位音訊波形瞬間振幅的最大值。`0 dBFS` 為數位訊號的上限。 | **無法反映人耳真實聽感**。短暫的高頻敲擊聲峰值可能極高（如 `-0.4 dB`），但整體能量不高；反之，平穩飽滿的講話聲峰值不高，聽起來卻可能更大聲。超過 `0 dBFS` 會發生削波失真（Clipping 破音）。 |
| **感知響度 (Perceived Loudness)** | **LUFS** (Loudness Units relative to Full Scale) 或 **LKFS** | 國際電信聯盟（ITU-R BS.1770）與歐洲廣播聯盟（EBU R128）制訂的標準。加入 **K-weighting** 人耳聽覺濾波，並對能量隨時間做積分計算。 | **最貼近人類實際聽覺大小**。數值越接近 0 越大聲。**每相差約 6 ~ 10 LUFS，聽感音量相當於相差一倍**。目前串流平台（Spotify: `-14 LUFS`、YouTube: `-14 LUFS`、Podcast: `-16 ~ -19 LUFS`）皆以此作為標準化基準。 |

### 實測案例：落差 7 ~ 10 LUFS 是什麼概念？
在本次問題排查中，我們實測了線上音檔：
* **答對音效 (`quiz_correct.mp3`)**：`-12.6 LUFS`（Peak: `-0.4 dB`，接近爆音臨界值）
* **答錯音效 (`quiz_wrong.mp3`)**：`-15.8 LUFS`（Peak: `-5.1 dB`）
* **單字發音 (`word.mp3`)**：`-19.3 LUFS`（Peak: `-4.9 dB`）
* **例句發音 (`sentence.mp3`)**：`-22.7 LUFS`（Peak: `-7.8 dB`）

> 答對音效比單字發音大聲近 **`7 LUFS`**，比例句大聲整整 **`10 LUFS`**。在人類聽覺中，這代表使用者做答正確時，聽到的音效音量是單字讀音的 **2 倍以上**。

---

## 使用 FFmpeg 進行音訊檢測

Linux 上的 `ffmpeg` 是音訊處理最強大的瑞士刀。我們可以透過以下幾個濾鏡進行精準診斷：

### 1. 測量感知響度（EBU R128）

使用 `ebur128` 濾鏡輸出 EBU R128 標準報告：

```bash
ffmpeg -i input.mp3 -af "ebur128" -f null -
```

在輸出結尾的 `Summary` 區塊中，重點觀察 **`Integrated loudness: I:`**：
```text
  Integrated loudness:
    I:         -12.6 LUFS
    Threshold: -29.9 LUFS
```
這代表該檔案整體的整合感知響度為 `-12.6 LUFS`。

### 2. 測量峰值與平均音量（volumedetect）

使用 `volumedetect` 濾鏡檢測最大峰值與平均 RMS 分貝：

```bash
ffmpeg -i input.mp3 -af "volumedetect" -f null - 2>&1 | grep -E "(mean_volume|max_volume)"
```

輸出範例：
```text
[Parsed_volumedetect_0] mean_volume: -22.0 dB
[Parsed_volumedetect_0] max_volume: -0.4 dB
```
若 `max_volume` 接近 `0.0 dB`，代表波形振幅已經快要頂到天花板，極易引起聽覺疲勞或在某些揚聲器上產生破音。

### 3. 偵測音檔尾部靜音（silencedetect）

許多下載或生成的音效檔，實際發聲可能只有 0.5 秒，但尾端卻拖了 2 秒以上的無效空白：

```bash
# 偵測低於 -50dB 且長度超過 0.1 秒的靜音區段
ffmpeg -i input.mp3 -af "silencedetect=noise=-50dB:d=0.1" -f null - 2>&1 | grep -i silence
```

輸出範例：
```text
[Parsed_silencedetect_0] silence_start: 1.501995
[Parsed_silencedetect_0] silence_end: 3.056327 | silence_duration: 1.554331
```
這代表檔案在 1.5 秒之後完全無聲，可以直接截掉以節省頻寬並提升播放器響應靈敏度。

---

## 使用 FFmpeg 進行音量標準化

針對語言學習類型的產品，建議將所有音效與語音標準化在 **`-19.0 ~ -20.0 LUFS`**，並將 True Peak 限制在 **`-1.5 dBFS`** 以下，確保留有足夠的動態餘裕（Headroom）。

### 做法 A：精確調整增益並裁切靜音（推薦用於短音效）

當已知音檔的現有響度時，可以直接套用分貝差額進行調整，並加上平滑淡出（`afade`）避免截斷音訊產生爆音點擊聲（Click）：

```bash
# 1. 答對音效：原為 -12.6 LUFS，目標 -19.4 LUFS（需調降 -6.4 dB）
# 保留自然殘響至 1.8 秒，最後 0.2 秒平滑淡出
ffmpeg -y -i quiz_correct.mp3 \
  -af "volume=-6.4dB,afade=t=out:st=1.6:d=0.2" \
  -t 1.8 \
  -b:a 128k \
  correct.mp3

# 2. 答錯音效：原為 -15.8 LUFS，目標 -19.5 LUFS（需調降 -4.1 dB）
# 發聲俐落，截取前 1.0 秒並於最後 0.2 秒淡出
ffmpeg -y -i quiz_wrong.mp3 \
  -af "volume=-4.1dB,afade=t=out:st=0.8:d=0.2" \
  -t 1.0 \
  -b:a 128k \
  wrong.mp3
```

### 做法 B：使用 `loudnorm` 自動雙程/單程標準化（推薦用於長語音批次處理）

若是 AI 生成的例句錄音或長篇語音，可以使用 FFmpeg 內建的 `loudnorm` 濾鏡進行 ITU-R BS.1770-4 自動標準化：

```bash
# 目標整合響度 -19 LUFS、最大真峰值 -1.5 dBTP、響度範圍 LRA 11
ffmpeg -i input.mp3 \
  -af "loudnorm=I=-19:TP=-1.5:LRA=11" \
  -b:a 128k \
  output.mp3
```

---

## Web Audio API 音量調整原理

在前端音訊架構中（如 Svelte / Vue / React 搭配 Web Audio API），音量通常是透過 `GainNode` 節點進行串接控制：

```typescript
// 建立音訊上下文與全域增益節點
const audioContext = new AudioContext();
const masterGain = audioContext.createGain();

masterGain.gain.value = volume; // volume 數值設定
masterGain.connect(audioContext.destination);
```

### 1. 線性振幅乘數 vs. 人耳對數聽覺

`GainNode.gain.value` 接收的是一個**線性浮點數倍率**（Linear Amplitude Multiplier，`0.0 ~ 1.0`），而非分貝（dB）：
* `gain = 1.0`：原音振幅輸出（`0 dB` 增益）。
* `gain = 0.5`：振幅減半。

振幅轉換為分貝的物理公式為：
$$\Delta \text{dB} = 20 \times \log_{10}(\text{gain})$$

當代入 `gain = 0.5` 時：
$$20 \times \log_{10}(0.5) \approx -6.02 \text{ dB}$$

這意味著：**只要將全域音量設為 `0.5`，所有音訊的輸出響度就會直接暴跌 `-6 dB`！**

### 2. 為什麼將 `DEFAULT_VOLUME` 設為 `0.5` 會造成發音過小？

* 單字發音原本為 `-19.3 LUFS`，經過 `0.5` 衰減後，實際從使用者喇叭輸出的響度只有 **`-25.3 LUFS`**。
* 例句發音原本為 `-22.7 LUFS`，衰減後甚至跌至 **`-28.7 LUFS`**。

在 `-25 ~ -29 LUFS` 的環境下：
1. **手機與筆電揚聲器功率不足**：小型設備的發聲單體本就較弱，此時使用者就算將手機硬體音量開到 80%~90%，仍然會覺得英文的清輔音（如 `/s/`, `/f/`, `/θ/`）或字尾咬字模糊不清。
2. **本末倒置的解決方式**：當初設定 `0.5` 主要是為了壓抑高達 `-12.6 LUFS` 的刺耳音效；一旦音效檔在源頭已經標準化降至 `-19.4 LUFS`，播放器就應該回到原音輸出標準。

### 3. 為什麼改設為 `0.8 ~ 1.0` 是合理設定？

* **Unity Gain（單元增益原則）**：在現代 Web 影音規範中，播放器的預設增益應維持在 `1.0`（或保留微量裕量的 `0.8`，衰減約 `-1.9 dB`），將最終響度交由**使用者的實體音量鍵**掌控。
* **充足的防破音餘裕（Headroom）**：經過標準化後的音檔，最大峰值皆落在 `-4.9 dB` 至 `-9.6 dB`，即使以 `1.0` 全功率播放，距離 `0 dBFS` 的失真紅線依然保有將近 5 dB 以上的安全空間，完全不會產生破音。

### 4. 消除調音時的爆音雜音（Click / Pop）

在動態改變音量時，若直接以 JavaScript 賦值：
```typescript
// ❌ 不良做法：直接瞬間賦值
masterGain.gain.value = newVolume;
```
如果剛好在波形的非零點瞬間切換振幅，音訊波形會出現垂直階躍斷層（Discontinuity），在耳機中會聽到一聲清脆的點擊雜音（Click/Pop）。

正確做法是使用 Web Audio API 提供的音訊參數時間曲線，進行指數平滑逼近：
```typescript
// ✅ 最佳做法：使用 setTargetAtTime 在 10ms 內平滑漸變
masterGain.gain.setTargetAtTime(
    newVolume,
    audioContext.currentTime,
    0.01 // 時間常數（秒）
);
```

---

## 雲端 CDN 部署與快取失效注意事項

當在伺服器端或本機重新產生標準化音檔並上傳至物件存儲（如 Cloudflare R2 / AWS S3）後，最常見的盲點是**瀏覽器依然播放舊版超大聲音效**。

原因在於 CDN 的 Edge Cache 與瀏覽器的 `ETag` 快取尚未更新。以 Cloudflare 為例：
1. 上傳檔案至 R2 存儲桶覆蓋原檔案（例如 `quiz/correct.mp3`）。
2. 至 Cloudflare 控制台或透過 API 執行 **Purge Cache（自訂清除）**，輸入受影響的完整 URL：
   * `https://assets.yourdomain.com/quiz/correct.mp3`
   * `https://assets.yourdomain.com/quiz/wrong.mp3`
3. 可以在終端機透過 `curl` 檢驗回傳的 `content-length` 或 `etag` 是否已更新：
   ```bash
   curl -I https://assets.yourdomain.com/quiz/correct.mp3
   ```
4. 或直接在終端機讓 FFmpeg 讀取線上網址驗證響度：
   ```bash
   ffmpeg -i "https://assets.yourdomain.com/quiz/correct.mp3" -af "ebur128" -f null -
   ```
   確認線上即時量測值確實落在目標的 `-19.4 LUFS`，即代表全鏈路更新完成。

### 想清除快取可以使用

首先，確保已設定 `ZONE_ID` 和 `CLOUDFLARE_API_TOKEN` 環境變數：

`CLOUDFLARE_API_TOKEN` 可以在 Account API Tokens 頁面設定，記得要在 All Zones 範圍內選取 **Cache Purge** 權限。

```bash
export ZONE_ID=""
export CLOUDFLARE_API_TOKEN=""

curl "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/purge_cache" \
    -H 'Content-Type: application/json' \
    -H "Authorization: Bearer $CLOUDFLARE_API_TOKEN" \
    -d '{
          "prefixes": [
            "assets.vomorize.com/quiz"
          ]
        }'
```
