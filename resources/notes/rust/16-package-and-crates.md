# Packages 與 Crates

Crate 是 Rust 編譯器能執行成功的最小程式碼量。

舉個例子，新增一個 `main.rs` 檔案，內容為一個印出 `Hello World!` 簡單程式碼。

```rust
fn main() {
    println!("Hello World!");
}
```

使用 `rustc` 編譯這個檔案。

```bash
rustc main.rs

# 編譯成功之後會生成一個可執行檔案
./main
```

對 Rust 編譯器來說，這個 `main.rs` 檔案，就可以被看成是一個 Crate。

## Binary Crate 與 Library Crate

Crate 有兩種形式，一種為 Binary Crate，另外一種為 Library Crate。

Binary Crate 可以編譯並產生一個可執行程式，例如我們常用的命令行工具，前面筆記所有寫的程式碼都屬於 Binary Crate。

Library Crate 不會有 `main` 函式，他們也不會被編譯成可執行程式，他們會定義許多功能，並用在其他專案上，例如之前用來產生一組隨機數的 `rand` 就是一種 Library Crate，通常 Rustaceans 提到 Crate 的時候，更多是指 Library Crate。

## Crate Root

Crate Root 是一個源文件 (source file)，Rust 編譯器會從從該源文件開始去建構你的 Crate Root Module。

## Package

Package 是一個包含許多 Crate 並提供一組特定功能或方法的程式碼。

Package 會包含 `Cargo.toml` 去描述這個 Package 需要依賴哪些 Crate。

Cargo 實際上是一個軟體包，包含了你用來構建程式碼所需命令行工具的 Binary Crate，當中也包含這些命令行工具所需要的 Library Crate。

Package 可以包含**許多 Binary Crate，但最多只能有一個 Library Crate**。

Package 至少要包含一個 Crate，不論是 Binary Crate 或是 Library Crate 都可以。

## main.rs 與 lib.rs

使用 `cargo new` 新建一個初始 Package 時，Cargo 會自動認定 `src/main.rs` 為該 Package Binary Crate 的 Crate Root。

> 這個 Crate 的名稱會與專案名稱相同。

如果是 `src/lib.rs` 就會認定為 Library Crate 的 Crate Root。

Package 可以有多個 Binary Crate，但需要將其放置於 `src/bin` 目錄底下，每一個檔案都會被視為一個 Binary Crate。
