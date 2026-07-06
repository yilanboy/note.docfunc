# 函式

函式需要在前面加上 `fn` 關鍵字。

Rust 中函式名稱與變數名稱使用 **snake_case** 規範。

Rust 程式碼的入口點是 `main` 函式。

函式的順序並不重要，只要一定義好，你可以在其前或其後進行呼叫 (編譯型語言的特徵)。

```rust
fn main() {
    println!("Hello, world!");

    another_function();
}

fn another_function() {
    println!("Another function.");
}
```

函式可以設定參數，且參數**必須定義好類型**。

```rust
fn main() {
    another_function(5);
}

fn another_function(x: i32) {
    println!("The value of x is: {}", x);
}
```

Rust 是一個基於表達式 (expression based) 的語言。

陳述式 (statement) 是一些執行操作但不返回值的指令。

表達式 (expression) 在計算後會返回一個值。

`let` 宣告變數為陳述式。

```rust
fn main() {
    // 你不能這麼做，因為 let y = 6 不會返回值
    let x = (let y = 6);
}
```

在其他語言，變數宣告是會返回值，例如 PHP。

```php
<?php

$x = $y = 6;

echo $x; // 6
echo $y; // 6
```

簡單的計算，例如 `5 + 6`，在 Rust 中為表達式，會返回值。

或是用來創建新作用域的 `{ }`，也是一種表達式。

在 Rust 中，想返回的值可以以表達式的方式寫在作用域的最後一行，但不能加上 `;`。

```rust
fn main() {
    let y = {
        let x = 3;

        // 在 Rust 中，想返回的值可以以表達式的方式寫在作用域的最後一行
        // 切記不能加上分號 ";"，否則這會變成陳述式
        x + 1
    };

    // y = 4
    println!("The value of y is: {}", y);
}
```

函式如果有返回值，也需要事先定義好返回值的類型。

```rust
fn plus_one(x: i32) -> i32 {
    // 不能加上 ";"
    x + 1
}

fn main() {
    let x = plus_one();

    println!("The value of x is: {}", x);
}
```

## 參考資料

- [learnku - Rust 程式語言 - 函式如何工作](https://learnku.com/docs/rust-lang/2018/ch03-03-how-functions-work/4501)
