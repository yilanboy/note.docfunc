# String

## 什麼是字串？

Rust 只有一種字串類型：`str`。

字串片段（string slice）是對 UTF-8 編碼的字串數據的引用，通常以 `&str` 的借用形式出現。

字串文字（string literals）存儲在程序的二進製文件中，因此也是一種字串片段。

Rust 的標準庫提供了一個名為 `String` 的類型，是可變的、擁有的、UTF-8 編碼的字串類型。

在 Rust 中，"字串"可能指的是 `String` 或 string slice（`&str`）類型之一，並不僅僅指其中一種類型。

儘管本節主要介紹 String，但在 Rust 的標準庫中，String 和 string slice 類型都被廣泛使用。

String 和 string slice 都是 UTF-8 編碼的。

## 新建字串

可以使用 `new()` 函式建立一個空的字串。

```rust
let mut s = String::new();
```

我們可以往空的字串塞資料。

```rust
let data = "initial contents";

// to_string() 可以用在任何有實作 Display trait 的類型
let s = data.to_string();

// 該方法也可直接用於字串字面值
let s = "initial contents".to_string();

// 等同於使用 to_string()
let s = String::from("initial contents");
```

注意字串是使用 UTF-8 編碼，所以可以包含任何正確編碼的字串。

```rust
let hello = String::from("السلام عليكم");
let hello = String::from("Dobrý den");
let hello = String::from("Hello");
let hello = String::from("שָׁלוֹם");
let hello = String::from("नमस्ते");
let hello = String::from("こんにちは");
let hello = String::from("안녕하세요");
let hello = String::from("你好");
let hello = String::from("Olá");
let hello = String::from("Здравствуйте");
let hello = String::from("Hola");
```

## 更新字串

`String` 的內容可以變更。

你也可以使用 `+` 或 `!format` 巨集來拼接 `String` 的值。

### 使用 push_str 與 push 附加字串

我們可以使用 `push_str` 與 `push` 來附加字串片段，使 `String` 變長。

```rust
let mut s = String::from("foo");

// foobar
s.push_str("bar");
```

`push_str` 並不會獲取傳入參數的所有權，因此下面的程式碼可以正常執行。

```rust
let mut s1 = String::from("foo");
let s2 = "bar";

s1.push_str(s2);

// s2 is bar
println!("s2 is {}", s2);
```

`push` 可以將一個單獨的字，附加到一個字串上。

```rust
let mut s = String::from("lo");

// 注意這裡使用單引號
// lol
s.push('l');

// error: expected `char`, found `&str`
s.push("l")
```

### 使用 + 或 !format 拼接兩個字串

可以使用 `+` 拼接兩個字串。

```rust
let s1 = String::from("Hello, ");
let s2 = String::from("world!");

// Hello, world!
// 注意 s1 被移動了，後續不能繼續使用
let s3 = s1 + &s2;
```

為什麼 `s2` 這裡要使用引用，是因為使用 `+` 運算符時，其實是調用 `add` 的方法。

```rust
fn add(self, s: &str) -> String {
    // ...
}
```

在標準函式庫中，`add` 方法是使用泛型 (generic) 定義，但上面 `add` 已經用具體的類型替代了泛型，因為這裡我們使用了 `String` 來調用這個方法。

接下來詳細說明這行程式碼。

```rust
let s3 = s1 + &s2;
```

首先，`s2` 使用了 `&`，意味著我們使用第二個字串的引用與第一個字串相加。

我們從 `add` 函式的第二個參數 `s` 來判斷，這裡只能將 `&str` 和 `String` 相加，不能將兩個 `String` 值相加。

但這裡其實有個問題，正如 `add` 的第二個參數 `s` 所指定的是 `&str`，
`&s2` 的類型是 `&String` 而不是 `&str`，那為什麼這行程式碼仍然可以執行？

主要是因為 `&s2` 的類型從 `&String` 被**強轉 (coerced)** 為 `&str`。

當 `add` 被調用時，rust 使用了一個被稱為**解引用強制多態（deref coercion**）的技術，你可以將其理解為它把 `&s2` 變成了 `&s2[..]`。

因為 `add` 沒有獲取參數 `s` 的所有權，所以 `s2` 在這個操作後仍然是有效的 `String`。

可以發現 `add` 獲取了 `self` 的所有權，因為 `self` 沒有使用 `&`。

這意味 `s1` 的所有權被移動到 `add` 中，因此後續 `s1` 就不再有效。

雖然 `let s3 = s1 + &s2;` 看起來就像它會複製兩個字串並創建一個新的字串，
然後實際上，這行程式碼會獲取 `s1` 的所有權，附加上從 `s2` 中拷貝的內容，並返回結果的所有權。

換句話說，它看起來好像生成了很多拷貝不過實際上並沒有，這個實現比拷貝要更高效。

如果你要串連多個字串，`+` 就顯得有點笨重了。

```rust
let s1 = String::from("tic");
let s2 = String::from("tac");
let s3 = String::from("toe");

let s = s1 + "-" + &s2 + "-" + &s3;
```

這時候我們可以是 `!format` 代替。

```rust
let s1 = String::from("tic");
let s2 = String::from("tac");
let s3 = String::from("toe");

let s = format!("{}-{}-{}", s1, s2, s3);
```

## 索引字串

很多語言都提供索引字串的功能，例如在 PHP 中。

```php
$s = 'string';

// s
echo $s[0];
// g
echo $s[-1];
```

但在 rust 中，我們不能這麼做，為什麼呢？

```rust
let s1 = String::from("hello");
let h = s1[0];
```

### 內部表現

`String` 其實是 `Vec<u8>` 的封裝，讓我們看一個正確編碼字串的例子。

```rust
let len = String::from("Hola").len();
```

在這裡，`len` 的值是 4 ，這意味著儲存字串 `Hola` 的 `Vec` 的長度是四個字節，
因為每一個英文字母的 UTF-8 編碼都占用一個字節。那下面這個例子呢？

```rust
// 注意這個字串中的首字母是西里爾字母的 Ze 而不是阿拉伯數字 3 。
let len = String::from("Здравствуйте").len();
```

你可能會以為 `len` 的值是 12，然而實際上是 24。

這是使用 UTF-8 編碼 `Здравствуйте` 所需要的字節數，這是因為每個 Unicode 標量值需要**用兩個字節來儲存**。
因此一個字符串字節值的索引並不總是對應一個有效的 Unicode 標量值。

以剛剛無效的 rust 程式碼來舉例，如果這段程式碼是有效的，那 `answer` 的內容應該是什麼？

```rust
let hello = "Здравствуйте";

// answer 的內容應該是什麼？
let answer = &hello[0];
```

`answer` 的值應該是什麽呢？它應該是第一個字符 `З` 嗎？

當使用 UTF-8 編碼時，`З` 的第一個字節 `208`，第二個是 `151`，所以 `answer` 實際上應該是 `208`，不過 `208` 自身並不是一個有效的字母
。返回 `208` 可不是一個請求字符串第一個字母的人所希望看到的，不過它是 rust 在字節索引 `0` 位置所能提供的唯一數據。

用戶通常不會想要一個字節值被返回，即便這個字符串只有拉丁字母： 即便 `&"hello"[0]` 是返回字節值的有效代碼，它也應當返回 `104` 而不是 `h`。

為了避免返回意想不到值並造成不能立刻發現的 bug。rust 選擇不編譯這些程式碼並及早杜絕了誤會的發生。

## 字節、標量值和字形簇

這引起了關於 UTF-8 的另外一個問題，從 rust 的角度來講，事實上有三種相關方式可以理解字串，分別是字節、標量值和字形簇 (最接近人們眼中字母的概念)。

比如這個用梵文書寫的印度語單詞 `नमस्ते`，最終它儲存在 vector 中的 u8 值看起來像這樣。總共 18 個字節，也就是電腦裡最終會儲存的資料。

```rust
[224, 164, 168, 224, 164, 174, 224, 164, 184, 224, 165, 141, 224, 164, 164, 224, 165, 135]
```

但如果從 unicode 標量值的角度理解它們，也就像 rust 的 `char` 類型那樣，這些字節看起來像這樣。
總共有六個 `char`，不過第四個和第六個都不是字母，它們是發音符號本身並沒有任何意義。

```rust
['न', 'म', 'स', '्', 'त', 'े']
```

最後，如果以字形簇的角度理解，就會得到人們所說的構成這個單詞的四個字母。

```rust
["न", "म", "स्", "ते"]
```

Rust 提供了多種不同的方式來解釋電腦儲存原始字串的資料，這樣程式就可以選擇它需要的表現方式，而無所謂是何種人類語言。

最後一個 rust 不允許使用索引獲取 `String` 字符的原因是索引操作預期總是需要常數時間 $O(1)$。

但是對於 `String`，rust 不可能保證這樣的性能，因為 rust 不得不檢查從字串的開頭到索引位置的內容來確定這裡有多少有效的字符。

## 字串 Slice

字串 Slice 並不是一個好的操作，因為字串索引應該返回的類型是不明確的 (字節值、字符、字形簇或者字串 slice)。

我們可以使用 `[]` 和一個 `range` 來創建含特定字節的字串 slice，這比使用 `[]` 和單個值的索引要來得明確的多。

```rust
let hello = "Здравствуйте";

// 因為一個字需要兩個字節，這裡代表取用前四個字節，所以 s 的值為 "Зд"
let s = &hello[0..4];
```

那如果獲取 `&hello[0..1]` 會發生什麽呢？

答案是...在運行時會 panic，就跟訪問 vector 中的無效索引時一樣。

```text
thread 'main' panicked at 'byte index 1 is not a char boundary; it is inside 'З' (bytes 0..2) of `Здравствуйте`', src/libcore/str/mod.rs:2188:4
```

謹慎使用字串 slice，因為這個操作有可能導致你的程式崩潰。

## 遍歷字串的方法

如果你需要單獨操作 Unicode 標量值，可以使用 `chars` 方法。

```rust
for c in "नमस्ते".chars() {
    println!("{}", c);
}
```

這會返回字串中的每一個 `char`，上述程式碼會印出。

```text
न
म
स
्
त
े
```

`bytes` 方法返回每一個原始字節。

```rust
for b in "नमस्ते".bytes() {
    println!("{}", b);
}
```

上述程式碼會印出。

```text
224
164
// --snip--
165
135
```

從字串中獲取字形簇是很複雜的，所以標準庫並沒有提供這個功能，crates.io 上有些提供這樣功能的 crate。

## 字串並不簡單

總而言之，字串是很複雜的，但不同的程式語言對如何向工程師展示這種複雜性做出了不同的選擇。

Rust 選擇了以準確的方式處理 `String` 資料作為所有 Rust 程式的默認行為，這意味著我們必須更多的思考如何預先處理 UTF-8 資料。

這種權衡取捨相比其他語言更多的暴露出了字串的複雜性，不過也使你在開發生命周期後期免於處理涉及非 ASCII 字符的錯誤。
