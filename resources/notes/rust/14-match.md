# match 控制流運算符

Rust 提供一種 `match` 控制流程，可以將一個值與一系列的模式相比較，根據相匹配的結果執行對應的程式碼。

> `match` 好用的點來自於編譯器的檢查，編譯器會確保所有可能的情況都會得到處理。

假設有一個函式可以獲取一個未知的美幣，並以類似驗鈔機的功能，判斷他是何種硬幣後返回硬幣的美金幣值。

```rust
enum Coin {
    Penny,
    Nickel,
    Dime,
    Quarter,
}

fn value_in_cents(coin: Coin) -> u32 {
    // 這裡的 coin 為表達式，也是 match 的判斷式
    match coin {
        // 模式 (pattern) => 程式碼
        // 當 coin 符合底下其中一個模式時，就執行 => 後方的程式碼
        Coin::Penny => 1,
        Coin::Nickel => 5,
        Coin::Dime => 10,
        Coin::Quarter => 25,
    }
}
```

`match` 中會列出各種模式 (pattern) 與其對應要執行的程式碼。

`match` 跟 `if` 的差別在 `if` 的判斷式要求為 `bool` 值，但 `match` 的條件可以是任何類型。

如果需要在符合模式之後執行一段多行的程式碼，可以用大括號包裹多行程式碼。

```rust
fn value_in_cents(coin: Coin) -> u32 {
    match coin {
        Coin::Penny => {
            println!("Lucky penny!");
            1
        },
        Coin::Nickel => 5,
        Coin::Dime => 10,
        Coin::Quarter => 25,
    }
}
```

## 綁定值的模式

`match` 另一個有用的特點是可以綁定匹配模式的部分值。這也是我們如何從列舉變數中提取數值的方法。

舉個例子，修改 `Coin` 中的成員 `Quarter`，使其帶上一個州的資料。

```rust
#[derive(Debug)] // 這樣可以立刻看到州的名稱
enum UsState {
    Alabama,
    Alaska,
    // --snip--
}

enum Coin {
    Penny,
    Nickel,
    Dime,
    Quarter(UsState),
}
```

在用 `match` 匹配的過程中，我們可以在匹配 `Coin::Quarter` 的模式中加上一個 `state` 的參數。

當匹配到 `Coin::Quarter` 時，`state` 就會自動綁定 `Quarter` 設定的值。

```rust
#[derive(Debug)]
enum UsState {
    Alabama,
    Alaska,
}

enum Coin {
    Penny,
    Nickel,
    Dime,
    Quarter(UsState),
}

fn value_in_cents(coin: Coin) -> u32 {
    match coin {
        Coin::Penny => 1,
        Coin::Nickel => 5,
        Coin::Dime => 10,
        Coin::Quarter(state) => {
            println!("State quarter from {:?}!", state); // Alaska
            25
        },
    }
}

fn main() {
    value_in_cents(Coin::Quarter(Alaska));
}
```

## 匹配 Option

可以使用 `match`，將 `Option` 中的 `T` 給拿出來。

```rust
let some_value = Some(1);

let result = match some_value {
    Some(i) => i,
    None => {
        panic!("There is no value");
    }
};

println!("{}", result); // 1
```

來寫一個函式，它獲取一個 `Option<32>` 參數，如果有值就將其加一，沒有值就返回 `None`。

```rust
fn plus_one(x: Option<i32>) -> Option<i32> {
    match x {
        None => None,
        Some(i) => Some(i + 1),
    }
}

let five = Some(5);
let six = plus_one(five);
let none = plus_one(None);
```

## 匹配 Some(T)

在剛剛的操作中，當調用 `plus_one(five)` 時，`plus_one` 函式中的 `x` 值會是 `Some(5)`。

比較第一個模式，很明顯不匹配。

```rust
None => None,
```

比較第二個模式，`Some(i)` 與 `Some(5)` 是匹配的，因此執行對應的程式碼。

```rust
Some(i) => Some(i + 1),
```

而調用 `plus_one(None)` 時，第一個模式就匹配了，所以返回 `None`。

## 匹配是窮盡的

`match` 有一個特性需要特別討論。以下方的程式碼為例，這段程式碼是無法執行的。

```rust
fn plus_one(x: Option<i32>) -> Option<i32> {
    match x {
        Some(i) => Some(i + 1),
    }
}
```

原因就在於我們沒有處理 `None` 的情況，這個時候 Rust 會貼心的提醒我們。

```text
rror[E0004]: non-exhaustive patterns: `None` not covered
 --> src/main.rs:6:11
  |
6 |     match x {
  |           ^ pattern `None` not covered
  |
```

Rust 知道我們沒有覆蓋到所有可能的情況，甚至知道有哪些情況被我們忽略了，Rust 中的**匹配是窮盡 (exhaustive) 的，必須要窮舉所有可能性來使程式碼可以執行**。

## 通配符 **\_**

Rust 也提供一個模式 `_` 用於囊括所有不符合其他模式的處理方式。

例如 `u8` 可以擁有 0 到 255 的值，假設我們只關心 1, 2, 3 的值，又不想全部列出 4 到 255 的值，這時候就可以使用 `_` 代替。

```rust
let some_u8_value = 0u8;

match some_u8_value {
    1 => println!("one"),
    2 => println!("two"),
    3 => println!("three"),
    _ => (),
}
```
