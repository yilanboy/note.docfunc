# Hash Map

Hash Map 是常見的一種資料集合。

`HashMap<K, V>` 是一個資料結構，可以將 K 型態的 key 映射到 V 型態的 value。

Hash Map 使用 hashing function 來決定如何將 key 和 value 存儲在記憶體中。

許多程式語言支援這種資料結構，但它們可能使用不同的名稱，如 hash、map、object、hash table、dictionary 或 associative array 等。

Hash Map 可以通過 key 查找資料，而不是像使用向量一樣通過索引查找。

Hash Map 在某些情況下非常有用，例如在遊戲中，可以使用 Hash Map 來跟踪每個隊伍的分數。

## 新建 Hash Map

可以使用 `new()` 創建一個空的 hash map，並使用 `insert` 增加元素。

```rust
use std::collections::HashMap;

let mut scores = HashMap::new();

scores.insert(String::from("Blue"), 10);
scores.insert(String::from("Yellow"), 50);
```

像 vector 一樣，hash map 將它們的數據儲存在堆 (heap) 上，這個 `HashMap` 的鍵類型是 `String` 而值類型是 `i32`，
**hash map 所有的鍵必須是相同類型，值也必須都是相同類型**。

還可以使用 vector 的 `collect` 方法來建立 hash map。

首先使用 vector 建立一組鍵與一組值，並使用 `zip` 建立 vector，之後使用 `collect` 方法將這個 vector 換成一個 `HashMap`。

```rust
use std::collections::HashMap;

let teams  = vec![String::from("Blue"), String::from("Yellow")];
let initial_scores = vec![10, 50];

let scores: HashMap<_, _> = teams.iter().zip(initial_scores.iter()).collect();
```

注意這裡 `HashMap<_, _>` 類型註解是**必要**的，因為 `collect` 可以是很多不同的資料結構，
這裡我們用 `_` 讓 rust 自動根據 vector 元素的資料類型來判斷 hash map 的鍵值類型。

## Hash Map 與所有權

對於像 `i32` 這種實現了 `Copy` trait 的類型，其值是可以拷貝進 `HashMap` 的，
但擁有所有權的 `String`，其值就會被移動至 `HashMap` 中，並成為這些值的所有者。

```rust
use std::collections::HashMap;

let field_name = String::from("Favorite color");
let field_value = String::from("Blue");

let mut map = HashMap::new();
map.insert(field_name, field_value);
// 這裡 field_name 和 field_value 不再有效，

// error: borrow of moved value `field_name`
println!("key is {} and value is {}", field_name, field_value);
```

可以將值的引用插入 `HashMap`，但這些引用指向的值必須在 `HashMap` 有效的同時也是有效的。

## 訪問 Hash Map 中的值

可以通過 `get` 方法並提供對應鍵來從 `HashMap` 中獲取值。

```rust
use std::collections::HashMap;

let mut scores = HashMap::new();

scores.insert(String::from("Blue"), 10);
scores.insert(String::from("Yellow"), 50);

let team_name = String::from("Blue");
let score = scores.get(&team_name);
```

`get` 會返回 `Option<V>`，所以結果被裝進 `Some`，如果 `HashMap` 中沒有對應的鍵，`get` 會返回 `None`。

可以使用 `for` 遍歷 `HashMap` 中的每一個鍵值對。

```rust
use std::collections::HashMap;

let mut scores = HashMap::new();

scores.insert(String::from("Blue"), 10);
scores.insert(String::from("Yellow"), 50);

for (key, value) in &scores {
    println!("{}: {}", key, value);
}
```

## 更新 Hash Map

### 覆蓋一個值

如果我們插入了一個鍵值對，接著用相同的鍵插入一個不同的值，那麽與這個鍵相關聯的舊值將被替換。

```rust
use std::collections::HashMap;

let mut scores = HashMap::new();

scores.insert(String::from("Blue"), 10);
scores.insert(String::from("Blue"), 25);

// {"Blue": 25}
println!("{:?}", scores);
```

### 只在鍵沒有對應值時插入

我們經常會先檢查特定鍵有沒有值，如果沒有就插入一個新值。

`HashMap` 有一個特有的 API 叫做 `entry`，他以我們想要檢查的鍵作為參數，並返回一個列舉 `Entry`，
這個列舉可以代表我們要找的值可能存在也可能不存在。

```rust
use std::collections::HashMap;

let mut scores = HashMap::new();
scores.insert(String::from("Blue"), 10);

scores.entry(String::from("Yellow")).or_insert(50);
scores.entry(String::from("Blue")).or_insert(50);

println!("{:?}", scores);
```

`Entry` 的 `or_insert` 方法在值存在時，就直接返回這個值的 `Entry`，如果值不存在，則在插入新的值之後返回這個新值的 `Entry`。

### 根據舊值更新一個新值

另外一個常見的使用場景是找到一個鍵對應的值，並根據舊的值去更新，例如計算一個句子中，每個單字重複出現的次數。

```rust
use std::collections::HashMap;

let text = "hello world wonderful world";

let mut map = HashMap::new();

for word in text.split_whitespace() {
    let count = map.entry(word).or_insert(0);
    *count += 1;
}

// {"world": 2, "hello": 1, "wonderful": 1}
println!("{:?}", map);
```

`or_insert` 方法實際上會返回這個鍵的值的一個可變引用 (`&mut V`)。

這里我們將這個可變引用儲存在 `count` 變數中，所以為了賦值必須首先使用星號（`*`）解引用 `count`，
這個可變引用在 `for` 循環的結尾離開作用域，這樣所有這些改變都是安全的並符合借用規則。

### Hashing Functions

HashMap 的預設雜湊函數為 SipHash，可提供對抗使用哈希表的 DoS 攻擊的能力。

SipHash 並非最快的哈希算法，但以性能降低換取更好的安全性是值得的。

如果發現預設的哈希函數對於目的太慢，可以透過指定不同的 hasher 來切換至另一個函數。

hasher 是實現 BuildHasher trait 的一種類型。

不一定需要從頭開始自行實現 hasher，crates.io 上有其他 rust 使用者共享的庫，提供實現許多常見哈希算法的 hasher。
