# Cargo 筆記

Cargo 為 Rust 為的套件管理工具，可以使用 Cargo 來新建專案與安裝套件。

## 相關指令

使用 cargo 指令新建 hello_world 的專案。

```bash
cargo new hell_world
```

執行完之後的檔案結構如下：

```shell
$ tree .
.
├── Cargo.toml
└── src
    └── main.rs

1 directory, 2 files
```

檢查程式碼能否執行的指令。

```bash
cargo check
```

執行程式的指令。

```bash
cargo run
```

編譯執行檔案的指令。

```bash
cargo build
```

如果想要新增套件，可以在 `Cargo.toml` 中的 `[dependencies]` 底下加上想安裝的套件與版本號碼。

```toml
[dependencies]
rand = "0.8.5"
```

在執行下方的指令安裝套件。

```bash
cargo build
```

但建議還是使用 Cargo 來安裝套件，執行下方的指令安裝套件。

```bash
cargo add <package_name>
```

更新套件的指令。

```bash
cargo update
```

## 全域安裝套件

以 Tauri 為例，安裝 `tauri-cli` 的指令如下：

```bash
cargo install tauri-cli
```

安裝的套件預設會放在 `$HOME/.cargo/bin` 底下 (MacOS)。

之後就可以使用 `tauri` 指令來建立專案。

```bash
cargo tauri init
```

## 執行測試

可以使用 `cargo test` 來執行測試。 測試執行會產生一個測試執行檔案。

```bash
cargo test
```

## 發佈設定檔 (Release Profiles)

Cargo 有兩個主要的設定檔：

- `dev` 設定檔會在當你對 Cargo 執行 `cargo build` 時所使用。
- `release` 設定檔會在當你對 Cargo 執行 `cargo build --release` 時所使用。

dev 設定檔預設定義為適用於開發時使用，而 release 設定檔預設定義為適用於發佈時使用。

```toml
# opt-level 設定控制了 Rust 對程式碼進行優化的程度，範圍從 0 到 3
# 0: 不進行優化
# 3: 進行最大優化，但會增加編譯時間
[profile.dev]
opt-level = 0

[profile.release]
opt-level = 3
```

對於完整的設置選項與每個設定檔的預設列表，請查閱 [Cargo 的技術文件](https://doc.rust-lang.org/cargo/reference/profiles.html)。

## 發佈 Crate 到 Crates.io

除了從 crates.io 下載 Rust 套件，我們也可以自己寫套件，並將其推送至 crates.io 讓別人來使用。

Rust 與 Cargo 有許多功能可以幫助其他人更容易找到你發佈的套件。下面會一一介紹。

### 寫上有幫助的技術文件註解

Rust 中可以使用 `///` 來寫**技術文件註解**，這可以用來產生 HTML 技術文件。

````rust
/// Adds one to the number given.
///
/// # Examples
///
/// ```
/// let arg = 5;
/// let answer = my_crate::add_one(arg);
///
/// assert_eq!(6, answer);
/// ```
pub fn add_one(x: i32) -> i32 {
    x + 1
}
````

上面的 `# Examples` 可以建立一個名為 Example 的文件段落，以下是常用的技術文件段落：

- Panics：說明該函式可能會導致恐慌的情況，請確保使用者沒有在這些情況下使用該函式。
- Errors：如果函式會回傳 `Result`，需要說明哪種情況下可能會出現錯誤，幫助使用者處理所有可能發生的錯誤。
- Safety：如果函式是 `unsafe` 的話，需要說明為什麼這個函式是不安全的，並提及函式預期呼叫者要確保哪些不便條件 (invariants)。

### 將技術文件註解作為測試

在技術文件註解加上範例程式還有一個好處，就是執行 `cargo test` 時，文件中範例程式也會一起被執行並顯示測試結果。確保技術文件與程式碼是同步的，不會修改程式碼後，忘記修改文件。

### 包含項目結構的文件註解

用來描述整個 crate 或是模組的的說明文件，使用 `//!`。與剛剛的技術文件不同，這種文件後面不會有程式。通常會放在 crate 的源頭檔案，例如 `src/lib.rs`。

````rust
//! # My Crate
//!
//! `my_crate` is a collection of utilities to make performing certain
//! calculations more convenient.

/// Adds one to the number given.
///
/// # Examples
///
/// ```
/// let arg = 5;
/// let answer = my_crate::add_one(arg);
///
/// assert_eq!(6, answer);
/// ```
pub fn add_one(x: i32) -> i32 {
    x + 1
}
````

當執行 `cargo doc --open`，這些註解會顯示在技術文件的首頁。說明這個 crate 的主要用途。
