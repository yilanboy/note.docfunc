---
layout: default
parent: PHP
nav_order: 12
---

# 在 PHP 中判斷 Magic Bytes

## 什麼是 Magic Bytes

Magic bytes(又稱 file signature)是檔案開頭固定的幾個位元組,用來標示檔案真正的格式。
相較於副檔名與 MIME，它讀的是**檔案內容本身**，無法靠改名偽造。

## 為什麼不要只信副檔名或 finfo

- **副檔名**可隨意更改：`malware.exe` 改名 `photo.ttf` 副檔名就騙過去了。
- **`finfo`(libmagic)** 對某些格式(尤其字型)判斷不一致：同一個合法檔在不同系統/版本可能回傳
  `font/sfnt`、`application/octet-stream` 等不同結果,容易誤判或誤殺。
- 直接讀 magic bytes 是**確定性**的：同樣的位元組永遠得到同樣結論,且無外部相依。

## 核心做法

1. 用**二進位模式**開檔(`'rb'`，Windows 上很重要)。
2. 只讀開頭需要的位元組數。
3. 用 `bin2hex()` 轉成可讀的 hex 字串再比對(比直接比對 `"\x00\x01"` 這種 raw bytes 好讀)。
4. 關閉 handle(PHP 8.1+ 物件離開作用域會自動回收,但顯式 `fclose` 仍是好習慣)。

```php
function matchesMagic(string $filePath, string $expectedHex): bool
{
    if (! is_file($filePath) || ! is_readable($filePath)) {
        return false;
    }

    $handle = fopen($filePath, 'rb');
    if ($handle === false) {
        return false;
    }

    // 讀的長度 = magic number 的位元組數(hex 字串長度的一半)
    $length = intdiv(strlen($expectedHex), 2);
    $header = bin2hex((string) fread($handle, $length));
    fclose($handle);

    return $header === strtolower($expectedHex);
}
```

## 實例:判斷 TrueType 字型(.ttf)

字型檔開頭是 4 bytes 的 sfnt version tag，TrueType 為 `0x00010000`:

```php
final class FontValidator
{
    // sfnt version tag (hex):TrueType outlines,排除 OpenType('OTTO')
    private const string VALID_TTF_HEADER = '00010000';

    public static function isValidTtf(string $filePath): bool
    {
        if (! is_file($filePath) || ! is_readable($filePath)) {
            return false;
        }

        // 同時保留副檔名約束(可選)
        if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'ttf') {
            return false;
        }

        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            return false;
        }

        $header = bin2hex((string) fread($handle, 4));
        fclose($handle);

        return $header === self::VALID_TTF_HEADER;
    }
}
```

## 該讀幾個 bytes?

讀「能唯一辨識格式的最小長度」。多讀沒幫助、少讀無法區分。

> ⚠️ 注意:有些參考表(例如 Wikipedia 的 List of file signatures)把 TTF 列成 5 bytes
> `00 01 00 00 00`。第 5 個 `00` 其實是下一個欄位 `numTables`(uint16)的高位元組,
> 因為字型不可能有 256+ 個 table 所以恆為 0。**規格定義的格式識別碼只有前 4 bytes**;
> 讀 4 bytes 是貼著 spec 的乾淨做法,讀 5 bytes 只是多一層保險。

## 常見格式的 Magic Bytes

| 格式              | Hex                                   | 長度       |
| ----------------- | ------------------------------------- | ---------- |
| PNG               | `89 50 4E 47 0D 0A 1A 0A`             | 8          |
| JPEG              | `FF D8 FF`                            | 3          |
| GIF               | `47 49 46 38`                         | 4          |
| PDF               | `25 50 44 46`                         | 4          |
| ZIP / docx / xlsx | `50 4B 03 04`                         | 4          |
| TrueType (.ttf)   | `00 01 00 00`                         | 4          |
| OpenType (.otf)   | `4F 54 54 4F`                         | 4          |
| WebP              | `52 49 46 46 .. .. .. .. 57 45 42 50` | 12(含偏移) |

## 注意事項(Gotchas)

- **一定用 `'rb'`**：文字模式在 Windows 會做換行轉換,污染二進位內容。
- **`fread` 可能短讀**：檔案比要讀的長度短時只回傳實際讀到的，用 `bin2hex((string) ...)` 包起來避免 `false`,
  比對長度不符自然會 fail。
- **不是所有格式都在 offset 0**：像 WebP 的 `WEBP` 標記在第 8 byte，需先讀一段再比對指定區段。
- **magic bytes 只證明「格式正確」**：不保證檔案內容完整有效，真要確認可用該格式的 parser 進一步驗證。
- **效能**：只讀開頭幾個 byte,成本極低,適合放在驗證/上傳檢查的第一道關卡。

## 一句話總結

> 別信副檔名，別全信 `finfo`。用 `'rb'` 開檔、讀開頭固定長度、`bin2hex` 比對 signature ——
> 確定、無相依、騙不過去。
