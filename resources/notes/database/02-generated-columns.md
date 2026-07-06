# Generated Columns

某天看到一個影片，是 PlanetScale 介紹如何在 MySQL 中使用 Generated Columns 來阻擋分身信箱的註冊。

分身信箱是指用戶可以使用 `+` 符號來讓信箱看起來不同，但實際上是同一個信箱。
假設我的信箱是 `allen@gmail.com`，我可以使用 `allen+video@gmail.com` 來註冊網站上的帳號。
網站如果有事情要通知我，寄信給 `allen+video@gmail.com` 時，我依然可以在 `allen@gmail.com` 上收到信件。

雖然分身信箱對於使用者來說很方便，但對於網站來說，可能會有點麻煩。
因為這代表同一個信箱可以註冊多個帳戶，這樣的話，網站就無法透過信箱來判斷使用者是否已經註冊過了。

而 PlantScale 就上傳了一個影片說明透過 Generated Columns 來解決這個問題。

## Generated Columns

Generated Columns 是 MySQL 的功能，可以讓我們在資料表中新增一個欄位，這個欄位的值是透過其他欄位計算出來的。

Generated Columns 有兩種類型，一種是 Virtual，另一種是 Stored。
前者是在查詢時才計算，而後者是真的會把計算結果存到硬碟中。

假設我們有兩個 column 是 `first_name` 與 `last_name`，我們可以透過 Generated Columns 來新增一個 `full_name` 欄位，這個欄位的值就是 `first_name` 與 `last_name` 的結合。

```sql
ALTER TABLE users
-- generated columns 預設都是 virtual，如果想使用 stored，可以在後面加上 STORED 關鍵字
ADD COLUMN full_name VARCHAR(255) GENERATED ALWAYS AS (CONCAT(first_name, ' ', last_name));
```

## 使用 Generated Columns 阻擋分身信箱

概念上很簡單，新增一個 `true_email` 欄位，這個欄位的值就是 `email` 去掉 `+` 後面的部分。

```sql
ALTER TABLE users
ADD COLUMN true_email VARCHAR(255) GENERATED ALWAYS AS (
  CONCAT_WS(
    '@',
    -- 取得 + 前面的部分，也就是真實的信箱
    SUBSTRING_INDEX(
      SUBSTRING_INDEX(email, '@', 1),
      '+',
      1
    ),
    -- 取得 @ 後面的部分
    SUBSTRING_INDEX(email, '@', -1)
  )
);
```

當我在 `email` 寫入 `allen+video@gmail.com` 時，`true_email` 的值就會是 `allen@gmail.com`。

| id  | email                   | true_email        |
| --- | ----------------------- | ----------------- |
| 1   | `allen+video@gmail.com` | `allen@gmail.com` |

我們將這個 generated column 設定為 unique。

```sql
ALTER TABLE users
ADD UNIQUE INDEX true_email (true_email);
```

假設我在寫入一個 `allen+social@gmail.com`，就會因為 generated column 已經存在 `allen@gmail.com` 的值，導致資料寫入失敗。

| id  | email                    | true_email        |
| --- | ------------------------ | ----------------- |
| 1   | `allen+video@gmail.com`  | `allen@gmail.com` |
| 2   | `allen+social@gmail.com` | `allen@gmail.com` |

```text
Query Error: Error: ER_DUP_ENTRY: Duplicate entry 'allen@gmail.com' for key 'true_email'
```

利用這種方式，就可以阻擋分身信箱的註冊。

## 參考資料

[![Using MySQL to stop freeloaders](https://img.youtube.com/vi/goC5BdyCvms/maxresdefault.jpg)](https://www.youtube.com/watch?v=goC5BdyCvms)
