# Map

使用 `map` 可以修改按鍵的對應，例如將 `zl` 對應到 `$`，這樣就可以在 normal mode 底下使用 `zl` 來移動到行尾。

```vim
nmap zl $
```

而 `map` 有幾種前綴，分別是：

- `nmap` : 只在 normal mode 下生效。
- `vmap` : 只在 visual mode 下生效。
- `imap` : 只在 insert mode 下生效。
- `cmap` : 只在 command mode 下生效。

除此之外還有一種 `nore` 前綴，代表非遞迴，這樣就可以避免重複對應。例如：

```vim
map a b
map c a
```

這樣對 `c` 來說，效果等同於：

```vim
map c a
```

## 參考資料

- [vim 的幾種模式和按鍵映射](http://haoxiang.org/2011/09/vim-modes-and-mappin/)
