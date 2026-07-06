# struct (結構體)

struct 或者稱 structure，是一個自定義的資料類型，允許你命名和包裝多個相關的值，形成一個有意義的組合。

struct 可以定義方法與關連函式來指定與 struct 資料相關的行為。

在程式碼中基於 struct 與枚舉 (enum) 創建新類型，可以充分利用 Rust 的編譯時檢查。

## 定義結構體

和元組一樣，**struct 中的每一個部分都可以是不同的類型，但需要明確定義**。

```rust
struct User {
    username: String,
    email: String,
    sign_in_count: u64,
    active: bool,
}
```

struct 定義好之後就可以創建實例。

```rust
let user1 = User {
    email: String::from("someone@example.com"),
    username: String::from("someone"),
    active: true,
    sign_in_count: 1,
}
```

如果要取得實例中的某個值，可以使用 `.`，例如 `user1.email`。如果想要修改實例中的某個值，也可以使用如下的方式。

```rust
user1.email = String::from("another@example.com");
```

**注意如果要這樣使用，整個實例必須是可變的**，也就是必須使用 `let mut` 宣告。

```rust
let mut user1 = User {
    email: String::from("someone@example.com"),
    username: String::from("someone"),
    active: true,
    sign_in_count: 1,
}
```

我們也可以使用一個函式來建立實體。

```rust
fn build_user(email: String, username: String) -> User {
    User {
        email: email,
        username: username,
        active: true,
        sign_in_count: 1,
    }
}
```

## 變數名稱與 struct key 值相同的簡單寫法

重複寫 struct 中的的 key 值可能有些囉嗦，當函式的參數名稱與 key 值相同，Rust 允許下方這種更方便的寫法。

```rust
fn build_user(email: String, username: String) -> User {
    User {
        email,
        username,
        active: true,
        sign_in_count: 1,
    }
}
```

## 使用更新語法從其他實例建立新實例

我們可以使用舊實例的部分值來建立新的實例。

下方的例子展示了不使用更新語法的方式。

```rust
let user1 = User {
    email: String::from("someone@example.com"),
    username: String::from("someusername123"),
    active: true,
    sign_in_count: 1,
};

let user2 = User {
    email: String::from("another@example.com"),
    username: String::from("anotherusername567"),
    active: user1.active,
    sign_in_count: user1.sign_in_count,
};
```

我們可以使用 `..` 來指定剩餘未設置值的部分使用 `user1` 的值。

```rust
let user2 = User {
    email: String::from("another@example.com"),
    username: String::from("another@example.com"),
    ..user1
}
```

## 使用非字串 key 的元組結構體

可以定義與元組 (tuple) 類似的元組結構體 (tuple structs)。

當你想給元組取一個名字，卻又不想要幫每個元素都命名 key 值，這種 struct 十分實用。

```rust
struct Color(i32, i32, i32);
struct Point(i32, i32, i32);

let black = Color(0, 0, 0);
let origin = Point(0, 0, 0);
```

> 注意 black 與 origin 雖然組成方式一樣，但他們是不同的類型。

## 沒有任何字段的類單元結構體 (unit-like structs)

我們也可以定義一個沒有任何字段的類單元結構體，類似於 `()`。

類單元結構體常常在你想要在某個類型上實現 trait 但不需要在類型中存儲數據的時候發揮作用。

## 結構體資料的所有權

在剛剛的 `User` 結構體中，使用 `String` 而不是 `&str` 字符串 slice 類型，這是一個有意而為之的選擇。
因為我們想要這個結構體擁有它所有的資料，只要整個結構體是有效的話其資料也是有效的。

雖然可以在結構體中包含引用，這這會需要用上生命週期 (lifetimes)。

```rust
struct User {
    username: &str,
    email: &str,
    sign_in_count: u64,
    active: bool,
}

fn main() {
    let user1 = User {
        email: "someone@example.com",
        username: "someusername123",
        active: true,
        sign_in_count: 1,
    };
}
```

這裡編譯器會告訴你他需要生命週期標示符。

```text
error[E0106]: missing lifetime specifier
 -->
  |
2 |     username: &str,
  |               ^ expected lifetime parameter

error[E0106]: missing lifetime specifier
 -->
  |
3 |     email: &str,
  |            ^ expected lifetime parameter
```
