# Storage Service

## Elastic Block Storage (EBS)

- EBS 預設加密。這個是地區 (regional) 級的功能。
- Amazon EBS 的類型有三種：
    - General Purpose (SSD)。
    - Provisioned IOPS (SSD)。
    - Magnetic (現在稱 Cold HDD)。
- Elastic Block Storage (EBS) io1/io2 支援同時被多個 EC2 掛載，最多 16 台。
- EBS 沒有 lifecycle policy。
- 使用 Amazon Data Lifecycle Manager (DLM) 可以幫 EBS 做到以下功能的自動化：
    - 建立 snapshot。
    - 設定 snapshot 保留時間。
    - 刪除 snapshot。
- EBS snapshot 會送至 S3 上保存。
- Data Lifecycle Manager (DLM) 可以與 CloudWatch 事件進行整合，例如 DLM 的備份數量達到上限，就會觸發 CloudWatch 事件。
- 用 DLM 來備份 EBS 是最快速也最經濟實惠的選擇，透過 AWS CLI 定期執行 `craete-snapshot` 也可以，但需要時間。
- EBS 支援 file lock，但不支援 object lock。object lock 是 S3 的功能。
- EBS 建立後，會自動在**同區域**建立 replica。
- EBS 與 EC2 必須要在同一個區域才能連接。
- 如果想換區域，可以使用 snapshot 來做到。
- EBS 使用 KMS 來加密，無論是 AWS Managed Key 或是匯入的 Custom Key 都可以。
- 未加密的 EBS，其 snapshot 也是未加密的，反之亦然。因此想加密 EBS，可以先建立一個加密的 snapshot，然後再建立 EBS。
- IOPS SSD，在 io1 可以提供 4GB 到 16TB 的存儲空間，根據你的硬碟大小，可以提供的 IOPS 也會提高。
    - 1GB 的硬碟大小可以提供 50 IOPS。

## Elastic File System (EFS)

- 支援 POSIX (Portable Operating System Interface) 標準。
- EFS 可以同時被**上千台 EC2** 掛載。
- EFS 的生命週期管理無法刪除檔案，根據時間可以將資料移往。
    - 不常存取 (Infrequent Access, Standard-IA)。
    - 單區域不常存取 (One-Zone IA)。

## Amazon FSx 檔案系統

- Amazon FSx For Lustre 是熱門開源的 parallel file system，效能非常好。
