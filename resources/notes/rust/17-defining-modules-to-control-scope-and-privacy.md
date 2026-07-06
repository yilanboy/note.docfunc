# 定義模組 (Modules) 來控制作用域 (Scope) 與私有性 (Privacy)

當編譯一個 crate 時，編譯器會從 crate 根目錄開始編譯。

- 如果是 library crate，會從 `src/lib.rs` 開始。
- 如果是 binary crate，會從 `src/main.rs` 開始。

我們可以在 crate 根目錄宣告一個 `mod garden;` 編譯器會從以下這些地方去尋找。

- 在同檔案尋找 `mod garden`。

```rust
mod garden {
    // ...
}
```

- 根據路徑，例如 `src/garden.rs`。
- 也是根據路徑，例如 `src/garden/mod.rs`。

你可以根據路徑來尋找項目 (item)，例如有一個 `Asparagus` 項目存放在 garden 中 vegetables 模組底下，那你可以使用 `crate::garden::vegetables::Asparagus` 找到這個項目。

`mod` 預設是私有的，可以使用 `pub` 關鍵字使其成為公有 (public)。

可以使用 `use` 關鍵字將一個模組或是項目引入作用域，以避免重複寫很長的路徑。

以剛剛 Asparagus 的項目為例，在檔案中使用 `use crate::garden::vegetables::Asparagus;`，之後如果要使用 Asparagus 項目只需要寫 `Asparagus` 即可。

假設我們有一個 backyard 專案，其專案結構如下：

```text
backyard
├── Cargo.lock
├── Cargo.toml
└── src
    ├── garden
    │   └── vegetables.rs
    ├── garden.rs
    └── main.rs
```

crate 根目錄為 `src/main.rs`，其內容為：

```rust
use crate::garden::vegetables::Asparagus;

pub mod garden;

fn main() {
    let plant = Asparagus {};
    println!("I'm growing {:?}!", plant);
}
```

`pub mod garden;` 告訴編譯器將 `src/garden;` 的程式碼也一起編譯進來。

因為有使用 `use` 將 `crate::garden::vegetables::Asparagus` 引入作用域，所以後續我們可以直接使用 `Asparagus`，不用再寫完整的路徑。

`src/garden/vegetables.rs` 的內容為：

```rust
#[derive(Debug)]
pub struct Asparagus {}
```

模組可以讓程式碼更具可讀性，也能很方便的重複使用

模組與項目預設都是私有的，私有的內容無法被外部所使用，但我們可以使用 `pub` 關鍵字使其變為公有，公有的內容可以被外部所使用。

寫一個 library crate 來定義一間餐廳所具備的功能。

餐廳分為前台 (front of house) 與後台 (back of house)。

- 前台具備客人、座位、一位接受訂單與付款的服務生與一位調酒師調製飲品。
- 後台具備廚師、製作出來的料理、洗碗機以及一位經理在管理。

一開始我們可以在 `src/lib.rs` 中這樣定義前台：

```rust
mod front_of_house {
    mod hosting {
        fn add_to_waitlist() {}

        fn seat_at_table() {}
    }

    mod serving {
        fn take_order() {}

        fn serve_order() {}

        fn take_payment() {}
    }
}
```

我們訂一個模組 `front_of_house`，模組中還可以包含模組，例如上述例子中的 `hosting` 與 `serving`。

也可以包含其他如 struct (結構體)、enum (列舉)、constants (常數)、traits 與上述例子中的各種 function (函式)。

藉由模組，我們可以將有相關性的定義放在一起，這樣開發者就可以根據名稱來尋找需要的定義，而不是辛苦地翻遍所有定義。

開發者再在加入的功能時，也能根據用途與名稱知道新功能應該放在哪裡，保持程式碼的可讀性。

`src/lib.rs` 與 `src/main.rs` 都被稱為 crate 根目錄，那是因為他們在 rust 的結構中，他們都屬於一個名為 crate 的模組。

```text
crate
 └── front_of_house
     ├── hosting
     │   ├── add_to_waitlist
     │   └── seat_at_table
     └── serving
         ├── take_order
         ├── serve_order
         └── take_payment
```

可以從中看出 hosting 與 serving 都屬於 front_of_house 模組。

如果一個模組 A 包含在模組 B 中，我們可以說 A 是 B 的子模組，而 B 是 A 的父模組。

需要注意的是，所有模組都被包含在一個隱性的 crate 模組中。

就如同你會在電腦上使用資料夾來整理的檔案，在 rust 中我們也可以使用模組來整理我們的程式碼。
