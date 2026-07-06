# API Gateway

- 可以將回傳結果做 cache，減少後面 server 或是 Lambda 的負擔
- API Gateway 可以很輕鬆的做到金絲雀部署 (canary deployment)，金絲雀部署是指先讓一小部分的用戶測試新功能，如果沒問題再逐步擴大至全部用戶
    - 藍綠部署是指同時有新版本與舊版本，並且可以很方面的快速切換，但同一時間只會有一個環境在線上
- API Gateway 可以調整流量限制 (throttling)
- API gateway 只收資料傳輸量的費用，即 pay as you go
- 可以根據 path 將請求送到不同的 backend
- 可以根據 query string 將請求送到不同的 backend

## Burst Limit VS Rate Limit

API Gate 可以調整流量限制 (Throttling)，可以在左側選單中選擇 Protect 底下的 Throttling，其中又分為 Burst Limit 與 Rate Limit。

- Burst Limit 代表你的 API 在同一時間最多可以處理幾個請求。
- Rate Limit 代表你的 API 在一秒鐘內最多可以處理幾個請求。

> 所謂的 API Throttling，其實是一種用來限制用戶 API 請求數量的方式，用以保護後端不被大量請求癱瘓
>
> API Throttling 有多種策略，最常見的策略是限制使用者在一定時間內可以發出的 API 請求數量。
> 除此之外還有令牌桶策略，設定一個有上限數量的令牌桶，令牌重新生成的速率固定，
> 用戶每發一次請求都會消耗一個令牌，令牌沒了就要等令牌重新生成才能發請求。
> 令牌桶策略可以允許偶發性的大量請求出現。
