# Slice

假設我們要從一個字符串中取得第一個單字，單字間會有空白隔開，如果一個字符串中沒有任何空白，那麼這整個字符串就可以被視為一個單字。

首先寫一個函式找出空白的索引位置。

```rust
fn first_word(s: &String) -> usize {
    // 轉換為 byte 數組
    let bytes = s.as_bytes();

    // 使用 iter() 返回數組中的每一個元素
    // 使用 enumerate() 轉為元組
    // 使用 (i, &item) 對元組中的元素進行解構
    // &item 獲取元素的引用
    for (i, &item) in bytes.iter().enumerate() {
        if item == b' ' {
            // return 代表函式結束，並返回值
            // 如果只使用 i，代表是這個作用域結束並返回值，函式並不會結束
            return i;
        }
    }

    s.len()
}
```

接下來就可以根據空白的索引值來返回第一個單字。

但有趣的點在於如果我們嘗試在取得空白索引值之後將原本的字符串給清空，就會導致拿到的索引值也無法使用。

```rust
fn main() {
    let mut s = String::from("hello world");

    let first_word = first_word(&s);

    // 故意清空字符串
    s.clear();

    // first_word 雖然還是 5，但是字符串已經被清空，這個索引值也無法繼續使用
}
```

上述的語法完全可以執行，因為邏輯上並沒有問題。

上述的方法可以使用 slice 來簡化，也能避免上述的狀況。

**slice 語法可以用來切割字符串**。

```rust
let s = String::from("hello");

let slice = &s[0..2]; // he
let slice = &s[..2]; // 如果一開始的索引為 0，可以省略，所以這裡也是 he
let slice = &s[..] // 如果結束為最後一個索引也可以省略，這裡是 hello
```

改寫剛剛的 `first_word` 函式。

```rust
// 注意這裡的返回值是一個引用
fn first_word(s: &String) -> &str {
    let bytes = s.as_bytes();

    for (i, &item) in bytes.iter().enumerate() {
        if item == b' ' {
            return &s[0..i];
        }
    }

    &s[..]
}
```

這時候如果再次故意清空字符串，就會發生錯誤。

如早前提到過的規則，**如果擁有某值的不可變引用時，就不能再獲取該值的可變引用**。

```rust
fn main() {
    let mut s = String::from("hello world");

    let word = first_word(&s);

    s.clear(); // error : cannot borrow `s` as mutable because it is also borrowed as immutable

    println!("the first word is: {}", word);
}
```

## 字符串字面值就是 slice

```rust
let s = "hello world!"
```

這裡的 `s` 類型是 `&str`，它是一個指向二進制程式特定位置的 slice，所以是不可變的，`&str` 是一個不可變的引用。

因此剛剛 `first_word()` 的參數類型，其實可以修改成 `&str`。

```rust
fn first_word(s: &str) -> &str {}
```

## 其他類型的 slice

針對數組的部分也可以使用 slice。

```rust
let a = [1, 2, 3, 4, 5];

let slice = &a[1..3];

println!("{:?}", slice); // [2, 3]
```

## 總結

所有權、借用與 slice 讓 Rust 在編譯程式時確保記憶體安全。

Rust 提供與其他語言相同的方式來控制你使用的記憶體，但資料所有者在離開作用域後自動清除資料的功能，讓你不需要在程式碼中額外花心思對記憶體管理進行處理。
