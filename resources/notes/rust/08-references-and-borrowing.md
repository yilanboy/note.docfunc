# 引用與借用 (References and Borrowing)

我們可以在函式的參數加上 `&`，允許函式使用變數的值但卻不給其所有權，`&` 這個符號就代表這裡使用引用 (references)。

```rust
fn main() {
    let s1 = String::from("hello");

    let len = calculate_length(&s1);

    println!("The length of '{}' is {}.", s1, len);
}

fn calculate_length(s: &String) -> usize {
    s.len()
}
```

我們將獲取引用做為函式的參數稱為借用 (borrowing)。

借用來的變數如同現實生活中跟人借用物品一般，借完就要還回去，因此借用來的變數在離開函式作用域之後並不會失效。

但借用來的變數默認是不能更動的，與變數預設為 immutable 相同。

```rust
fn main() {
    let s = String::from("hello");

    change(&s);
}

fn change(some_string: &String) {
    some_string.push_str(", world"); // error: use `&mut String` here to make mutable
}
```

我們可以透過在變數宣告時加上 `mut`，與在參數前面加上 `&mut` 讓引用可以更動。

```rust
fn main() {
    let mut s = String::from("hello");

    change(&mut s);
}

fn change(some_string: &mut String) {
    some_string.push_str(", world");
}
```

但這種可變引用有一個很大的限制，只要在作用域內使用過一次，就不能再次使用。

```rust
let mut s = String::from("hello");

let r1 = &mut s;
let r2 = &mut s;

println!("{}, {}", r1, r2); // cannot borrow `s` as mutable more than once at a time
```

這個限制的好處在於避免編譯時發生資料競爭 (data race)，這可能由三個行為造成：

- 兩個或更多指針同時訪問同一資料。
- 至少有一個指針被用來寫入資料。
- 沒有同步資料訪問的機制。

我們可以建立一個新的作用域，來允許擁有多個可變引用。

```rust
let mut s = String::from("hello");

{
    let r1 = &mut s;

} // r1 在這裡離開了作用域，所以我們可以完全建立一個新的引用

let r2 = &mut s;
```

類似的規則也存在於可變與不可變引用。

```rust
let mut s = String::from("hello");

let r1 = &s; // no problem
let r2 = &s; // no problem
let r3 = &mut s; // BIG PROBLEM

println!("{}, {}, and {}", r1, r2, r3);
```

多個不可變引用是允許的。

## 懸垂引用 (Dangling References)

在具有指針的語言中，如果釋放記憶體卻保留指向它的指針，就會產生一個懸垂指針 (dangling pointer)。

Rust 在編譯時就會檢查是否有懸垂引用的情況發生。

```rust
fn dangle() -> &String { // dangle 返回一個字符串的引用

    let s = String::from("hello"); // s 是一個新字符串

    &s // 返回字符串 s 的引用
} // 這裡 s 離開作用域並被丟棄。其記憶體被釋放。
```

## 引用的規則

概括對於引用的討論。

- 再任意給定的時間內，你只能擁有一個可變引用或是多個不可變引用。
- 引用必須永遠都是有效的。

## 查看變數記憶體位址

可以使用 `{:p}` 來查看變數的記憶體位址。

```rust
fn main() {
    let test_1: &str = "hello";
    let test_2 = test_1;

    println!("{} {}", test_1, test_2); // hello hello
    println!("{:p}", test_1); // 0x7f8b1a5a0a20
    println!("{:p}", test_2); // 0x7f8b1a5a0a20
}
```

## 參考資料

- [Temporary value is freed at the end of this statement [duplicate]](https://stackoverflow.com/questions/54056268/temporary-value-is-freed-at-the-end-of-this-statement)
- [Why is the memory address printed with {:p} much bigger than my RAM specs?](https://stackoverflow.com/questions/46683073/why-is-the-memory-address-printed-with-p-much-bigger-than-my-ram-specs)
