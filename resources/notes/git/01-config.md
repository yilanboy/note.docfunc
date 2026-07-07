# 初次使用 Git 的設定

查看全域設定。

```bash
git config --global --list
```

查看目前專案的設定。

```bash
git config --local --list
```

設定 username 與 email。

```bash
# 全域設定
git config --global user.email "allen@example.com"
git config --global user.name "allen"

# 目前專案設定
git config --local user.email "allen@example.com"
git config --local user.name "allen"
```

使用編輯期來編輯設定。

```bash
git config --global --edit
```
