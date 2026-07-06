# Vector

可以用 vector 儲存多於一個的值，它們會在記憶體中彼此相鄰排列。

請注意，**vector 只能儲存相同類型的值**。

可以使用 `Vec::new()` 建立一個新的空 vector。

```rust
// 因為是空 vector，所以我們必須告訴 Rust 我們要放入什麼類型的值
// 這裡使用類型註解 <i32> 告訴 Rust，這個 vector 會放入 i32 類型的值
let v: Vec<i32> = Vec::new();
```

如果一開始有給定初始值，就不用使用類型註解。

```rust
// Rust 可以自行推導出類型，因此不需要類型註解
let v = vec![1, 2, 3];
```

## 更新 Vector

可以使用 `push()` 增加 vector 的元素。

```rust
let mut v = Vec::new();

v.push(5);
v.push(6);
v.push(7);
```

## 移除 Vector，其中的元素也會被移除

```rust
{
    let v = vec![1, 2, 3, 4];

    // 處理變數 v

} // <- 這裡 v 離開作用域並被移除
```

當 vector 離開作用域被移除時，當中的元素也會被清理，雖然感覺很直觀，但某些情況下可能會讓你覺得有點複雜，例如下方的例子。

## 讀取 Vector 中的元素

有兩種方法可以讀取 vector 中的值。

```rust
let v = vec![1, 2, 3, 4, 5];

// 使用 & 與 [] 取得 vector 中元素的引用
// 元素的索引是從 0 開始
let third: &i32 = &v[2];
println!("The third element is {}", third);

// 使用 get() 返回一個 Option<&T>
match v.get(2) {
    Some(third) => println!("The third element is {}", third),
    None => println!("There is no third element."),
}
```

Rust 之所以有兩種引用元素的方法，是因為 Rust 想讓我們可以選擇如何適當的處理當索引值在 vector 中沒有對應值的情況。

我們使用這兩種方法來訪問一個不存在的元素。

```rust
let v = vec![1, 2, 3, 4, 5];

let does_not_exist = &v[100];
let does_not_exist = v.get(100);
```

使用 `[]` 訪問不存在的元素時，會造成 panic 並使程式崩潰，當你認為訪問不存在的元素是一個嚴重的錯誤且應該中斷程式執行時，可以使用此方式。

使用 `get()` 訪問不存在的元素時，不會造成 panic，而是返回一個 `None`，當你的程式認為偶爾超過 vector 的範圍屬於正常情況，可以考慮使用此方式。

例如當用戶不小心輸入一個過大的值，你可以處理返回 `None` 的情況，並告訴用戶輸入的值過大，使用者體驗會比程式崩潰好得多。

一旦獲取 vector 元素的引用，Rust 的借用檢查器會確保所有元素皆可被引用，但要注意相同的作用域中不能同時存在可變和不可變引用。

當我們獲取 vector 第一個元素的不可變元素時，就不能在 vector 末端再增加一個元素。

```rust
let mut v = vec![1, 2, 3, 4, 5];

let first = &v[0];

v.push(6);

println!("The first element is: {}", first);
```

錯誤訊息如下。

```text
error[E0502]: cannot borrow `v` as mutable because it is also borrowed as immutable
  --> src/main.rs:10:5
   |
8  |     let first = &v[0];
   |                  - immutable borrow occurs here
9  |
10 |     v.push(6);
   |     ^^^^^^^^^ mutable borrow occurs here
11 |
12 |     println!("The first element is: {}", first);
   |                                          ----- borrow later used here
```

不能這麼做的原因在於 vector 的工作方式，在 vector 新增元素時，如果沒有足夠空間將所有元素依次相鄰存放，
Rust 可能會要求重新分配記憶體，並將老的元素拷貝到新的空間中。

這時，第一個元素的引用就被指向了被釋放的記憶體，Rust 借用規則會阻止這種情況的發生。

## 遍歷 Vector 中的元素

使用 `for` 循環來獲取 vector 中每一個元素的的不可變引用。

```rust
let v = vec![100, 32, 57];

for i in &v {
    println!("{}", i);
}
```

我們也可以獲取每一個元素的可變引用，以便改變 vector 的內容。

```rust
let mut v = vec![100, 32, 57];

for i in &mut v {
    // 在修改可變引用所指向的值時，在使用 += 運算符號之前必須先使用解引用運算符號 * 獲取 i 的值
    *i += 50;
}
```

## 使用列舉來儲存多種類型的值

Vector 只能儲存相同類型的值，但我們可以使用列舉來讓 vector 儲存多種類型的值。

```rust
enum SpreadsheetCell {
    Int(i32),
    Float(f64),
    Text(String),
}

let row = vec![
    SpreadsheetCell::Int(3),
    SpreadsheetCell::Text(String::from("blue")),
    SpreadsheetCell::Float(10.12),
];
```
