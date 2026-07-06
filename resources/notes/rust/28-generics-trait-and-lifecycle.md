# Generic、Traits 與 Lifetimes

如果你有一段程式碼可以接收不同種類型的值，並使用相同的邏輯處理，那麼就可以考慮使用泛型。
泛型並不會給定一個固定類型，而是呼叫時在指定一個類型。
Rust 本身已經有內建一些泛型讓我們直接使用，例如 `Option<T>`、`Vce<T>`、`HashMap<K, V>` 與 `Result<T>`。

Traits 可以用來定義泛型行為的方法，traits 可以將泛型限制為擁有特定行為的類型，而非任意類型。

Lifetimes 允許我們向編譯器提供引用如何相互關聯的泛型。
Rust 的生命週期功能允許在很多場景下借用值的同時，仍然使編輯器能夠檢查這些引用的有效性。

## 提取函式來減少重複

假設我們有一個尋找最大值的函式。

```rust
fn main() {
    // 將列表存放在變數 number_list 中
    let number_list = vec![34, 50, 25, 100, 65];

    // 將列表的第一項存放在可變變數 largest 中
    let mut largest = &number_list[0];

    // 將 largest 與 number_list 中所有元素一一比對
    // 如果元素比 largest 大，就將元素的值放入 largest 中取代掉原本的值
    for number in &number_list {
        if number > largest {
            largest = number;
        }
    }

    println!("The largest number is {}", largest);
}
```

如果們需要多次找尋列表的最大值時，可以將上面的程式碼抽取出來，變成一個獨立的函式。

```rust
fn largest(list: &[i32]) -> &i32 {
    let mut largest = &list[0];

    for item in list {
        if item > largest {
            largest = item;
        }
    }

    largest
}

fn main() {
    let number_list = vec![34, 50, 25, 100, 65];

    let result = largest(&number_list);
    println!("The largest number is {}", result);

    let number_list = vec![102, 34, 6000, 89, 54, 2, 43, 8];

    let result = largest(&number_list);
    println!("The largest number is {}", result);
}
```

上述的修改是我們在重構程式碼時很常會遇到的狀況。

1. 找出重複程式碼。
2. 將重複的程式碼抽取出來，變成一個獨立的函式，並設定好參數與返回值。
3. 將重複程式碼的地方修改為呼叫函式。

下一步，我們將利用泛型來執行這些相同的步驟，以減少程式碼重複的情況。
就如同函式的內容可以操作抽象的列表，而非已經固定為數字的列表 (如上述的例子)，泛型可以讓程式碼對抽象型態進行操作。

## 泛型資料類型

假設我們有兩個函式，一個可以如同上面的範例，找出數字列表中的最大值。
而另外一個則是可以找出字元 (char) 列表中的最大值。

```rust
fn largest_i32(list: &[i32]) -> &i32 {
    let mut largest = &list[0];

    for item in list {
        if item > largest {
            largest = item;
        }
    }

    largest
}

fn largest_char(list: &[char]) -> &char {
    let mut largest = &list[0];

    for item in list {
        if item > largest {
            largest = item;
        }
    }

    largest
}

fn main() {
    let number_list = vec![34, 50, 25, 100, 65];

    let result = largest_i32(&number_list);
    println!("The largest number is {}", result);

    let char_list = vec!['y', 'm', 'a', 'q'];

    let result = largest_char(&char_list);
    println!("The largest char is {}", result);
}
```

這兩個函式的邏輯完全相同，如果想要避免重複撰寫一樣的邏輯，泛型也許是個可行的方法，嘗試使用泛型修改上面的程式碼。

```rust
fn largest<T>(list: &[T]) -> T {
    let mut largest = list[0];

    for &item in list.iter() {
        if item > largest {
            largest = item;
        }
    }

    largest
}

fn main() {
    let number_list = vec![34, 50, 25, 100, 65];

    let result = largest(&number_list);
    println!("The largest number is {}", result);

    let char_list = vec!['y', 'm', 'a', 'q'];

    let result = largest(&char_list);
    println!("The largest char is {}", result);
}
```

上面的程式碼執行後，會出現下面的錯誤。

```text
   Compiling playground v0.0.1 (/playground)
error[E0369]: binary operation `>` cannot be applied to type `&T`
 --> src/main.rs:5:17
  |
5 |         if item > largest {
  |            ---- ^ ------- &T
  |            |
  |            &T
  |
help: consider restricting type parameter `T`
  |
1 | fn largest<T: std::cmp::PartialOrd>(list: &[T]) -> &T {
  |             ++++++++++++++++++++++

For more information about this error, try `rustc --explain E0369`.
error: could not compile `playground` (bin "playground") due to previous error
```

說明表示函式中並不是所有的類型 `T` 都可以使用比較運算子 `>`。

此時我們可以根據建議，利用 trait 來限制 `T` 的類型，只有實現 `std::cmp::PartialOrd` 的類型才能夠使用比較運算。

```rust
fn largest<T: PartialOrd + Copy>(list: &[T]) -> T {
    let mut largest = list[0];

    for &item in list.iter() {
        if item > largest {
            largest = item;
        }
    }

    largest
}
```

這時我們可以根據錯誤提示，修改 `T` 的類型，或是明確指定 `T` 的類型為 `i32` 或是 `chat`。

## 結構體定義中的泛型

結構體也同樣可以使用泛型。

```rust
struct Point<T> {
    x: T,
    y: T,
}

fn main() {
    let integer = Point { x: 5, y: 10 };
    let float = Point { x: 1.0, y: 4.0 };
}
```

注意因為 `x` 與 `y` 都宣稱為類型 `T`，所以他們的類型必須一致，下面是錯誤的範例。

```rust
struct Point<T> {
    x: T,
    y: T,
}

fn main() {
    let wont_work = Point { x: 5, y: 4.0 };
}
```

如果 `x` 與 `y` 的類型不同，可以使用多個泛型參數。

```rust
struct Point<T, U> {
    x: T,
    y: U,
}

fn main() {
    let both_integer = Point { x: 5, y: 10 };
    let both_float = Point { x: 1.0, y: 4.0 };
    let integer_and_float = Point { x: 5, y: 4.0 };
}
```

## 列舉定義中的泛型

Rust 標準庫中提供的列舉 `Option` 類型，也屬於一種泛型的使用。

```rust
enum Option<T> {
    Some(T),
    None,
}
```

`Result` 也是一種泛型的使用，`Result` 有兩個成員，`Ok` 可以存放一個類型 `T`，而 `Err` 也可以存放一個類型 `E`。

因此 `Result` 成功可以返回任何一種類型，發生錯誤時也是。

```rust
enum Result<T, E> {
    Ok(T),
    Err(E),
}
```

當發現程式碼中有多個只有存放的值的類型有所不同的結構體或枚舉定義時，
你就應該像之前的函數定義中那樣引入泛型類型來減少重覆程式碼。

## 方法定義中的泛型

我們也可以在結構體上的方法使用泛型。

注意必須在 `impl` 後面聲明 `T`，這樣 Rust 就知道 `Point` 的尖括號是泛型，而不是特定類型。

```rust
struct Point<T> {
    x: T,
    y: T,
}

// 必須在 impl 後面聲明 T
impl<T> Point<T> {
    fn x(&self) -> &T {
        &self.x
    }
}

fn main() {
    let p = Point { x: 5, y: 10 };

    println!("p.x = {}", p.x());
}
```

當然也可以直接指定為特定類型。

```rust
struct Point<T> {
    x: T,
    y: T,
}

impl Point<f32> {
    fn distance_from_origin(&self) -> f32 {
        (self.x.powi(2) + self.y.powi(2)).sqrt()
    }
}
```

或是擁有多個參數的泛型。

```rust
struct Point<X1, Y1> {
    x: X1,
    y: Y1,
}

impl<X1, Y1> Point<X1, Y1> {
    fn mixup<X2, Y2>(self, other: Point<X2, Y2>) -> Point<X1, Y2> {
        Point {
            x: self.x,
            y: other.y,
        }
    }
}

fn main() {
    let p1 = Point { x: 5, y: 10.4 };
    let p2 = Point { x: "Hello", y: 'c' };

    let p3 = p1.mixup(p2);

    println!("p3.x = {}, p3.y = {}", p3.x, p3.y);
}
```

## 使用泛型會影響性能嗎？

當 Rust 編譯時候，它會進行單態化 (monomorphization) 來保證效率，
**單態化是一個通過填充編譯時使用的具體類型，將通用程式碼轉換為特定程式碼的過程**。

以下面的 `Option` 列舉為例。

```rust
let integer = Some(5);
let float = Some(5.0);
```

當 Rust 編譯時會讀取傳遞給 `Option` 的值，當他發現只有兩種類型的 `Option<T>`，一種為 `i32` 一種為 `f64`，
它會將 `Option<T>` 展開為 `Option_i32` 和 `Option_f64`，**接著將泛型定義替換成這兩個具體定義**。

```rust
// 看起來會像這樣
enum Option_i32 {
    Some(i32),
    None,
}

enum Option_f64 {
    Some(f64),
    None,
}

fn main() {
    let integer = Option_i32::Some(5);
    let float = Option_f64::Some(5.0);
}
```
