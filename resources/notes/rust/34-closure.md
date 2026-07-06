# Closures

Rust 的閉包（closures）是個你能賦值給變數或作為其他函式引數的匿名函式。

與函式不同的是，閉包可以從它們所定義的作用域中獲取數值。

```rust
// 一個簡單的閉包範例
fn main () {
    let mut number_list = vec![1, 2, 3];

    // 閉包內可以直接獲取定義內作用域的變數
    // 這裡的 x 是閉包的參數
    // 如果獲取了閉包外的可變變數，則閉包需要加上 mut 關鍵字
    let mut append = |x: i32| {
        number_list.push(x);
    };

    append(4);

    assert_eq!(number_list, vec![1, 2, 3, 4])
}
```

## 閉包型別推導與詮釋

閉包通常不必像 `fn` 函式那樣要求你要詮釋參數或回傳值的型別。但為了可讀性，通常還是會詮釋型別。

```rust
// 函式語法與閉包語法的比較，仔細看其實相當類似
fn  add_one_v1   (x: u32) -> u32 { x + 1 }
let add_one_v2 = |x: u32| -> u32 { x + 1 };
let add_one_v3 = |x|             { x + 1 };
let add_one_v4 = |x|               x + 1  ;
```

閉包的定義，編譯器會對每個參數與它們的回傳值推導出一個實際型別。

```rust
fn main() {
    let example_closure = |x| x;

    // 編譯器會推導出 example_closure 的型別為 String -> String
    let s = example_closure(String::from("哈囉"));
    // error: x 已被推導為 String，因此傳入 i32 的 5 會發生型別錯誤
    let n = example_closure(5);
}

```

## 獲取參考或移動所有權

閉包要從它們周圍環境取得數值有三種方式，這能直接對應於函式取得參數的三種方式：

- 不可變借用
- 可變借用
- 取得所有權

當閉包取得周圍環境的可變數值時，閉包必須加上 `mut`。

```rust
fn main() {
    let mut list = vec![1, 2, 3];
    println!("定義閉包前：{:?}", list);

    // 當 borrows_mutably 定義時，它會獲取 list 的可變參考
    let mut borrows_mutably = || list.push(7);

    // error: 在閉包定義與呼叫之間，利用不可變參考印出輸出是不允許的，因為在可變參考期間不能再有其他參考
    println!("呼叫閉包前：{:?}", list);
    // 呼叫閉包，可變參考就結束了
    borrows_mutably();
    println!("呼叫閉包後：{:?}", list);
}
```

如果你想要強迫閉包取得周圍環境數值的所有權的話，你可以在參數列表前使用 `move` 關鍵字。

```rust
use std::thread;

fn main() {
    let list = vec![1, 2, 3];
    println!("呼叫閉包前：{:?}", list);

    thread::spawn(move || println!("來自執行緒：{:?}", list))
        .join()
        .unwrap();
}
```

## Fn 特徵以及將獲取的數值移出閉包

閉包從周圍環境獲取數值後，其處理方式會影響閉包實作哪種 `Fn` 特徵。

- `FnOnce`：會將獲取的數值移出本體的閉包
- `FnMut`：適用於不會將獲取數值移出本體，而且可能會改變獲取數值的閉包。這種閉包可以被呼叫多次。
- `Fn`：適用於不會將獲取數值移出本體，而且不會改變獲取數值或是甚至不從環境獲取數值的閉包。這種閉包可以被呼叫多次。

以 `Option<T>` 的 `unwrap_or_else` 方法定義為例：

```rust
impl<T> Option<T> {
    pub fn unwrap_or_else<F>(self, f: F) -> T
    where
        F: FnOnce() -> T
    {
        match self {
            Some(x) => x,
            None => f(),
        }
    }
}
```

泛型型別 `F` 指定的特徵界限是 `FnOnce() -> T`，也就是說 `F` 必須要能夠呼叫一次、不帶任何引數然後回傳 `T`。

如果 `Option` 是 `Some` 的話，`f` 就不會被呼叫。如果 `Option` 是 `None` 的話，`f` 就會被呼叫一次。

標準函式庫中切片定義的 sort_by_key 方法，也是使用 `Fn` 特徵，但它是使用 `FnMut` 特徵。

```rust
#[derive(Debug)]
struct Rectangle {
    width: u32,
    height: u32,
}

fn main() {
    let mut list = [
        Rectangle { width: 10, height: 1 },
        Rectangle { width: 3, height: 5 },
        Rectangle { width: 7, height: 12 },
    ];

    let mut num_sort_operations = 0;

    // 使用 sort_by_key 重新排列 list，並傳入一個閉包計算總共執行了幾次排序
    // 這裡的閉包是 FnMut 特徵，因為它會改變獲取的數值，並且會被呼叫多次
    list.sort_by_key(|r| {
        num_sort_operations += 1;
        r.width
    });

    println!("{:#?} 的排序經過 {num_sort_operations} 次運算", list);
}
```
