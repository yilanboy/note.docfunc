# 從 MySQL 搬家到 PostgreSQL

前陣子為了省錢，將自己的 Laravel 服務改為部署到 AWS Lambda 上，資料庫不使用 AWS 上貴鬆鬆的 RDS，而是使用有提供免費方案的 PlanetScale。這個架構相當不錯，除了網站響應速度相當不錯之外，每月成本還不到 2 美金。

結果好景不長，搬家後沒多久，[PlanetScale 發生裁員，並宣布四月停止提供免費方案](https://planetscale.com/blog/planetscale-forever)，讓我不得不去尋找其他可行的替代方案。上網查了些資料後，我發現提供 PostgreSQL 服務的 [Supabase](https://supabase.com/) 與 [Neon](https://neon.tech/) 似乎都是不錯的選擇，而且兩者都有提供免費方案。但 Neon 有一點非常特別，它提供的 PostgreSQL 服務是採用無伺服器 (Serverless) 的架構。

這好像跟 Lambda 很搭？於是我決定讓資料庫再搬一次家，從 PlanetScale 改為使用 Neon。以下就來簡單分享這次搬家的體驗。

## 簡單介紹 Neon

![Neon Website](https://blobs.docfunc.com/images/2024_07_12_18_06_52_e9dc9d319406.png)

Neon 是一個提供 PostgreSQL 服務的平台，其亮點之一是能夠像無伺服器架構一樣輕鬆進行水平擴展，以處理突如其來的大流量。眾所周知，資料庫由於儲存著資料，屬於有狀態 (Stateful) 服務，因此與無狀態服務相比，實現水平擴展難度更高。

![Neon Architecture](https://blobs.docfunc.com/images/2024_07_15_17_33_44_f74eafa49be5.jpg)

為了解決水平擴展不易這個問題，Neon 在架構上將運算層與儲存層分開來。

運算層由多個運行 Postgres 程序的節點組成。這些程序主要有 `Active` 與 `Idle` 兩種狀態，Neon 之所以可以輕鬆做到水平擴展，就在於運算層是無狀態的，可以藉由增加節點來提升請求的處理速度。而程序在 5 分鐘內未收到查詢請求時，也會自動切換至 `Idle` 狀態以節省資源。

儲存層由 Pageservers、Safekeeper 和雲端物件儲存空間 (例如 AWS S3) 三個部分組成。Postgres 程序會將紀錄資料更新操作的[預寫式日誌 (Write-Ahead Logging，縮寫為 WAL)](https://zh.wikipedia.org/zh-tw/%E9%A2%84%E5%86%99%E5%BC%8F%E6%97%A5%E5%BF%97)傳遞給 Safekeeper 進行儲存。之後，Pageservers 會處理這些預寫式日誌，並將其上傳至雲端物件儲存空間。此外，Pageservers 還負責處理所有資料的讀取請求，有需要時會從雲端物件儲存空間上下載資料。

可以看到 Neon 的架構相當有趣，概念與 Amazon Aurora Serverless 相同，都是將運算層與儲存層分開，並透過增加運算層節點數來提升效率。但 Neon 是一個[開源專案](https://github.com/neondatabase/neon)，如果你想節省更多成本的話，也可以自行架設 Neon 來運行 PostgreSQL。

## 在 Neon 上建立資料庫

在 Neon 上建立資料庫相當簡單。申請好帳號後就可以開始建立新的專案與資料庫 (免費方案下只能建立一個專案)。這裡注意地區請選擇與服務相近的地區以減少連線延遲。

![Create Project on Neon](https://blobs.docfunc.com/images/2024_07_15_14_50_53_50283ab5637f.png)

資料庫建立好之後就會拿到連線資訊，連線設定預設強制要求 SSL 加密。

![Connection Details](https://blobs.docfunc.com/images/2024_07_15_14_58_47_7c09819024b0.png)

## 使用 PGLoader 從 MySQL 轉移資料至 PostgreSQL

有了資料庫之後，再來就是將原本在 MySQL 中的資料轉移過去。這邊我使用 [PGLoader](https://github.com/dimitri/pgloader) 這個工具來轉移資料，首先使用 HomBrew 安裝 PGLoader。

```bash
brew install pgloader
```

建立一個 PGLoader 的設定檔案 config.load。

```text
load database
  from mysql://user:password@host/source_db?sslmode=require
  into postgres://alex:endpoint=ep-cool-darkness-123456;[email protected]/dbname?sslmode=require;
```

設定檔案寫好之後，就可以開始使用 PGLoader 進行資料轉移。

```bash
pgloader config.load
```

## 備份 PostgreSQL 上的資料

我們可以使用 `pg_dump` 來備份 PostgreSQL 上面的資料。

```bash
pg_dump --host='db-host.com' \
--dbname='db_name' --username='db_owner' \
--column-inserts --no-owner \
--file='db-backup.sql'
```

這裡 `--column-inserts` 代表輸出的 SQL 檔案會使用 INSERT 的方式來寫入資料。

`--no-owner` 則表明不需要包含所有者的資訊，避免匯入到新資料庫時，還需要先建立一個一模一樣的 Role。

如果只想備份資料結構，而不想包含資料，可以加上 `--schema-only` 參數。

```bash
pg_dump --host='db-host.com' \
--dbname='db_name' --username='db_owner' \
--no-owner --schema-only \
--file='db-schema-backup.sql'
```

相反的，如果只想備份資料，而不想包含資料結構，可以加上 `--data-only` 參數。

```bash
pg_dump --host='db-host.com' \
--dbname='db_name' --username='db_owner' \
--column-inserts --no-owner --data-only \
--file='db-data-backup.sql'
```

上面的連線資訊其實可以用一行 Connection String 來帶過。

```bash
pg_dump -d 'postgresql://db_owner:[email protected]/db_name?sslmode=require' \
--column-inserts --no-owner \
-f 'db.sql'
```

## 修改服務的資料庫連線資訊

資料庫準備好之後，接下來就是修改 Lambda 上 Laravel 服務的連線資訊。這個部分是透過 Lambda 的環境變數來設定，可以到 Lambda 函式底下的 Configuration → Environment variables 修改環境變數。

```ini
DB_CONNECTION=pgsql
DB_HOST=db-host.com
DB_PORT=5432
DB_DATABASE=db_name
DB_USERNAME=db_owner
DB_PASSWORD=db_password
```

## 啟用 PHP 外掛套件

連線資訊修改好了之後，你會發現還是無法連線到資料庫，並出現錯誤訊息顯示沒有連線 PostgreSQL 所必要的 `pdo_pgsql` 擴充套件。

**雖然 Bref 的 PHP Runtime 有安裝** `pdo_pgsql`**，但預設並沒有啟用**。我們可以新增一個 `pgsql.ini` 檔案來啟用它，檔案的內容如下。

```ini
; Enable pdo_pgsql extension module in bref
; See https://bref.sh/docs/environment/php
extension=pdo_pgsql
```

在 Laravel 專案目錄底下新增資料夾 `php/conf.d`，並將剛剛的 `pgsql.ini` 檔案放在底下。

```text
php
└── conf.d
    └── pgsql.ini
```

## 參考資料

- [Neon architecture](https://neon.tech/docs/introduction/architecture-overview)
- [How To Migrate a MySQL Database to PostgreSQL Using pgLoader](https://www.digitalocean.com/community/tutorials/how-to-migrate-mysql-database-to-postgres-using-pgloader)
- [Stack Overflow - COPY FROM STDIN does not work in liquibase](https://stackoverflow.com/questions/59214217/copy-from-stdin-does-not-work-in-liquibase)
- [Bref - Extensions installed but disabled by default](https://bref.sh/docs/environment/php#extensions-installed-but-disabled-by-default)
