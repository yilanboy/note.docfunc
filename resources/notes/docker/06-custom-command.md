# 使用 Docker Container 來製作自己的指令

我不太想為了 `pg_dump` 安裝 `PostgreSQL` 資料庫 ，所以就打算使用 Docker Container 來製作自己的 `pg_dump` 指令。首先新增一個檔案 `pg_dump.sh`

```bash
#!/bin/bash

# -it：指示 Docker 容器在互動模式下執行並分配一個偽終端
# --rm：指示 Docker 容器在退出時自動刪除
# "$@"：表示命令列參數。"$@" 會展開傳遞給 shell script 檔案的所有參數
docker run -it --rm postgres:16.3 pg_dump "$@"
```

幫 `pg_dump.sh` 加上執行權限。

```bash
chmod a+x pg_dump.sh
```

試著執行 `pg_dump.sh`。

```bash
./pg_dump.sh --version

# 指令輸出結果如下
# pg_dump (PostgreSQL) 16.3 (Debian 16.3-1.pgdg120+1)
```

看能不能用 `pg_dump.sh` 來備份資料庫中的資料，找一個 PostgreSQL 資料庫試試看。

```bash
./pg_dump.sh --host='my-postgres-database.net' -d my_db --username='db_owner' -f db.sql
```

輸入密碼之後，會發現 `db.sql` 並沒有出現，這是因為檔案是下載到容器中，而不是本地環境上。可以透過 Docker Volume 與 Work Dir 來解決這個問題。

修改一下 `pg_dump.sh` 的內容。

```bash
#!/bin/bash

# -v ${PWD}:/app：此參數將當前工作目錄掛載到容器內部的 /app 目錄。這意味著容器內部的 pg_dump 命令將能夠訪問你的當前工作目錄中的檔案。
# -w /app：此參數將容器內部的工作目錄設定為 /app。這意味著 pg_dump 命令將在 /app 目錄中執行。
docker run -it --rm -v ${PWD}:/app -w /app postgres:16.3 pg_dump "$@"
```

再次使用 `pg_dump.sh` 來備份資料庫中的資料，就可以看到 `db.sql` 出現囉。
