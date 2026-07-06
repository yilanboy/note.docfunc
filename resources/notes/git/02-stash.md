# 使用 Stash 來暫存目前的變更

將目前所有修改加入暫存。

```bash
git stash
```

暫存可以使用註解，方便查找。

```bash
git stash save "save message"
```

查看所有暫存。

```bash
git stash list
```

顯示最新的暫存。

```bash
git stash show
```

顯示第二新的暫存，根據 `{}` 中的數字以此類推。

```bash
git stash show stash@{1}
```

顯示最新暫存的改動。

```bash
git stash show -p
```

顯示第二新的暫存的改動，根據 `{}` 中的數字以此類推。

```bash
git stash show -p stash@{1}
```

套用暫存，並將此暫存從暫存列表中刪除。

```bash
git stash pop
```

套用暫存，但是不將此暫存從暫存列表中移除。

```bash
git stash apply
```

丟棄暫存。

```bash
git stash drop
```

刪除全部暫存。

```bash
git stash clear
```
