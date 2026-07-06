# Test Organization

Rust 社群將測試分為兩大分類術語：單元測試和整合測試。

- 單元測試（unit tests）比較小且較專注，傾向在隔離環境中一次只測試一個模組，且能夠測試私有介面。
- 整合測試（integration tests）對於你的函式庫來說是個完全外部的程式碼，整合測試會如其他外部程式碼一樣使用你的程式碼，只能使用公開介面且每個測試可能會有數個模組。

## 單元測試

單元測試是要在隔離其他程式碼的狀況下測試每個程式碼單元，迅速查明程式碼有沒有如預期或非預期的方式運作。

單元測試通常會放在 src 目錄中，在目標測試程式的同個檔案下。

常見的做法是在每個檔案建立一個模組 `tests` 來包含測試函式，並用 `cfg(test)` 來詮釋模組。

```rust
// src/lib.rs
pub fn add(left: usize, right: usize) -> usize {
    left + right
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn it_works() {
        let result = add(2, 2);
        assert_eq!(result, 4);
    }
}
```

### 測試模組與 `#[cfg(test)]`

測試模組上的 `#[cfg(test)]` 詮釋會告訴 Rust 當你執行 `cargo test` 才會編譯並執行測試程式碼。

因為 `#[cfg(test)]` 只會在執行 `cargo test` 時才會編譯，所以測試模組中的程式碼不會出現在最後的執行檔中。

### 測試私有函式

在其他程式語言中，測試私有函式一直有許多爭論，而且有些語言會讓測試私有函式相當不容易。

但在 Rust 中，測試私有函式並不困難。

```rust
// 因為沒有加上 `pub`，此為私有函式
fn internal_adder(a: i32, b: i32) -> i32 {
    a + b
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn internal() {
        assert_eq!(4, internal_adder(2, 2));
    }
}
```

## 整合測試

整合測試對於你的函式庫來說完全是個外部的程式碼。

整合測試用於模擬一般使用者會如何使用你的函式庫，並測試你的函式庫是否能如預期運作。

整合測試會放在專案的 `tests` 目錄下，而不是在 `src` 目錄下。以剛剛的 `addr` 為例，目錄結構如下：

```text
adder
├── Cargo.lock
├── Cargo.toml
├── src
│   └── lib.rs
└── tests
    └── integration_test.rs
```

`integration_test.rs` 會被當作一個獨立的 crate 來編譯和執行，所以你需要將你的函式庫引入。

```rust
use adder;

#[test]
fn it_adds_two() {
    assert_eq!(4, adder::add_two(2));
}
```

執行測試後的結果如下：

```text
   Compiling adder v0.1.0 (/Users/allen_chiang/code/rust/adder)
    Finished test [unoptimized + debuginfo] target(s) in 0.64s
     Running unittests src/lib.rs (target/debug/deps/adder-4416ff7d1fbf12b3)

running 1 test
test tests::it_works ... ok

test result: ok. 1 passed; 0 failed; 0 ignored; 0 measured; 0 filtered out; finished in 0.00s

     Running tests/intergration_test.rs (target/debug/deps/intergration_test-e0a99be11be46b03)

running 1 test
test it_adds_two ... ok

test result: ok. 1 passed; 0 failed; 0 ignored; 0 measured; 0 filtered out; finished in 0.00s

   Doc-tests adder

running 0 tests

test result: ok. 0 passed; 0 failed; 0 ignored; 0 measured; 0 filtered out; finished in 0.00s
```

總共有三段測試，第一段是單元測試，第二段是整合測試，第三段是文件測試。

如果要執行特定的整合測試，可以使用 `cargo test` 的 `--test` 參數，使用檔案名稱來指定要執行的整合測試。

```bash
cargo test --test integration_test
```

### 整合測試的子模組

如果你的整合測試有共用的邏輯，可以將其模組化，提升程式碼的可讀性。

我們新增一個 `tests/common/mod.rs` 檔案。

```text
addr
├── Cargo.lock
├── Cargo.toml
├── src
│   └── lib.rs
└── tests
    ├── common
    │   └── mod.rs
    └── integration_test.rs
```

建立好模組之後，我們就可以在任何整合測試檔案中使用它。

```rust
use adder;

// 使用模組
mod common;

#[test]
fn it_adds_two() {
    // 使用模組的 setup() 方法
    common::setup();
    assert_eq!(4, adder::add_two(2));
}
```

### 執行檔 Crate 的整合測試

如果我們的專案只包含 `src/main.rs` 檔案的執行檔 crate 而沒有 `src/lib.rs` 檔案的話，我們無法在 `tests` 目錄下建立整合測試，也無法將 `src/main.rs` 檔案中定義的函式透過 `use` 陳述式引入作用域。

只有函式庫 crate 能公開函式給其他 crate 使用，執行檔 crate 只用於獨自執行。
