# Paths for Referring to an Item in the Module Tree

要使用一個模組 (Module) 中的項目 (Item)，需要先知道它的路徑 (Path)。

路徑有兩種：

- 絕對路徑 : 從 crate 根目錄開始，路徑會從 `crate` 開始。
- 相對路徑 : 從當前的模組開始，會使用 `self` 或是 `super`，或是當前模組的標識符當開頭。

絕對路徑和相對路徑都會有一個或多個由雙冒號（`::`）分割的標識符。

以之前所提到的 `add_to_waitlist` 函式當例子，如果我們想使用這個函式，就需要知道他的路徑，以下示範絕對路徑與相對路徑的表示方式：

```rust
mod front_of_house {
    mod hosting {
        fn add_to_waitlist() {}
    }
}

pub fn eat_at_restaurant() {
    // Absolute path
    crate::front_of_house::hosting::add_to_waitlist();

    // Relative path
    front_of_house::hosting::add_to_waitlist();
}
```

哪一種比較好？

如果你將 `front_of_house` 與 `eat_at_restaurant` 一起搬到 `customer_experience` 模組底下，那麼相對路徑不需要變更，絕對路徑需要更動。

如果你將 `eat_at_restaurant` 移動到 `dining` 模組底下，則絕對路徑不需要更動，相對路徑需要變更。

一般來說，**我們傾向於指定絕對路徑**，因為我們更有可能想獨立地移動程式碼和項目調用。

如果編譯上述的程式碼，會發生以下的錯誤。

```text
   Compiling restaurant v0.1.0 (file:///projects/restaurant)
error[E0603]: module `hosting` is private
 --> src/lib.rs:9:28
  |
9 |     crate::front_of_house::hosting::add_to_waitlist();
  |                            ^^^^^^^ private module
  |
note: the module `hosting` is defined here
 --> src/lib.rs:2:5
  |
2 |     mod hosting {
  |     ^^^^^^^^^^^

error[E0603]: module `hosting` is private
  --> src/lib.rs:12:21
   |
12 |     front_of_house::hosting::add_to_waitlist();
   |                     ^^^^^^^ private module
   |
note: the module `hosting` is defined here
  --> src/lib.rs:2:5
   |
2  |     mod hosting {
   |     ^^^^^^^^^^^

For more information about this error, try `rustc --explain E0603`.
error: could not compile `restaurant` due to 2 previous errors
```

在 Rust 中，所有項目 (function、method、struct、enums、 modules 與 constant) 對於**父模組來說都是私有的**
，如果你想讓一個項目變成私有，可以將其放入模組中。

> 歸納一個重點，**父模組不能夠使用子模組的私有項目，但子模組可以使用父模組的私有項目**
>
> This is because child modules wrap and hide their implementation details,
> but the child modules can see the context in which they’re defined.
>
> 因為子模組將它們的實作細節包裹並隱藏了起來，但子模組可以看到它們被定義的背景。

雖然模組預設為私有，但我們可以使用 `pub` 關鍵字使項目變成公有。

以下方的程式碼為例，我們可以幫 `hosting` 加上 `pub` 使其變成公有。

但這段程式碼編譯時，仍然會產生錯誤，因為 `add_to_waitlist` 仍舊是私有的，我們無法從外部對其進行呼叫。

```rust
mod front_of_house {
    pub mod hosting {
        fn add_to_waitlist() {} // error: function add_to_waitlist is private
    }
}

pub fn eat_at_restaurant() {
    // Absolute path
    crate::front_of_house::hosting::add_to_waitlist();

    // Relative path
    front_of_house::hosting::add_to_waitlist();
}
```

> 因此需要注意的是，**父模組為公有的，不代表其子模組也是公有的，模組是公有的，不代表內部的項目也是公有的**。

我們必須幫 `add_to_waitlist` 加上 `pub`，程式碼才能使程式碼成功編譯。

```rust
mod front_of_house {
    pub mod hosting {
        pub fn add_to_waitlist() {}
    }
}

pub fn eat_at_restaurant() {
    // Absolute path
    crate::front_of_house::hosting::add_to_waitlist();

    // Relative path
    front_of_house::hosting::add_to_waitlist();
}
```

雖然 `front_of_house` 沒有加上 `pub`，但因為與 `eat_at_restaurant` 處在同一個模組下，所以可以直接使用。

## 使用 super 開始相對路徑

也可以使用 `super` 開頭來構建相對路徑。這麽做類似於文件系統中以 `..` 開頭。

```rust
fn deliver_order() {}

mod back_of_house {
    fn fix_incorrect_order() {
        cook_order();

        // super 的相對路徑寫法
        super::deliver_order();
    }

    fn cook_order() {}
}
```

## 讓 Structs 與 Enums 公有化

Struct 也可以加上 `pub`，使其公有化，但要注意的是，其內容不會變成公有的。

> Struct 的內容也可以加上 `pub`，使其變成公有的。

```rust
mod back_of_house {
    pub struct Breakfast {
        pub toast: String,
        seasonal_fruit: String,
    }

    impl Breakfast {
        pub fn summer(toast: &str) -> Breakfast {
            Breakfast {
                toast: String::from(toast),
                seasonal_fruit: String::from("peaches"),
            }
        }
    }
}

pub fn eat_at_restaurant() {
    // Order a breakfast in the summer with Rye toast
    let mut meal = back_of_house::Breakfast::summer("Rye");
    // Change our mind about what bread we'd like
    meal.toast = String::from("Wheat");
    println!("I'd like {} toast please", meal.toast);

    // The next line won't compile if we uncomment it; we're not allowed
    // to see or modify the seasonal fruit that comes with the meal
    // meal.seasonal_fruit = String::from("blueberries");
}
```

以上述的程式碼為例，`Breakfast` 設定為公有，但其中的 `seasonal_fruit` 仍舊為私有的。

而 `Breakfast` 中的 `toast` 與 `summer` 也需要特別加上 `pub` 才能使其變為公有。

Enum 則不一樣，只要一宣告為公有的，其內容也通通都是公有的。

```rust
mod back_of_house {
    pub enum Appetizer {
        Soup,
        Salad,
    }
}

pub fn eat_at_restaurant() {
    let order1 = back_of_house::Appetizer::Soup;
    let order2 = back_of_house::Appetizer::Salad;
}
```
