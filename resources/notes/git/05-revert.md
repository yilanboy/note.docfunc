# Revert

與 `git reset` 不同，你可以用新增一個 commit 的方式來取消上一次 commit 的修改。

## 只想取消部分檔案的修改

這個操作與 `git reset` 的概念類似，可以使用 `git checkout` 來將部分檔案改回指定 commit 的內容。

```bash
git checkout COMMIT_ID -- /path/to/file1 /path/to/file2
```

然後將這些檔案 commit 一個版本即可。

```bash
git add -A
git commit "revert the file change in COMMIT_ID"
```
