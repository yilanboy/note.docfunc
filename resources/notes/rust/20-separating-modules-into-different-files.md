# Separating Modules into Different Files

通常我們不會把所有程式碼都放在一個檔案中，而是使用模組的方式將程式碼拆散並放置到不同的檔案。

這麼做可以讓程式碼更易於管理，也能很清楚的找到每個模組與項目的定義處，尤其在專案越變越大之後，這個優點會更明顯。

讓我們修改之前 `front_of_house` 的範例，原本我們將程式碼都放在 `src/lib.rs` 中。

```rust
// src/lib.rs
mod front_of_house {
    pub mod hosting {
        pub fn add_to_waitlist() {}
    }
}

pub use crate::front_of_house::hosting;

pub fn eat_at_restaurant() {
    hosting::add_to_waitlist();
}
```

這次我們嘗試將其拆開並放到其對應的路徑上。

```rust
// src/lib.rs
mod front_of_house;

pub use crate::front_of_house::hosting;

pub fn eat_at_restaurant() {
    hosting::add_to_waitlist();
}
```

接下來建立一個 `src/front_of_house.rs` 檔案，將 `hosting` 模組放入。

```rust
// src/front_of_house.rs
pub mod hosting {
    pub fn add_to_waitlist() {}
}
```

因為 `hosting` 也是一個模組，我們可以再將其給拆分出來。

但在拆分之前，我們需要修改 `src/front_of_house.rs` 的內容。

```rust
// src/front_of_house.rs
pub mod hosting;
```

建立一個 `src/front_of_house/hosting.rs` 檔案，將 `add_to_waitlist` 函式放入。

```rust
// src/front_of_house/hosting.rs
pub fn add_to_waitlist() {}
```

> 注意 mod 並不像其他語言的 include，只要引入就能直接使用，所以還需要搭配 use 將模組引入作用域。

雖然之前提到，如果我們在 crate 根目錄中宣告一個模組 `mod front_of_house;`，那麼編譯器會去尋找兩種可能的路徑。

- `src/front_of_house.rs` : 我們剛剛範例使用的方式。

- `src/front_of_house/mod.rs` : 比較老舊的方式，但仍然支援。

如果 `front_of_house` 擁有子模組 `hosting`，路徑也是有兩種可能性。

- `src/front_of_house/hosting.rs` : 我們剛剛範例使用的方式。

- `src/front_of_house/hosting/mod.rs` : 比較老舊的方式，但仍然支援。

**同一個模組，這兩種方式不能同時使用**，不同的模組可以混搭使用。

**但不推薦使用老舊的方式**，原因在於如果專案足夠複雜，模組數量一多就會出現大量的 `mod.rs`，這在找尋程式碼時可能會造成困擾。
