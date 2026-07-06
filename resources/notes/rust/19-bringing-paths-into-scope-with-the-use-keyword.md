# Bringing Paths into Scope with the use Keyword

雖然我們可以使用絕對路徑來呼叫項目，但如果每一次呼叫都寫一長串路徑，會讓程式碼顯得有些冗長。

我們可以使用 `use` 關鍵字將模組引入作用域，之後若想使用模組，就不需要寫完整的路徑。

如下方的範例，如果想使用 `add_to_waitlist`，我們可以使用 `use` 先將 `hosting` 模組引入作用域中。

```rust
mod front_of_house {
    pub mod hosting {
        pub fn add_to_waitlist() {}
    }
}

// 使用 use 將 hosting 模組帶入作用域
use crate::front_of_house::hosting;

pub fn eat_at_restaurant() {
    // 這時候想使用 add_to_waitlist 就不需要大費周章再寫一次完整的路徑
    hosting::add_to_waitlist();
}
```

需要注意的是，`use` 只會引入當前放置的作用域。

在下方的例子中，如果我們將 `eat_at_restaurant` 移動到 `custom` 模組下，
就會因為作用域不相同，導致在呼叫 `add_to_waitlist` 時發生編譯錯誤。

```rust
mod front_of_house {
    pub mod hosting {
        pub fn add_to_waitlist() {}
    }
}

// 如果 use 之後沒有使用也會產生編譯錯誤
// warning: unused import: `crate::front_of_house::hosting`
use crate::front_of_house::hosting;

mod customer {
    // 這裡的作用域與 use crate::front_of_house::hosting; 並不相同
    pub fn eat_at_restaurant() {
        // error: failed to resolve: use of undeclared crate or module `hosting`
        hosting::add_to_waitlist();
    }
}
```

雖然我們可以使用完整路徑將函式引入，不過**引入模組是比較推崇的做法**，因為我們可以清楚的知道該函式是定義在哪個模組之下。

```rust
mod front_of_house {
    pub mod hosting {
        pub fn add_to_waitlist() {}
    }
}

// 雖然可以直接引入函式
use crate::front_of_house::hosting::add_to_waitlist;

pub fn eat_at_restaurant() {
    // 但我們會不清楚這個函式是定義在哪個模組之下
    add_to_waitlist();
}
```

另一方面，**如果是 structs、enums 與其它項目，比較推崇的做法是引入完整路徑**。

例如下方使用 Rust 標準函式庫 `HashMap` struct 的例子。

```rust
use std::collections::HashMap;

fn main() {
    let mut map = HashMap::new();
    map.insert(1, 2);
}
```

這個做法並沒有很強硬的理由，比較像是約定成俗的共識。

## 引入相同名稱的項目

雖然剛剛說項目類型在引用時會盡量使用完整路徑，但有個例外。

如果你想引入兩個名稱一模一樣的項目，引入時都寫完整名稱就會發生編譯錯誤。

因為後續項目的使用，編譯器無法知道你想使用哪一個項目 (即使是人也看不太出來)。

```rust
use std::fmt::Result;
use std::io::Result;

// 這是哪一個 Result？
fn function1() -> Result {
    // --snip--
    Ok(())
}
```

這時候可以引入上一層，也就是項目的模組名稱，來避免編譯錯誤。

```rust
// error
// use std::fmt::Result;
// use std::io::Result;

use std::fmt;
use std::io;

fn function1() -> fmt::Result {
    // --snip--
    Ok(())
}

fn function2() -> io::Result<()> {
    // --snip--
    Ok(())
}
```

除了上述這種方式，Rust 還提供一種使用別名 `as` 的方式來處理引入相同名稱項目的問題。

```rust
use std::fmt::Result;
use std::io::Result as IoResult;

fn function1() -> Result {
    // --snip--
    Ok(())
}

fn function2() -> IoResult<()> {
    // --snip--
    Ok(())
}
```

這兩種方式沒有誰好誰壞，端看開發者的選擇。

## 通過 pub use 重導出 (Re-exporting) 名稱

當使用 `use` 將名稱導入作用域時，在新的作用域中會是私有的。

如果你希望導入的名稱在新的作用域是公有的，可以使用 `pub use`。

```rust
mod sound {
    pub mod instrument {
        pub fn clarinet() {
            // ...
        }
    }
}

mod performance_group {
    // 如果沒有使用 pub use，那麼這裡 performance_group::instrument 就會是私有的
    pub use crate::sound::instrument;

    pub fn clarinet_trio() {
        instrument::clarinet();
        instrument::clarinet();
        instrument::clarinet();
    }
}

fn main() {
    performance_group::clarinet_trio();
    // 使用 pub use 讓引入的項目變為公有，使外部可以接續使用
    performance_group::instrument::clarinet();
}
```

## 使用外部套件

我們可以使用 `rand` 這個外部套件來產生一組隨機數。

如果想使用 `rand` 這個外部套件，我們需要在 `cargo.toml` 加上一行。

```toml
rand = "0.8.5"
```

此時 Cargo 就會知道我們的專案相依於 `rand` 這個外部套件，並幫我們下載這個套件。

接下來，如果想在程式碼中使用這個套件，還需要使用 `use` 將這個套件引入作用域中。

```rust
use rand::Rng;

fn main() {
    let secret_number = rand::thread_rng().gen_range(1..=100);

    // ...
}
```

Rust 社群開發了許多實用的套件並放在 [crates.io](https://crates.io/) 上。

Rust 標準函式庫中的 `std` 雖然也被視為外部套件，但因為已經包含在 Rust 中，所以我們不需要在 `cargo.toml` 中額外列出並下載。

## 使用嵌套路徑整理大量的 use 列表

如果我們需要使用很多外部套件，可能會有一整排多到數不清的 `use` 出現在你的程式碼中。

```rust
// ...
use std::cmp::Ordering;
use std::io;
```

這時我們可以使用嵌套路徑的方式，將有著相同前綴 (prefix) 底下的名稱整理在一起。

```rust
use std::{cmp::Ordering, io};
```

另外一個範例。

```rust
// before
use std::io;
use std::io::Write;

// after
use std::io::{self, Write};
```

## Glob 運算符

如果我們想要將一個名稱底下所有公有項目引入作用域中，可以使用 `*`。

```rust
use std::collections::*;
```

這會將 `std::collections` 底下所有公有項目引入當前的作用域中。

但要注意的是，使用 `glob` 會讓開發者難以得知引入的項目是定義在何處。

通常使用 `glob` 是在撰寫測試時，我們會將 `tests` 模組所有方法都引入作用域中，方便撰寫測試。
