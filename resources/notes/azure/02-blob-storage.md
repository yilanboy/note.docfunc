# Azure Blob Storage

在 Azure Storage Account 中，一個容器（Container）是一組相關的 Blob 對象的集合，類似於文件夾或目錄。而 Blob（Binary Large Object）則是存儲在 Azure 存儲服務中的二進制數據對象，可以是文本、圖像、音頻、視頻等任何類型的數據。

每個 Blob 對象必須位於一個容器中。在容器中，您可以創建、列出、刪除和列出 Blob 對象。您可以將容器與 Blob 對象一起使用，將其作為文件夾層次結構來組織和管理您的數據。

容器提供了一種方便的方式來組織和管理大量的 Blob 對象，並幫助簡化對數據的訪問和管理。您可以為容器設置公共訪問級別，以控制誰可以讀取容器和 Blob 對象。容器還可以通過共享訪問簽名（Shared Access Signatures）來授予其他用戶訪問權限。

Blob 對象存儲在容器中，並且可以通過唯一的 Blob URL 進行訪問。您可以在 Blob 上執行各種操作，例如上傳、下載、覆制、移動、重命名和刪除。Blob 對象還可以有元數據，這些元數據是與 Blob 相關聯的鍵值對，可以包含各種類型的信息，例如文件類型、大小、創建日期等。

總的來說，Azure Storage Account 中的容器和 Blob 對象提供了一種靈活的方式來存儲和管理數據，可以輕松地擴展和使用，適用於各種應用場景，如存儲媒體文件、備份和歸檔數據、存儲應用程序日志和數據等。

## Blobs 的種類

有三種 blobs，分別是 block blobs、append blobs 與 page blobs

一但 blob 被建立，就不能再更改其種類，並且它只能通過使用適合該 blob 類型的操作來更新

### 關於 Block Blobs

Block blobs 針對高效上傳大量數據進行了優化

Block blobs 是由多個 block 組成的，它們各自會有一個 block ID

一個 block blob 最多可以包含 **50,000** 個 block，每個 block 的大小可以不同，但最大能到 **4000 MB (根據服務版本的不同會有不一樣的大小)**

要建立或更新一個 block blob，可以透過 [put block](https://learn.microsoft.com/en-us/rest/api/storageservices/put-block) 操作

每個 storage client 預設能上傳單一最大 blob 的大小為 128MB (可以調整)，當檔案超過這個大小，就會被切成多個 blocks

當你上傳 block 到指定的 block blob，在還沒 commit 之前，這個 block 都不會整合進 blob 中，最多可以有 100,000 個未 commit 的 block

當你寫入一個 block 到 blob，**不會更新這個 blob 的最後修改時間**

#### 上傳 Block Blob

Block Blob 可以幫助你管理大型檔案的上傳，使用 Block Blob，你可以並行上傳多個 blocks 以減少上傳時間

每個 block 可以包含 MD5 雜湊值以驗證傳輸，所以你可以追蹤上傳進度並在需要時重新發送 blocks

你可以以任何順序上傳 blocks，然後在最終提交步驟中確定它們的順序

你可以上傳一個新 block 來替換相同 block ID 的現有未提交 block

你有一週的時間將 blocks 提交到 blob 中，否則它們會被丟棄

所有未提交的 blocks 在 block list 提交操作時也會被丟棄，如果它們未被包含在提交操作中

#### 編輯 Block Blob

你可以通過插入、替換或刪除現有的 blocks 來修改現有的 block blob

上傳已更改的 block 或 blocks 後，你可以使用一個提交操作將新的 blocks 與要保留的現有 blocks 一起提交，以提交新版本的 blob

為了在已提交的 blob 的兩個不同位置插入相同範圍的位元組，你可以在同一個提交操作中將相同的 block 提交到兩個位置

對於任何提交操作，如果找不到任何 block，整個提交操作將因錯誤而失敗，並且 blob 將不會被修改

任何 block 的提交都會覆蓋 blob 的現有屬性和元數據，並且丟棄所有未提交的 blocks

#### Block ID

Block ID 是 blob 中具有相等長度的字符串

Block 客戶端代碼通常使用 base-64 編碼將字符串標準化為相等的長度，使用 base-64 編碼時，預編碼字符串必須為 64 個字節或更少

不同的 blob 中可以**有相同的 block ID 值**

## 參考資料

- [Understanding block blobs, append blobs, and page blobs](https://learn.microsoft.com/en-us/rest/api/storageservices/Understanding-Block-Blobs--Append-Blobs--and-Page-Blobs?redirectedfrom=MSDN#about-block-blobs)
