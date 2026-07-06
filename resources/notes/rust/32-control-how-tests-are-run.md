# 控制程式如何執行

就跟 `cargo run` 一樣，`cargo test` 也會產生用於測試的執行檔，並執行它。

## 平行或接續執行測試

預設情況下，測試會並行執行，這樣可以加快測試速度。如果你的測試需要使用到共享資源，例如檔案或網路連線，並行執行可能會導致測試失敗。你可以使用 `--test-threads` 參數來控制測試執行緒數量。

```bash
cargo test -- --test-threads=1
```

## 顯示輸出

在測試中，如果測試是成功的，是無法看到 `println!` 輸出的，只有失敗的測試會顯示。

如果你想要在成功的測試看到輸出，可以使用 `--show-output` 參數。

```bash
cargo test -- --show-output
```

## 透過名稱來執行部分測試

如果你想要執行特定名稱的測試，可以在參數 `cargo test` 後面加上測試的名稱。

```bash
cargo test add
```

這樣測試只會執行函式名稱包含 `add` 的測試。

## 忽略測試

對於想忽略不執行的測試，可以在測試函式上加上 `#[ignore]`。

這樣當執行 `cargo test` 時，`expensive_test` 這個測試就不會被執行。

```rust
#[test]
fn it_works() {
    assert_eq!(2 + 2, 4);
}

#[test]
#[ignore]
fn expensive_test() {
    // 會執行一小時的程式碼
}
```

如果想執行被忽略的測試，可以加上參數 `--ignored`。

```bash
cargo test -- --ignored
```
