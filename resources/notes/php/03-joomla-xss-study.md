---
date: '2024-03-25'
updated: '2026-07-06'
tags: [php]
---

# Joomla! XSS 漏洞研究

前陣子，在全世界 CMS (Content Management System) 市場擁有 2% 市占率的 Joomla 被發現含有 XSS (Cross-Site Scripting) 漏洞。 造成這個漏洞的原因並非是 Joomla 的程式邏輯有什麼問題，而是 PHP 本身提供的函式有一個奇怪的 Bug。

Joomla 中有一個 `Joomla\Filter\InputFilter` 類別，用來對使用者的輸入進行 XSS 過濾，例如當用戶輸入：

```text
<script>alert('xss attack')</script>
```

程式會將 HTML 標籤從輸入中移除。因此輸出會是：

```text
alert('xss attack')
```

XSS 過濾的部分程式碼如下所示：

```php
// Is there a tag? If so it will certainly start with a '<'.
$tagOpenStart = StringHelper::strpos($source, '<');

while ($tagOpenStart !== false) {
    // Get some information about the tag we are processing
    $preTag .= StringHelper::substr($postTag, 0, $tagOpenStart);

    // ...
}
```

在這段過濾程式碼中，程式會先找到 `<` 符號在字串中的索引位置，並利用該索引位置將 `<` 符號前的字串保留起來。

程式碼中的 `StringHelper::strpos()` 與 `StringHelper::substr()`， 其實是 PHP 函式 `mb_strpos()` 與 `mb_substr()` 的包裝。 簡單說明一下 `mb_strpos()` 與 `mb_substr()` 的功能。

`mb_strpos()` 可以在一串字串中，找出指定字元的索引位置。

```php
mb_strpos("abcdefg", "a"); // 0
mb_strpos("abcdefg", "b"); // 1
mb_strpos("abcdefg", "g"); // 6
```

`mb_substr` 可以用來取得字串的一部分。

```php
mb_substr("abcdefg", 0, 3); // "abc"
mb_substr("abcdefg", 0, 5); // "abcde"
```

Joomla 會出現 XSS 漏洞，主要原因在於這兩個函式遇到 UTF-8 前導位元組 (Leading Byte) 時，會有不一樣的處理方式。

## 什麼是前導位元組 (Leading Byte)？

前導位元組指的是**多位元組字元編碼中的第 1 個位元組**。在這些編碼中，每 1 個字元由多個位元組組成，其中第 1 個位元組被稱為前導位元組，後面的位元組則稱為連續位元組。

常見的多位元組字元編碼有 UTF-8，UTF-8 可以使用 1 至 4 個位元組來顯示一個完整的字元。例如微笑的 emoji，用十六進位的位元組表示就是 `f0 9f 98 8a`。

```php
// 在 UTF-8 中，微笑 emoji 這個字元由四個位元組組成
// 如果每個位元以 16 進位表示
// 下面的 f0 就是前導位元組，後面的 9f、98 與 8a 為連續位元組
var_dump("\xf0\x9f\x98\x8a" === '😊'); // bool (true)

// 可以使用 bin2hex 將字元轉換成 16 進位

echo bin2hex('😊'); // f09f988a

// 我們人眼看到的微笑 emoji：😊
// 用 UTF-8 表示為：U+1F60A
// 用二進位表示為：11110000 10011111 10011000 10001010
// 用十六進位表示為：0xf0 0x9f 0x98 0x8a
```

## Joomla XSS 漏洞說明

說明完前導位元組，我們來看看 Joomla 的 XSS 漏洞是如何發生的。當 `mb_strpos()` 在字串中讀到前導位元組時，它會嘗試解析後面的連續位元組，直到讀取完整的位元組序列後才會認爲這是一個完整的字元。

```php
mb_strpos("Hello! \xf0\x9f\x98\x8a How are you?", "😊"); // 7
```

說到這裡，聰明的你可能有一個疑問。

如果位元組序列故意不給完整的呢？🤔

假設我們有一個包含不完整位元組序列的字串 `\xf0\x9fAAA<BB`，來看看 `strpos()` 會怎麼處理。

```text
\xf0 <- 發現前導位元組，mb_strpos() 預期後面會有 3 個連續位元組來顯示一個完整的字元，該位元組的索引為 0
\x9f <- 發現連續位元組，此時該位元組的索引還是為 0，因為完整的字元還沒有出來
A    <- 不是連續位元組，錯誤！停止解析連續位元組，並將該字元的索引設為 1
A    <- 該字元的索引為 2
A    <- 該字元的索引為 3
<    <- 該字元的索引為 4
B    <- 該字元的索引為 5
B    <- 該字元的索引為 6
```

因此當我們想使用 `mb_strpos` 在字串 `\xf0\x9fAAA<BB` 中取得 `<` 符號的位置時，結果為 4。

```php
mb_strpos("\xf0\x9fAAA<BB", "<"); // 4
```

上述的判斷邏輯好像沒什麼問題，但是當我們想使用 `mb_substr` 來取得 `<` 符號前面的所有字時，就會出現問題。

因為 `mb_substr` 對於前導位元組的處理，與 `mb_strpos` 完全不同。
`mb_substr` 只要遇到前導位元組，就會直接跳過後面三個位元組。

```text
\xf0 <- 發現前導位元組，mb_substr 預期後面會有 3 個連續位元組來顯示一個完整的字，該字節的索引為 0
\x9f <- 跳過！該位元組的索引為 0
A    <- 跳過！該字元的索引為 0
A    <- 跳過！該字元的索引為 0
A    <- 該字元的索引為 1
<    <- 該字元的索引為 2
B    <- 該字元的索引為 3
B    <- 該字元的索引為 4
```

因此當我們在 `mb_substr` 使用前面取得的 `<` 符號位置索引。會有與預期完全不同的結果。

```php
// 我們希望取得 "<" 符號前面的字串，也就是 "\xf0\x9fAAA"
// 實際上結果卻是 "\xf0\x9fAAA<B"，"<" 符號並沒有被排除掉
mb_substr("\xf0\x9fAAA<BB", 0, 4);
```

可以看到 `<` 符號被過濾機制忽略了，XSS 漏洞也因此產生。

> 最新版本的 PHP 與 Joomla 都已經修復這個漏洞。

## 參考資料

- [Joomla: PHP Bug Introduces Multiple XSS Vulnerabilities](https://www.sonarsource.com/blog/joomla-multiple-xss-vulnerabilities/)
