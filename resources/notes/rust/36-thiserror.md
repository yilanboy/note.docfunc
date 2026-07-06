# `thiserror`

`thiserror` 是一個用來定義自定義錯誤類型的庫，它提供了一個巨集 `Error` 來定義錯誤類型，並且可以自動實現 `std::error::Error` trait。

來看下面一個不使用 `thiserror` 的例子：

```rust
use std::error::Error;
use std::fmt::Debug;
use std::fmt::Display;
use std::fmt::Formatter;

enum CustomError {
    FileReadError(std::io::Error),
}

fn main() {
    println!("{:?}", read_file().unwrap_err());
}

fn read_file() -> Result<(), CustomError> {
    use CustomError::*;

    std::fs::read_to_string("non-exist-file").map_err(FileReadError)?;

    Ok(())
}

// 如果你想新增一個錯誤處理，並為這個錯誤處理新增錯誤訊息，你需要替 CustomError 實作各種 trait 與方法
// 這會導致程式碼變得冗長且複雜
// 為 CustomError 實作 Debug trait
impl Debug for CustomError {
    // 這裡的 self 是 CustomError 本身
    fn fmt(&self, f: &mut Formatter<'_>) -> std::fmt::Result {
        writeln!(f, "{}", self)?;

        // 因為這裡需要 self.source()，所以需要實作 Error 的 source 方法
        if let Some(source) = self.source() {
            writeln!(f, "Caused by: {}", source)?;
        }

        Ok(())
    }
}

// 為 CustomError 實作 source 方法
impl Error for CustomError {
    fn source(&self) -> Option<&(dyn Error + 'static)> {
        match self {
            CustomError::FileReadError(err) => Some(err),
        }
    }
}

// 為 CustomError 實作 Display trait
impl Display for CustomError {
    fn fmt(&self, f: &mut Formatter<'_>) -> std::fmt::Result {
        match self {
            CustomError::FileReadError(_) => write!(f, "failed to read the file"),
        }
    }
}
```

上述的程式碼我們可以使用 `thiserror` 來簡化：

```rust
use thiserror::Error;

#[derive(Error, Debug)]
enum CustomError {
    #[error("failed to read the file")]
    FileReadError(#[from] std::io::Error),
}

fn main() {
    println!("{:?}", read_file().unwrap_err());
}

fn read_file() -> Result<(), CustomError> {
    use CustomError::*;

    std::fs::read_to_string("non-exist-file").map_err(FileReadError)?;

    Ok(())
}
```

## 參考資料

- [Unwraps EVERYWHERE](https://www.reddit.com/r/rust/comments/mjaoq7/unwraps_everywhere/)
- [How to Use the “thiserror” Crate in Rust](https://betterprogramming.pub/a-simple-guide-to-using-thiserror-crate-in-rust-eee6e442409b)
