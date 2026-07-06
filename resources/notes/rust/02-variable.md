# 變數

## 變數與常量

Rust 的變數默認為不可變 (immutable)。

```rust
let x = 10;
x = 5; // cannot assign twice to immutable variable
```

想使變數可變可以加上 `mut` 關鍵字。

```rust
let mut x = 10;
x = 5;
```

可以使用 `const` 聲明常量，常量不允許改變。

在宣告的作用域中，常量在整個程式的生命週期都有效，因此常用來做全局範圍的固定值。

```rust
const MAX_POINT: u32 = 100_000;
```

## 隱藏 (Shadowing)

可以使用 `let` 重複宣告同一個變數，此時第一個變數會被**隱藏**。

與 `mut` 不同的地方是，隱藏其實是建立一個新的變數，只是變數名稱與別人相同而已，因此類型上也可以是全新的。

隱藏的好處在於對於意思類似但類型不同的變數，我們可以不用特地再去想一個新的變數名稱。

```rust
let spaces = "    ";
let spaces = spaces.len()
```

如果改用可變變數就會有問題，因為第二次賦值時，變數類型並不一樣。

```rust
let mut spaces = "    ";
spaces = spaces.len(); // expected type `&str` found type `usize`
```
