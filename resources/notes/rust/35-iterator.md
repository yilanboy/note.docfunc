# 疊代器

疊代器（Iterator）模式讓你可以對一個項目序列依序進行某些任務。

疊代器本身是惰性的，除非呼叫方法來使用疊代器，否則疊代器不會有任何動作。

```rust
let v1 = vec![1, 2, 3];

// iter() 會回傳一個疊代器，但除非呼叫方法來使用疊代器，否則疊代器不會有任何動作
let v1_iter = v1.iter();

// 利用 for 迴圈來使用疊代器
for val in v1_iter {
    println!("Got: {}", val);
}

// 疊代器並不會取得 v1 的所有權，所以 v1 仍然可以使用
println!("{:?}", v1);
```

## Iterator 特徵與 next 方法

所有的疊代器都會實作定義在標準函式庫的 `Iterator` 特徵。特徵的定義如以下所示：

```rust
pub trait Iterator {
    type Item;

    fn next(&mut self) -> Option<Self::Item>;

    // 以下省略預設實作
}
```

`next` 方法會用 `Some` 依序回傳疊代器中的每個項目，並在疊代器結束時回傳 `None`。

你也可以直接呼叫 `next`：

```rust
#[test]
fn iterator_demonstration() {
    let v1 = vec![1, 2, 3];

    let mut v1_iter = v1.iter();

    assert_eq!(v1_iter.next(), Some(&1));
    assert_eq!(v1_iter.next(), Some(&2));
    assert_eq!(v1_iter.next(), Some(&3));
    assert_eq!(v1_iter.next(), None);
}
```

注意到這裡 `v1_iter` 需要是可變的：在疊代器上呼叫 `next` 方法**會改變疊代器內部用來紀錄序列位置的狀態**。

> 剛剛利用 `for` 來使用疊代器的例子，為什麼 `v1_iter` 不需要先聲明為可變？
>
> 是因為 `for` 迴圈會取得 `v1_iter` 的所有權並在內部將其改為可變。

另外還要注意的是我們從 `next` 呼叫取得的是向量中數值的不可變參考。

如果我們想要一個取得 `v1` 所有權的疊代器，我們可以呼叫 `into_iter`。

如果我們想要遍歷可變參考，我們可以呼叫 `iter_mut`。

## 消耗疊代器的方法

如果再疊代器上使用 `sum` 方法，疊代器會被消耗，並回傳疊代器所產生的值的總和：

```rust
#[test]
fn iterator_sum() {
    let v1 = vec![1, 2, 3];

    let v1_iter = v1.iter();

    // 疊代器會被消耗，後續無法再使用 v1_iter
    let total: i32 = v1_iter.sum();

    assert_eq!(total, 6);
}
```

## 產生其他疊代器的方法

疊代配接器（iterator adaptors）是定義在 `Iterator` 特徵的方法，它們不會消耗掉疊代器。它們會改變原本疊代器的一些屬性來產生不同的疊代器。

```rust
fn main() {
    let v1: Vec<i32> = vec![1, 2, 3];

    // 使用 map 將原本疊代器中的每個值加 1，並產生一個新的疊代器
    // 疊代器是惰性的，所以必須使用 collect 來消耗疊代器並產生一個向量
    let v2: Vec<_> = v1.iter().map(|x| x + 1).collect();

    assert_eq!(v2, vec![2, 3, 4]);
}
```

> 你可以透過疊代配接器串連多重呼叫，在進行一連串複雜運算的同時，仍保持良好的閱讀性。
>
> 但因為所有的疊代器都是惰性的，你必須呼叫能消耗配接器的方法來取得疊代配接器的結果。

## 使用閉包獲取它們的環境

許多疊代配接器都會拿閉包作為引數，因此可以直接獲取環境的數值。

```rust
#[derive(PartialEq, Debug)]
struct Shoe {
    size: u32,
    style: String,
}

// 根據要求的尺寸來過濾鞋子
fn shoes_in_size(shoes: Vec<Shoe>, shoe_size: u32) -> Vec<Shoe> {
    // filter 引數是一個閉包，可以直接獲取環境數值 shoe_size
    shoes.into_iter().filter(|s| s.size == shoe_size).collect()
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn filters_by_size() {
        let shoes = vec![
            Shoe {
                size: 10,
                style: String::from("運動鞋"),
            },
            Shoe {
                size: 13,
                style: String::from("涼鞋"),
            },
            Shoe {
                size: 10,
                style: String::from("靴子"),
            },
        ];

        // 鞋子的尺寸必須剛好是 10
        let in_my_size = shoes_in_size(shoes, 10);

        assert_eq!(
            in_my_size,
            vec![
                Shoe {
                    size: 10,
                    style: String::from("運動鞋")
                },
                Shoe {
                    size: 10,
                    style: String::from("靴子")
                },
            ]
        );
    }
}
```
