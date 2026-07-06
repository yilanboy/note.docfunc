# Slotted Counter Pattern

某天在上班途中滑推特的時候，看到一個很有趣的資料庫計數器設計 Tips。常見的計數器 (counter) 更新操作，是當用戶觸發某些條件後，就將後端資料庫記錄的數量加 1。

以推文中的網頁瀏覽數當作例子，當用戶訪問頁面時，就將該頁面的瀏覽數量加 1。

```sql
UPDATE pageview_counts SET count = count + 1 WHERE url = '/home';
```

Laravel 中有提供 `increment` 方法，可以用來做到一模一樣的事情。

```php
$pageviewCount = PageviewCount::where('url', '/home')->first();

// 將該文章的留言數加 1
$pageviewCount->increment('count');
// update `pageview_counts` set `count` = `count` + 1, `pageview_counts`.`updated_at` = ? where `id` = ?
```

使用 `UPDATE` 語句來做計數更新是很常見的做法，但這麼做其實會有個問題。

**因為** `UPDATE` **語句會鎖定資料直到資料更新完畢**，當短時間內遇到大量的更新操作時，就有可能會遇到效能瓶頸。

於是推文中建議應該使用另外一種方式來更新計數。

```sql
-- 我有稍微修改推文中的更新語句，但效果不變
-- 欄位 fanout 改為 slot
-- count + VALUES(count) 改為 count + 1
INSERT INTO pageviews_counts
(url, slot, count)
VALUES ('/home', FLOOR(RAND() \* 100), 1)
ON DUPLICATE KEY UPDATE count = count + 1;
```

這是一種被稱為 Slotted Counter Pattern 的設計。雖然看似很複雜，但其實概念相當簡單，接下來就讓我我來簡單解釋一下。

## Slotted Counter Pattern 介紹

首先看一下推文中改善後的計數加 1 寫法。

```sql
INSERT INTO pageviews_counts
(url, slot, count)
VALUES ('/home', FLOOR(RAND() \* 100), 1)
ON DUPLICATE KEY UPDATE count = count + 1;
```

我們先拆成兩個部分來看，首先來看前半段。

```sql
INSERT INTO pageviews_counts
(url, slot, count)
VALUES ('/home', FLOOR(RAND() \* 100), 1);
```

是很常見的 `INSERT` 操作，在 `pageviews_counts` 中新增一筆資料。這筆資料的 `url`、`slot` 與 `count` 欄位，會分別寫入 `'home'`、`FLOOR(RAND() \* 100)` 與 `1`。

插入 `url` 與 `count` 欄位的資料內容都很好明白，是很單純的字串與數字。但插入 `slot` 欄位的函式 `FLOOR(RAND() \* 100)`，會產生什麼樣的資料呢？

`FLOOR()` 是 MySQL 的一個數學函式，它會無條件捨去參數的小數點並返回小於或等於參數的最大整數。

```sql
SELECT FLOOR(3.9); -- 結果為 3
SELECT FLOOR(7.2); -- 結果為 7
SELECT FLOOR(-5.5); -- 結果為 -6
```

`RAND()` 是 MySQL 的隨機數函數，用於生成介於 0 ~ 1（不包含 1）之間的隨機數。

```sql
SELECT RAND() -- 結果為 0.7795656209621258
```

所以 `FLOOR(RAND() \* 100)` 的功能，**就是隨機生成一個介於 0 ~ 99 的數字**。

因此剛剛 `INSERT` 操作會新增像這樣子的資料。

| id  | url   | slot   | count |
| --- | ----- | ------ | ----- |
| 1   | /home | 0 ~ 99 | 1     |

至於為什麼要特地在 slot 隨機生成一個數字呢？讓我們看完整的更新語句。

```sql
INSERT INTO pageviews_counts
(url, slot, count)
VALUES ('/home', FLOOR(RAND() \* 100), 1)
ON DUPLICATE KEY UPDATE count = count + 1;
```

後面加上的 `ON DUPLICATE KEY UPDATE`，是 MYSQL 提供的一種特殊語法，**在新增資料前會先判斷資料是否存在，如果資料存在，就不新增資料，而是改為更新現有資料**。

`ON DUPLICATE KEY UPDATE` 在使用上有個要求，**就是在要新增資料的欄位中，必須要有主鍵 (primary key) 或是唯一索引 (unique key)**。`ON DUPLICATE KEY UPDATE` **會根據這個 key 來判斷資料是否已經存在**。

根據 `ON DUPLICATE KEY UPDATE` 的使用要求，我們可以猜測 `pageview_counts` 這張資料表的設計如下。**資料表中應該有一個由** `url` **與** `slot` **組合成的唯一索引**。

```sql
CREATE TABLE `pageviews_counts` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`url` varchar(256) NOT NULL,
`slot` int(11) NOT NULL DEFAULT '0',
`count` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
-- 這個唯一組合索引 Hen 重要，可以讓 ON DUPLICATE KEY UPDATE 判斷要新增的資料是否已經存在
UNIQUE KEY `url_and_slot` ( `url`, `slot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
```

假設我們有一個沒有資料的 `pageviews_counts` 資料表。讓我們嘗試執行幾次幫 `/home` 瀏覽數 +1 的更新。

執行第一次更新，假設 `slot` 的隨機數為 0，因為資料表完全沒有資料，所以會新增一筆資料：

| id  | url   | slot | count |
| --- | ----- | ---- | ----- |
| 1   | /home | 0    | 1     |

執行第二次更新，假設 `slot` 的隨機數為 `36`，因為 `url` 為 `/home` 且 `slot` 為 `36` 的資料並不存在，所以還是會新增一筆資料，此時資料表會有兩筆資料：

| id  | url   | slot | count |
| --- | ----- | ---- | ----- |
| 1   | /home | 0    | 1     |
| 2   | /home | 36   | 1     |

執行第三次更新，假設 `slot` 的隨機數為 `0`，因為 `url` 為 `/home` 且 `slot` 為 `0` 的資料已經存在，所以並不會新增資料，而是更新現有資料，將其 `count` 的數量 + 1：

| id  | url   | slot | count |
| --- | ----- | ---- | ----- |
| 1   | /home | 0    | 2     |
| 2   | /home | 36   | 1     |

這樣大家是否明白這個計數 + 1 的更新語句如何運作了呢。

當 `url` 與 `slot` 組合的資料不存在時，會新增一筆資料，並將 `count` 設為 1。

反之當 `url` 與 `slot` 組合的資料已經存在時，會將該筆資料的 `count` 加 1。

最後我們資料表可能會像這樣：

| id  | url   | slot | count |
| --- | ----- | ---- | ----- |
| 1   | /home | 0    | 2     |
| 2   | /home | 36   | 1     |
| 3   | /home | 7    | 1     |
| 4   | /home | 81   | 2     |
| 5   | /home | 23   | 3     |
| ... | ...   | ...  | ...   |

當我們要總瀏覽數時，只要使用 `SUM` 函式取得 `count` 的加總即可。

```sql
SELECT SUM(count) as count
FROM pageview_counts
WHERE url = '/home';
```

**透過這樣的設計，資料庫同一時間可以處理最多 100 個更新計數操作**。

是不是很有趣也很厲害的設計呢？

## 參考資料

- [The Slotted Counter Pattern (planetscale.com)](https://planetscale.com/blog/the-slotted-counter-pattern)
- [Prevent Locking Issues For Updates On Counters - Database Tip (sqlfordevs.com)](https://sqlfordevs.com/concurrent-updates-locking)
- [Planet MySQL :: Planet MySQL - Archives - MySQL - The Slotted Counter Pattern](https://planet.mysql.com/entry/?id=5988579)
