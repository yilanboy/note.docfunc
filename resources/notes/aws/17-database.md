# Database Service

## Relational Database Service (RDS)

- 如果想更安全的讓 EC2 連線，可以使用 IAM DB Authentication。連線不透過密碼，而是在程式碼中先透過 IAM 取得連線 DB 的暫時性 Token (15 分鐘)，這種做法有個缺點是有延遲。
- Aurora 通常是開一個叢集。
- Aurora 可以設定 custom endpoint，將 endpoint 分流到不同能力的 DB instance 上。
- Aurora Global Database 是為了給部署全球的應用程式存取資料用。
- Aurora built-in reader endpoint 可以讓你連線到只能讀取的資料庫。
- Aurora 支援最高 64TB 的資料庫。
- Single Aurora，沒有 Replica，也不是 serverless，在 DB 掛掉後會重新建立。
- 想提高資料庫的負載，應該使用 Aurora Auto Scaling。
- RDS Storage Auto Scaling 可以自動擴展 rds 的硬碟，並且做到零停機部署。
- RDS Multi-AZ 目的是提高 HA，並不是提高資料庫負載能力，**資料複製是同步的 (synchronous)**。
- RDS Multi-AZ 會在不同的 AZ 開一個 standby，並且會自動做 failover。
- RDS Read Replica 目的是提高資料庫的負載能力，**資料複製是異步的 (asynchronous)**。
    - Aurora Read Replica 雖然是異步的，但 1 秒內就可以同步。
    - MySQL Read Replica 需要幾秒鐘才能同步。
- RDS MySQL standby replica 不開放任何讀寫。
- 需要讀的功能請使用 read replica。
- RDS 本身提供 monitoring 功能，可以開啟 Enhanced Monitoring，數據會透過 RDS 上面的 agent 送到 cloudwatch。
- RDS 提供操作類型的 event，例如：
    - instance events
    - parameter group event (parameter group 用來設定資料庫初始參數)
    - security group event
    - snapshot event
    - data-modifying events
- RDS 可以透過上述這些 event 調用 Lambda，或是使用 store procedure。
- RDS Proxy 通過建立一個資料庫的熱連接池，適用於 Lambda 會對 RDS 發出大量連接的場景。
- 當 failing over 發生時，RDS 會簡單的修改 CNAME，使連線指向 standby，並將 standby 主機升級為 primary。
- 注意遇到 failuer event，RDS 不會嘗試重啟。
- AWS Database Migration Service (AWS DMS) 可以用來一次性轉移 RMDBS、資料倉儲、NoSQL 與其他類型的資料存儲工具至雲端。
    - full load 代表全部轉移，但不包含新加入的資料 (on-going)。
    - change data capture (CDC) task，代表如果資料來源有更新也會一併納入轉移。
- DMS 也可以將資料轉往 S3，並資料以 csv 的格式儲存。
- AWS Schema Conversion Tool (SCT) 提供以專案為基礎的使用者介面，可自動將來源資料庫的資料庫結構描述轉換為與目標 Amazon RDS 執行個體相容的格式。
- 觸發 RDS failover 的機制如下：
    - Loss of availability in primary AZ
    - Loss of network connectivity to primary
    - Compute unit failure primary
    - Storage failure on primary
- 資料庫帳號密碼等機密資料，應該選用 Secret Manager 來儲存。
- CloudWatch for RDS 預設可以看以下這些資料。
    - CPU Utilization
    - Database Connections
    - Freeable Memory
- CloudWatch Enhanced Monitoring 可以看 RDS 以下這些資料。
    - RDS process
    - RDS child process
    - OS process

![image](https://hackmd.io/_uploads/r1ALR9_ET.png)

![image](https://hackmd.io/_uploads/rk7vA5_Ep.png)

## ElastiCache

ElastiCache 支援 Auto Discovery，客戶端程式能夠自動識別快取叢集中的所有節點，並啟動和維護與所有這些節點的連線。

## DynamoDB

- DynamoDB stream 可以將 CRUD 操作資訊以串流的方式傳給 Lambda。
- DynamoDB 的 Partition Key 設計，應該盡可能地使用高基數 (high-cardinality) 的 partition key，例如 unique id。
- DynamoDB 並沒有 read replica 功能。
- DynamoDB 是公共服務，如果想走私有網路，可以使用 VPC Gateway endpoint，並在 VPC 內部設定 route table。
- DynamoDB on-demand backup 無法跨地區或是跨帳戶備份，**AWS Backup 可以做到跨地區或是跨帳戶備份**。
- Point-in-time (PITR) 可持續備份 DynamoDB 表資料。啟用之後，DynamoDB 可保留最後 35 天的表格增量備份。
- Amazon DynamoDB Accelerator (DAX) 是 Amazon DynamoDB 的完全託管、高度可用的記憶體緩存，可提供微秒級的響應時間。
