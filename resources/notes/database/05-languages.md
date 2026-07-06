# 資料庫中的各種語句

簡單記錄一下資料庫中常看到的 DDL、DML、DQL、DCL 與 TCL，這些簡寫是什麼意思？

## DDL (Data Definition Language)

資料定義語言，用於定義和管理 SQL 資料庫中的所有物件的語句。

- `CREATE`，建立一個物件，如資料庫與資料表。
- `ALTER`，修改資料表結構。
- `DROP`，刪除資料表。
- `TRUNCATE`，清除資料表所有資料。

## DML (Data Manipulation Language)

資料操作語言，分別是 `UPDATE`、`INSERT` 與 `DELETE` 這些用來修改資料的語句。

## DQL (Data Query Language)

資料查詢語言，指的是 `SELECT` 這個用來查詢資料的語句，不會對資料進行修改。

## DCL (Data Control Language)

資料控制語言，是用來設置或更改資料庫使用者或角色許可權的語句。
包括 `GRANT`、`DENY` 與 `REVOKE`。
在預設狀態下，只有 sysadmin、dbcreator、db_owner 或 db_securityadmin 等人員才有權力執行。

## TCL (Transaction Control Language)

事務控制語言，有以下幾種語句。

- `SAVEPOINT`，設置保存點。
- `ROLLBACK`，回滾。
- `SET TRANSACTION`，開始事務。
- `COMMIT`，提交事務。
