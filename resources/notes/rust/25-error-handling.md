# Error Handling

Rust 的錯誤有兩個主要類別。

- 可恢復錯誤 (recoverable)，在 Rust 中可以用 `Result<T, E>` 處理。
- 不可恢復錯誤 (unrecoverable)，在 Rust 中可以用 `panic!` 這個巨集 (macro) 來處理並終止程式。

大部分程式語言會將這兩類錯誤統一成例外 (Exception)，但 Rust 並沒有例外。

## panic! 與不可恢復錯誤

如果遇到開發者無法處理的錯誤，可以使用 `panic!` 巨集。
當執行 `panic!` 時，程式會印出一個錯誤訊息，接著展開 (unwind) 並清理堆疊 (stack) 上的資料，最後退出程式。

> unwind 意味著 Rust 會沿著堆疊往回走，並清理遇到的每個函數的數據。
> 但這個流程很花時間，因此 Rust 也允許你不清理並直接終止程式 (aborting)。此時清理的工作會交由作業系統處理。
>
> 如果你需要最終二進制文件越小越好，可以將 unwind 設定為 abort。
> 只需要在 `Cargo.toml` 中設定。
>
> ```toml
> [profile.release]
> panic = 'abort'
> ```

在程式碼中調用 `panic!`。

```rust
fn main() {
    panic!("crash and burn");
}
```

程式碼執行後會出現這樣的訊息。

```shell
$ cargo run
   Compiling panic v0.1.0 (file:///projects/panic)
    Finished dev [unoptimized + debuginfo] target(s) in 0.25s
     Running `target/debug/panic`
thread 'main' panicked at 'crash and burn', src/main.rs:2:5
note: run with `RUST_BACKTRACE=1` environment variable to display a backtrace
```

最後兩行包含 `panic!` 調用的錯誤訊息。

倒數第二行顯示 panic 中設定的錯誤訊息並指名程式碼中 panic 出現的位置。 `src/main.rs:2:5` 顯示這是 `src/main.rs` 中第二行第四個字元。

而最後一行的訊息。有時候 panic 會出現在我們程式碼所調用的程式碼中，因此錯誤訊息的報告是由別人程式碼中的 panic 所發出。
這時我們可以使用 panic 的 backtrace 來查找我們調用的 panic。

## 使用 panic! 的 Backtrace

簡單舉個例子，我們嘗試訪問 `Vector` 中不存在的元素。

```rust
fn main() {
    let v = vec![1, 2, 3];

    // 嘗試訪問 Vector 不存在的元素，會造成 panic
    v[99];
}
```

> 在 C 語言中，試圖讀取超出數據結構末端的數據是未定義行為。即使內存中的位置不屬於該結構，但依然可能會得到對應於該數據結構中該元素的內存位置的內容。
> 這種情況稱為緩衝區超讀（buffer overread），如果攻擊者能夠以某種方式操縱索引以讀取存儲在數據結構之後不應被允許讀取的數據，這可能會導致安全漏洞。

為了避免安全問題，如果嘗試讀取不存在的元素，Rust 會停止執行。

```shell
$ cargo run
   Compiling panic v0.1.0 (file:///projects/panic)
    Finished dev [unoptimized + debuginfo] target(s) in 0.27 secs
     Running `target/debug/panic`
thread 'main' panicked at 'index out of bounds: the len is 3 but the index is
99', /checkout/src/liballoc/vec.rs:1555:10
note: Run with `RUST_BACKTRACE=1` for a backtrace.
```

可以看到錯誤訊息指向了不是由我們編寫的地方 `vec.rs`，這是 Rust 標準庫中實作 `Vec` 的部分。

錯誤訊息提醒我們可以設定環境變數 `RUST_BACKTRACE=1` 來啟動 backtrace。

backtrace 會列出執行到目前位置所有被調用的函式列表，如下所示。

```shell
➜ RUST_BACKTRACE=1 cargo run
    Finished dev [unoptimized + debuginfo] target(s) in 0.00s
     Running `target/debug/hello_world`
thread 'main' panicked at 'index out of bounds: the len is 3 but the index is 99', src/main.rs:5:5
stack backtrace:
   0: rust_begin_unwind
             at /rustc/9eb3afe9ebe9c7d2b84b71002d44f4a0edac95e0/library/std/src/panicking.rs:575:5
   1: core::panicking::panic_fmt
             at /rustc/9eb3afe9ebe9c7d2b84b71002d44f4a0edac95e0/library/core/src/panicking.rs:64:14
   2: core::panicking::panic_bounds_check
             at /rustc/9eb3afe9ebe9c7d2b84b71002d44f4a0edac95e0/library/core/src/panicking.rs:159:5
   3: <usize as core::slice::index::SliceIndex<[T]>>::index
             at /rustc/9eb3afe9ebe9c7d2b84b71002d44f4a0edac95e0/library/core/src/slice/index.rs:260:10
   4: core::slice::index::<impl core::ops::index::Index<I> for [T]>::index
             at /rustc/9eb3afe9ebe9c7d2b84b71002d44f4a0edac95e0/library/core/src/slice/index.rs:18:9
   5: <alloc::vec::Vec<T,A> as core::ops::index::Index<I>>::index
             at /rustc/9eb3afe9ebe9c7d2b84b71002d44f4a0edac95e0/library/alloc/src/vec/mod.rs:2732:9
   6: hello_world::main
             at ./src/main.rs:5:5
   7: core::ops::function::FnOnce::call_once
             at /rustc/9eb3afe9ebe9c7d2b84b71002d44f4a0edac95e0/library/core/src/ops/function.rs:250:5
note: Some details are omitted, run with `RUST_BACKTRACE=full` for a verbose backtrace.
```

可以看到 backtrace 的 `6:...` 標示出我們造成問題的地方。
