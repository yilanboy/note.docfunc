# Azure Data Explorer

記錄一下使用 Azure Data Explorer (以下簡稱 ADX)的過程。

前陣子公司在研究如何將地端的 Log 系統上雲，做了一些研究之後，發現 ADX 或許是我們可以考慮的解決方案之一。理由如下：

- ADX 可接收的資料量很大。端看你要使用 streaming ingestion 或是 batch ingestion。
- 公司內部的認證與 Azure AD 深度整合，因此在訪問 ADX 的權限控管上會比較好設定。
- ADX 有提供 REST API 與 SDK，因此可以自己寫工具來存取資料。
- ADX 內部的資料可以設定 retention policy，時間一到可以自動刪除資料，節省成本。
- ADX 提供 Kusto Query Language (KQL) 讓用戶對資料進行操作，可以用來查詢資料，也可以結合 Power BI 來做資料視覺化。

雖然 ADX 看起來貌似不錯，但由於 ADX 會啟動一組 cluster 處理所有的 ingestion、data markup 與 user query。**所以即使你不使用，也需要負擔 cluster 運行的成本**。但另外一方面來說，即使 user query 的次數很頻繁，也不會因此增加費用。

總結來說，ADX 很適合用在存放大量的資料，且 query 次數相當頻繁的場景。

## Log 系統架構圖

![Log 系統架構圖](https://allen-files.s3.ap-northeast-1.amazonaws.com/images/azure/log-system-architecture.jpg)

簡單說明架構的設計：

- Log 的來源大多為地端的 server。並由 fluent bit 送往 Azure。
- 跟據合規性的要求，需要將 log 備份一段時間。因此 log 會先送至 blob storage 進行備份。
- Blob storage 可以設定資料物件的 policy。根據保存的時間自動轉 tier，例如從 cool 轉成 archive，也可以根據時間自動刪除資料，以節省成本。
- 當 log 被上傳至 blob storage，會觸發 event grid 通知 ADX 進行 ingestion。將資料倒入 ADX 中。
- ADX 上也會設定 retention policy，根據時間自動刪除資料。我這裡設計是保存一個月，超過一個月的資料會自動刪除，以節省成本。

> Fluent bit 本身支援將資料送往 blob storage 或是 ADX，我之前嘗試過在 fluent bit 將資料複製一份同時送往 blog storage 與 ADX，但非常不穩定，時常發生掉資料的情況。
