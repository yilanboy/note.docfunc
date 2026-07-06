# 數據類型

Rust 為**靜態類型**的語言，代表編譯時就必須知道所有變數的資料類型。

如果是編譯器無法確定變數類型的情況，就一定要特別標註變數的類型。

```rust
let guess: u32 = "42".parse().expect("Not a number!");
```

## 標量類型 (scalar)

標量類型代表一個單獨的值，Rust 有四種基本的標量類型：

- 整數
- 浮點
- 布爾值
- 字符

### 整數

整數是一個沒有小數部分的數字，Rust 的默認類型是 `i32`。

| 長度   | 有符號(負號) | 無符號 |
| ------ | ------------ | ------ |
| 8-bit  | i8           | u8     |
| 16-bit | i16          | u16    |
| 32-bit | i32          | u32    |
| 64-bit | i64          | u64    |
| arch   | isize        | usize  |

根據 bit，有符號的可以儲存 $-(2^{n - 1})$ ~ $(2^{n - 1}) - 1$，所以 `i8` 可以儲存 `-128 ~ 127` 的數字。

而無符號的 `u8` 就是 0 ~ $(2^n - 1)$ 所以是 `0 ~ 255`。

如果給定超過規定的數字就會發生整數溢出 (integer overflow) 的問題，例如類型 `u8`，數值卻設定 256。

Rust 在編譯時不會檢測溢出，但會進行一種被稱謂 two's complement wrapping 的操作，因此在 `u8` 下設定 256 時，會自動變成 0，257 會變成 1，以此類推。

```rust
let x: u32 = 123; // 顯示指定類型
```

### 浮點

浮點是只有小數點的數字，Rust 有兩種，分別是 `f32` 與 `f64`。

Rust 預設是 `f64`。

```rust
fn main() {
    let x = 2.0; // f64

    let y: f32 = 3.0; // f32
}
```

### 布爾值

使用 `bool` 表示，只有 `true` 與 `false`。

最多的使用場景是在控制流 (control flow) 中的 `if` 表達式。

```rust
fn main() {
    let t = true;

    let f: bool = false;
}
```

### 字符

Rust 支援字母類型，使用 `char`。

注意 `char` 是使用**單引號**，字串才是使用雙引號。

`char` 類型代表一個 Unicode。

```rust
fn main() {
    let c = 'z';
    let nice = '👍';
}
```

## 複合類型 (compound types)

複合類型可以將多個值組合成一個類型。

Rust 有兩個原生的複合類型：

- 元組 (tuple)
- 數組 (array)

### 元組

元組是一個將多個不同類型組合近一個複合類型的主要方式。

```rust
fn main() {
    let tup: (i32, f64, u8) = (500, 6.4, 1)
}
```

想要從元組中取得單個值，可以使用模式匹配 (pattern matching) 來解構 (destructure) 元組值。

```rust
fn main() {
    let tup = (500, 6.4, 1)

    let (x, y, z) = tup; // 解構

    println!("The value of y is: {}", y);
}
```

除了使用解構，也可以使用點號 `.` 以索引的方式訪問個別值，索引值是從 0 開始。

```rust
fn main() {
    let x: (i32, f64, u8) = (500, 6.4, 1);

    let five_hundred = x.0;

    let six_point_four = x.1;

    let one = x.2;
}
```

### 數組

數組中的值必須為同類型。

Rust 中 array 的**長度為固定**的，一旦宣告，長度就不能修改。

```rust
fn main() {
    let a = [1, 2, 3, 4, 5];
}
```

如果你不能確定長度，可以考慮使用 `vector` 這種數組類型，`vector` 的長度可以修改。

數組的宣告方式如下，除了宣告類型，長度也需要。

```rust
let a: [i32; 5] = [1, 2, 3, 4, 5];
```

訪問個別元素的方式。

```rust
fn main() {
    let a = [1, 2, 3, 4, 5];

    let first = a[0];
    let second = a[1];
}
```

訪問無效索引的話，Rust 在編譯時就會出現 panic (舊版本在執行時才會發現錯誤)。

```rust
fn main() {
    let a = [1, 2, 3, 4, 5];
    let index = 10; // 編譯時 Rust 會進行檢查

    let element = a[index];

    println!("The value of element is: {}", element);
}
```

## 參考資料

有關於什麼是 Unicode (萬國編碼)，什麼是 ASCII，可以參考這個[影片](https://www.youtube.com/watch?v=zSstXi-j7Qc)。
