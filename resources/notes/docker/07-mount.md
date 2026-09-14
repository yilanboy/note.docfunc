---
date: '2025-02-06'
updated: '2026-07-06'
tags: [docker]
---

# Mount

在 Docker 中，你可以透過 `--mount` 來將主機上的某個資料夾掛載到 Docker 容器中。
這個掛載是雙向同步的，當你在主機上修改資料夾底下的某個檔案，那麼容器中的檔案也會同步修改的內容。
反過來也是，當你在容器中修改某個檔案，那麼主機上的檔案也會同步修改。

> 過去掛載的指令是 `--volume`，但現在更建議使用功能更為方便且彈性的 `--mount`。

想要啟動一個掛載本地資料夾的容器，下面是一個簡單的示範指令：

```bash
docker run --mount type=bind,source=./src,target=/app ubuntu
```

如此一來就能將 `/src` 資料夾掛載到容器中的 `/app`。

如果你希望容器中不能修改檔案，讓檔案在容器中是**唯讀的**，可以使用 `readonly` 或 `ro`。

```bash
docker run --mount type=bind,source=./src,target=/app,ro ubuntu
```

## 能不能掛載單一個檔案？

可以！但不建議，因為同步的效果有可能會失效 😅。

Docker 中只會看同一個 [inode](https://zh.wikipedia.org/zh-tw/Inode) 檔案有沒有改變。
如果你有任何更新檔案內容的操作，例如 `sed` 或者 `vim`，這些操作會導致檔案的 inode 發生變更，
新的 inode 檔案不會被 Docker 注意到。

**它只會注意舊的 inode 檔案內容有沒有變化**。

簡單做的小實驗，建立一個新的檔案 `plain.txt`，並查看這個檔案的 inode，
然後使用 vim 修改這個檔案，修改完後再次查看這個檔案的 inode，你會發現 inode 發生變化。

```bash
touch plain.txt

# Use -i flag to print the file's file serial number (inode number).
ls -i

# Result:
# 262219571 plain.txt

# Use vim to update plain.txt, for example, adding 'Hello world!',
# then check inode again
ls -i

# Result:
# 262220007 plain.txt
```

有些更新操作可以避免檔案的 inode 發生變化，例如：

```bash
echo 'append a new line' >> plain.txt
```

或是使用 VSCode 修改檔案也能避免檔案 inode 發生變化。

## 參考資料

- [Docker - Bind Mount](https://docs.docker.com/engine/storage/bind-mounts/)
- [Docker mounted file is not updating? Your text editor probably updated the file inode.](https://medium.com/@jonsbun/why-need-to-be-careful-when-mounting-single-files-into-a-docker-container-4f929340834)
