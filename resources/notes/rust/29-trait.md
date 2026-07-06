# Trait

Trait 可以為不同的類型定義一個共享的行為。

> trait 有點類似其他語言的 interface，但有些不同。

## 定義 Trait

一個類型由其可調用的方法組成。如果可以對不同類型調用相同的方法，這些類型就可以共享相同的行為了。

Trait 定義是一種將方法簽名組合起來的方法，目的是定義一個實現某些目的所必需的行為的集合。

假設我們有多個存放不同類型資料的結構 `NewsArticle` 與 `Tweet`，
`NewsArticle` 用來儲存世界各地的新聞，`Tweet` 用來儲存最多 280 字的推文。
我們希望為這兩個結構實現一個 `summarize` 方法，以便在需要時顯示摘要。

```rust
trait Summary {
    // 只定義了一個方法簽名，但沒有實際的方法內容
    fn summarize(&self) -> String;
}
```

## 為類型實現 Trait

為類型實現 Trait 的語法如下：

```rust
pub trait Summary {
    fn summarize(&self) -> String;
}

pub struct NewsArticle {
    pub headline: String,
    pub location: String,
    pub author: String,
    pub content: String,
}

impl Summary for NewsArticle {
    fn summarize(&self) -> String {
        format!("{}, by {} ({})", self.headline, self.author, self.location)
    }
}

pub struct Tweet {
    pub username: String,
    pub content: String,
    pub reply: bool,
    pub retweet: bool,
}

impl Summary for Tweet {
    fn summarize(&self) -> String {
        format!("{}: {}", self.username, self.content)
    }
}
```

一旦實作了 trait，在結構定義好之後就可以直接使用。

```rust
let tweet = Tweet {
    username: String::from("horse_ebooks"),
    content: String::from(
        "of course, as you probably already know, people",
    ),
    reply: false,
    retweet: false,
};

println!("1 new tweet: {}", tweet.summarize());
```

如果別人想使用我們定義的 `Summary` trait，要先引入我的們 trait，
這也是為什麼 trait 前面會有一個 `pub` 的原因。

```rust
// 使用 pub 讓其他模組可以引入
pub trait Summary {
    fn summarize(&self) -> String;
}
```

引入 trait 的語法如下：

```rust
use learning_rust::Summary;
```

需要注意的是，只有當 trait 或結構定義在同一個模組中時，才可以直接使用。
例如可以為自定義 `Tweet` 實作標準庫中的 `Display` trait，這是因為 `Tweet` 位於多媒體聚合庫 crate 的本地作用域中。
你也可以在多媒體聚合庫 crate 中為 `Vec` 實作 `Summary` trait，因為 `Summary` 位於多媒體聚合庫 crate 的本地作用域中。

但不能為外部類型實現外部 trait，例如不能在多媒體聚合庫中為 `Vec` 實作 `Display` trait，因為 `Display` 和 `Vec` 都位於標準庫中，而不是多媒體聚合庫的本地作用域中。

這個限制被稱為相干性 (coherence) 或孤立性 (orphan rule)，其主要目的是避免衝突，因為如果允許外部實作外部 trait，就可能會有多個相同的實作，而編譯器無法判斷要使用哪一個。

## 默認實作

Trait 可以提供默認實作，這樣實作該 trait 的類型就不需要實作該方法了。

```rust
pub trait Summary {
    fn summarize(&self) -> String {
        String::from("(Read more...)")
    }
}
```

如果實作了默認實作，那麼實作該 trait 的類型就可以直接使用該方法了。

```rust
impl Summary for Tweet {}

let tweet = Tweet {
    username: String::from("horse_ebooks"),
    content: String::from(
        "of course, as you probably already know, people",
    ),
    reply: false,
    retweet: false,
};

println!("1 new tweet: {}", tweet.summarize());
```

Trait 可以有多個方法，也可以部分有默認實作，部分沒有默認實作。

```rust
pub trait Summary {
    fn summarize_author(&self) -> String;

    fn summarize(&self) -> String {
        format!("(Read more from {}...)", self.summarize_author())
    }
}
```

如果想要使用這個版本的 `Summary` trait，只需要實作 `summarize_author` 方法即可。

```rust
impl Summary for Tweet {
    fn summarize_author(&self) -> String {
        format!("@{}", self.username)
    }
}
```

## 用 Trait 來限制參數類型

Trait 可以用來限制函數參數的類型。

```rust
// 在 notify 函數中，item 參數的類型是實作了 Summary trait 的類型
pub fn notify(item: impl Summary) {
    println!("Breaking news! {}", item.summarize());
}
```

### Trait Bound 語法

```rust
pub fn notify<T: Summary>(item: T) {
    println!("Breaking news! {}", item.summarize());
}
```

較為複雜的場景，可以考慮使用 trait bound 語法，例如多個參數

```rust
pub fn notify(item1: impl Summary, item2: impl Summary) {}
```

可以改寫成

```rust
pub fn notify<T: Summary>(item1: T, item2: T) {}
```

### 通過 + 指定多個 Trait

```rust
pub fn notify(item: impl Summary + Display) {}
```

這個寫法的意思是，`item` 參數必須實作 `Summary` 和 `Display` 這兩個 trait。

Trait bound 語法也可以這樣寫：

```rust
pub fn notify<T: Summary + Display>(item: T) {}
```

### 通過 where 簡化 Trait Bound

使用過多的 trait bound 也有缺點，如下所示，這樣寫起來會很冗長，難以閱讀。

```rust
fn some_function<T: Display + Clone, U: Clone + Debug>(t: T, u: U) -> i32 {}
```

可以透過 `where` 改寫成

```rust
fn some_function<T, U>(t: T, u: U) -> i32
    where T: Display + Clone,
          U: Clone + Debug
{}
```

### 返回實作了 Trait 的類型

```rust
fn returns_summarizable() -> impl Summary {
    Tweet {
        username: String::from("horse_ebooks"),
        content: String::from(
            "of course, as you probably already know, people",
        ),
        reply: false,
        retweet: false,
    }
}
```

## 使用 Trait Bound 有條件的實現方法

通過 trait bound，可以有條件的只為實作了某個 trait 的類型實現方法。

```rust
use std::fmt::Display;

struct Pair {
    x: T,
    y: T,
}

impl Pair {
    fn new(x: T, y: T) -> Self {
        Self {
            x,
            y,
        }
    }
}

// 只為實作了 Display 和 PartialOrd trait 的類型實現 cmp_display 方法
impl<T: Display + PartialOrd> Pair {
    fn cmp_display(&self) {
        if self.x >= self.y {
            println!("The largest member is x = {}", self.x);
        } else {
            println!("The largest member is y = {}", self.y);
        }
    }
}
```

也可以對任何實現特定 trait 的類型有條件的實作 trait。例如標準庫為任何實現了 `Display` trait 的類型實作了 `ToString` trait。

> 這也被稱為 blanket implementations

```rust
impl<T: Display> ToString for T {
    // --snip--
}
```
