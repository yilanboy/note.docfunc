# 寫一個使用 struct 的範例程式

使用 Rust 寫一個計算長方形面積的程式。

```rust
fn main() {
    let width1 = 30;
    let height1 = 30;

    println!(
        "The area of the rectangle is {} square pixels.",
        area(width1, height1)
    );
}

fn area(widht: u32, height: u32) -> u32 {
    width * height
}
```

上面的函式 `area()` 的參數為長方形的長與寬，雖然意思是這樣，但從程式碼的設計上卻看不出這一點。

試著使用元組類型重構。

```rust
fn main() {
    let rect1 = (30, 50);

    println!(
        "The area of the rectangle is {} square pixels.",
        area(rect1)
    );
}

fn area(dimensions: (u32, u32)) -> u32 {
    dimensions.0 * dimensions.1
}
```

雖然只需要一個參數，但誰是長誰是寬反而看不出來，這時候我們可以使用 struct 來解決這個問題。

```rust
struct Rectangle {
    width: u32,
    height: u32,
}

fn main() {
    let rect1 = Rectangle { width: 30, height: 50 };

    println!(
        "The area of the rectangle is {} square pixels.",
        area(&rect1)
    );
}

fn area(rectangle: &Rectangle) -> u32 {
    rectangle.width * rectangle.height
}
```

使用 struct 可以組合一組相關聯的參數，並給這些參數賦予命名，使其他人能更容易理解這段程式碼在做什麼。

## 通過 Derived Trait 增加實用的功能

嘗試一下印出 Rectangle struct 的值。

```rust
struct Rectangle {
    width: u32,
    height: u32,
}

fn main() {
    let rect1 = Rectangle { width: 30, height: 50 };

    println!("rect1 is {}", rect1); // error: the trait `std::fmt::Display` is not implemented for `Rectangle`
}
```

雖然 `println!` 能夠印出很多類型 (例如數字與字串)，不過 `{}` 默認使用被稱為 `Display` 的格式。

不過 `Rectangle` 並沒有提供 `Display` 格式，`println!` 會不知道該用什麼格式印出。

這個時候我們可以使用 `{:?}`，代表我們要使用 `Debug` 的格式印出 `Rectangle`。

```rust
println!("rect1 is {:?}", rect1);
```

`Debug` 是一個 trait，它允許我們以一種對開發者有幫助的方式印出 struct。

但上述的打印方式依舊行不通，會出現以下的錯誤。

```text
  = help: the trait `Debug` is not implemented for `Rectangle`
  = note: add `#[derive(Debug)]` to `Rectangle` or manually `impl Debug for Rectangle`
  = note: this error originates in the macro `$crate::format_args_nl` which comes from the expansion of the macro `println` (in Nightly builds, run with -Z macro-backtrace for more info)
help: consider annotating `Rectangle` with `#[derive(Debug)]`
```

Rust 確實包含為 struct 印出其結構的功能，但是我們必須在程式碼中註明要為 struct 使用這個功能。

因此在 struct 前面，我們必須要加上 `#[derive(Debug)]`

```rust
#[derive(Debug)]
struct Rectangle {
    width: u32,
    height: u32,
}

fn main() {
    let rect1 = Rectangle { width: 30, height: 50 };

    println!("rect1 is {:?}", rect1);
}
```

使用註解來派生 `Debug` 的 trait，就可以使用除錯格式印出 `Rectangle` 實例

不過印出來的格式並不是很漂亮。

```text
rect1 is Rectangle { width: 30, height: 50 }
```

我們可以使用 `{:#?}`，那麼最後印出來的結果就會經過排版。

```text
rect1 is Rectangle {
    width: 30,
    height: 50
}
```
