# Reset

如果你想移除所有尚未 commit 的修改時，可以使用 `git reset` 指令。

```bash
git reset --hard
```

但其實你想移除上一個 commit 也可以。

```bash
# 刪除最新的 commit 回到上一個版本的程式碼
git reset --hard HEAD^
```

使用 `--hard` 有些粗暴，如果只是單純的想拆掉 commit 並保留修改，可以使用 `--soft`。

```bash
git reset --soft HEAD^
```

> 請勿拿這個指令來拆掉別人的 commit。如果想要修改別人 commit 內容，建議使用 `git revert`。
