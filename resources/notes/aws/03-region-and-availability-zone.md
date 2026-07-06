# Region and Availability Zone

## Region

如何選擇 AWS Region？有幾個面向可以考慮：

- Compliance：政府資料規定不能夠離開該國家。
- Proximity：離使用者最近的地區，減少延遲 (Latency)。
- Available Service：某些服務只有在特定地區才有提供。
- Pricing：不同地區的價格不同。

## Availability Zone

每個 region 都有多個 availability zone (簡稱 AZ)，通常為 3 個，最少為 3 個，最多為 6 個。
例如 ap-southeast-2 (亞太區域 - 雪梨) 就有 3 個 AZ，分別為 ap-southeast-2a、ap-southeast-2b 與 ap-southeast-2c。

每個 AZ 都是獨立的，因此在做災害復原 (Disaster Recovery) 的時候，可以將資源部署在不同的 AZ，以達到高可用性 (High Availability) 的目的。

每個 AZ 間都使用高頻寬 (bandwidth) 且極低延遲 (ultra-low latency) 的網路互相連線。
