# 控制流

根據條件來執行部分程式碼。

## if

如果條件滿足，就執行這段程式碼，反之則不執行。

`if` 後面的條件必須返回 `bool` 值。

```rust
fn main() {
    let number = 3;

    if number < 5 {
        println!("condition was true");
    } else {
        // 程式碼會執行這段程式碼
        println!("condition was false");
    }
}
```

因為 Rust 為強型別，所以並不會**自動幫你轉換類型**，`number` 的型別並非是 `boolean` 所以會出現錯誤。

```rust
if number {
    println!("this will fail");
}
```

## else if

可以使用 `else if` 來實現多重條件，當符合其中一個條件時，其餘判斷條件下的程式碼都不會執行。

```rust
fn main() {
    let number = 6;

    if number % 4 == 0 {
        println!("number is divisible by 4");
    } else if number % 3 == 0 {
        println!("number is divisible by 3");
    } else if number % 2 == 0 {
        println!("number is divisible by 2");
    } else {
        println!("number is not divisible by 4, 3, or 2");
    }
}
```

## 在 let 語句中使用 if

Rust 的 `if` 是表達式 (expression)，所以會回傳值。
也可以結合 `let` 語句使用，將判斷後的結果值返回給指定的變數。

```rust
fn main() {
    let condition = true;

    let number = if condition {
        5
    } else {
        6
    };

    // number is 5
    println!("The value of number is: {}", number);
}
```

但要注意的是，因為變數不允許隨意改變類型，**因此每個條件返回的值必須是同類型的**。

```rust
fn main() {
    let condition = true;

    // 這段程式碼會出現錯誤
    let number = if condition {
        5
    } else {
        "six"
    };

    println!("The value of number is: {}", number);
}
```

## 迴圈

Rust 中有三種迴圈：

- `loop`
- `while`
- `for`

### loop

`loop` 關鍵字會不斷重複執行區域中的程式碼，除非在區域中使用 `break`。

`loop` 的其中一種使用方式是重複可能會失敗的操作，比如檢查執行緒上的任務是否完成，任務完成回傳一個結果讓 `break` 結束迴圈。

```rust
fn main() {
    loop {
        println!("again!");
    }
}
```

`loop` 也是表達式，可以回傳結果。
`loop` 其中一種使用方式是重複可能會失敗的操作，比如檢查執行緒上的任務是否完成，任務完成可以**回傳一個結果**並讓 `break` 結束迴圈。

```rust
fn main() {
    let mut counter = 0;

    let result = loop {
        counter += 1;

        if counter == 10 {
            break counter * 2;
        }
    };

    assert_eq!(result, 20);
}
```

### while

`while` 後面可以接一個條件，當條件為 `true` 就繼續執行，直到條件為 `false`。

```rust
fn main() {
    let mut number = 3;

    while number != 0 {
        println!("{}!", number);

        number = number - 1;
    }

    println!("loop end!");
}
```

### for

可以使用 `for` 來遍歷一個集合中的所有元素。

```rust
fn main() {
    let a = [10, 20, 30, 40, 50];

    for element in a.iter() {
        println!("the value is: {}", element);
    }
}
```

## 參考資料

- [learnku - Rust 程式語言 - 控制流](https://learnku.com/docs/rust-lang/2018/ch03-05-control-flow/4503)
