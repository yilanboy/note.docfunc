# 方法語法

struct 可以定義方法，方法與函式類似，使用 `fn` 關鍵字，也擁有參數與回傳值。

> 與函式不同，方法的第一個參數總是 `self`，它代表調用該方法的 struct 實例。

## 定義方法

將之前的 `area` 函式改寫成 `Rectangle` struct 上的一個 `area` 方法。

```rust
#[derive(Debug)]
struct Rectangle {
    width: u32,
    height: u32,
}

impl Rectangle { // impl 為 implementation 的縮寫
    fn area(&self) -> u32 { // 注意這裡需要加上 & 來借用，因為我們不需要獲取 Rectangle 實例的所有權
        self.width * self.height
    }
}

fn main() {
    let rect1 = Rectangle { width: 30, height: 50 };

    println!(
        "The area of the rectangle is {} square pixels.",
        rect1.area()
    );
}
```

Rust 有一個叫自動引用與解引用 (automatic referencing and dereferencing) 的功能。

當使用 `object.something()` 時，Rust 會根據 `something()` 中 `self` 參數設定，自動為 object 加上 `&`、`&mut` 或 `*`。

```rust
#[derive(Debug,Copy,Clone)]
struct Point {
    x: f64,
    y: f64,
}

impl Point {
    // 因為 self 有加上 &，因此當我們呼叫 distance() 時，Rust 會自動為實例加上 &
    fn distance(&self, other: &Point) -> f64 {
        let x_squared = f64::powi(other.x - self.x, 2);
        let y_squared = f64::powi(other.y - self.y, 2);
        f64::sqrt(x_squared + y_squared)
    }
}

fn main() {
    let p1 = Point { x: 0.0, y: 0.0 };
    let p2 = Point { x: 5.0, y: 6.5 };

    // 下面這兩行的意思會是一樣的
    p1.distance(&p2);
    (&p1).distance(&p2);
}
```

## 帶有更多參數的方法

我們再加上一個可以用來判斷是否可以包含另外一個長方形的方法，如以下的範例。

```rust
fn main() {
    let rect1 = Rectangle { width: 30, height: 50 };
    let rect2 = Rectangle { width: 10, height: 40 };
    let rect3 = Rectangle { width: 60, height: 45 };

    println!("Can rect1 hold rect2? {}", rect1.can_hold(&rect2)); // true
    println!("Can rect1 hold rect3? {}", rect1.can_hold(&rect3)); // false
}
```

實作 `can_hold` 方法。

```rust
#[derive(Debug)]
struct Rectangle {
    width: u32,
    height: u32,
}

impl Rectangle {
    fn area(&self) -> u32 {
        self.width * self.height
    }

    fn can_hold(&self, other: &Rectangle) -> bool {
        self.width > other.width && self.height > other.height
    }
}
```

## 關聯函式

`impl` 還有另外一個功能，允許在其中定義一個不以 `self` 當作第一個參數的函式，也被稱為關聯函式。

關聯函式與 struct 相關聯，但並不是方法，**因為他們與 struct 的實例無關**，前面使用的 `String::from` 就是一種關聯函式。

> 感覺有點類似於 PHP 的靜態方法。即使沒有實例，也可以直接使用。

```rust
#[derive(Debug)]
struct Rectangle {
    width: u32,
    height: u32,
}

impl Rectangle {
    // 定義正方形
    fn square(size: u32) -> Rectangle {
        Rectangle { width: size, height: size }
    }
}
```

關聯函式使用 `::` 調用，例如下方的例子。

```rust
let sq = Rectangle::square(10);
```

## 多個 impl

struct 允許擁有多個 `impl`。

```rust
#[derive(Debug)]
struct Rectangle {
    width: u32,
    height: u32,
}

impl Rectangle {
    fn area(&self) -> u32 {
        self.width * self.height
    }
}

impl Rectangle {
    fn can_hold(&self, other: &Rectangle) -> bool {
        self.width > other.width && self.height > other.height
    }
}
```
