# CloudFront (AWS CDN Service)

CloudFront 是 AWS 的 CDN (Content Delivery Network) 服務，可以用來加速靜態和動態的 Web 內容分發。CloudFront 會將你的內容複製到多個位於全球各地的 Edge Location，當用戶訪問你的網站時，會自動將用戶導向到離用戶最近的 Edge Location，這樣可以減少網頁載入的時間。

## SAA 筆記

- Amazon CloudFront 是一項可加快向使用者分發靜態和動態 Web 內容（例如 .html、.css、.js 和映像檔）的服務
- 可以設定多個 origin group，並且設定 failover，當 origin group 1 掛掉時，會自動切換到 origin group 2
- 如果想在 CloudFront 限制用戶訪問你的內容，可以使用
    - Signed URLs：支援 Adobe's Real-Time Message Protocol (RTMP)
    - Signed cookies：不想更改 URL 可以用 cookie
    - Origin Access Identity (OAI)
    - Origin Access Control (OAC)，官方建議使用 OAC，其支援所有地區
- CloudFront geo-restriction 是用來避免部分地區的用戶訪問你的 CDN 內容
- Lambda@Edge 可以讓你在各個 CDN 端點使用程式做一些小任務，例如登入
- Lambda@Edge 後面可接 Kinesis 來即時處理資料
- 可以透過設置 `Cache-Control max-age` 的值來設定 CloudFront 的 cache 時間
