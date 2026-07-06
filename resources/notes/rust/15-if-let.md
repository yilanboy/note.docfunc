# if let 簡潔控制流

`if let` 是一種用來替代 `match` 的簡潔語法，但主要處理**只匹配一個模式的值而忽略其他模式的情況**。

例如下方的程式碼：

```rust
let some_u8_value = Some(0u8);

match some_u8_value {
    Some(3) => println!("three"),
    _ => (),
}
```

假設我們只關心 `Some(3)`，而不想處理任何其他 `Some<u8>` 與 `None` 的值，那麼就可以使用 `if let` 來替代 `match`。

```rust
let some_u8_value = Some(0u8)

if let Some(3) = some_u8_value {
    println!("three");
}
```

`if let` 雖然語法更簡潔，但這會失去 `match` 窮盡所有可能性的檢查。因此這兩者算是語法與功能上的取捨。

`match` 中可以使用 `_` 用來概括其他模式的處理，`if let` 中同樣也可以使用 `else` 來概括。

例如下方的程式碼：

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

let coin = Coin::Penny;

let mut count = 0;

match coin {
    Coin::Quarter(state) => println!("State quarter from {:?}!", state),
    _ => count += 1,
}
```

如果使用 `if let` 搭配 `else` 改寫，可以寫成：

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

let coin = Coin::Penny;

let mut count = 0;

if let Coin::Quarter(state) = coin {
    println!("State quarter from {:?}!", state);
} else {
    count += 1;
}
```

如果你遇到一個用 `match` 表達起來過於囉嗦的邏輯，就可以考慮使用 `if let` 代替。
