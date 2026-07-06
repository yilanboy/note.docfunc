# Vim

Vim 是一個很強大的編輯器，搭配多種快捷鍵，可以讓你的快速的編寫任何文件或是撰寫程式碼。

## 模式 (Mode)

Vim 有三種模式：

- Normal Mode (Keep Mode)
- Insert Mode
- Visual Mode

### Normal Mode

在 normal mode 底下無法編輯檔案，但可以輸入各種 vim 的快捷鍵。

### Insert Mode

在 normal mode 底下輸入 `i` 或是 `a`，即可進入編輯模式，開始修改檔案的內容。

### Visual Mode

在 normal mode 底下輸入 `v`，即可進入 visual mode，可以選取你想要的範圍已進行複製或是剪下。

## 指令模式

在 normal mode 輸入 `:` 進入指令模式，可以輸入指令或修改 vim 的設定。

例如，我想搜尋檔案的話，可以在輸入 `:/keyword`，就可以搜尋當前檔案的內容。

如果我想要修改 vim 的設定，例如在編輯器中顯示行的數字，可以輸入 `:set number`。

設定預設不會保留，如果想要儲存 vim 的設定可以建立一個檔案 `~/.vimrc`，然後將你要的檔案設定寫上。

```vim
syntax on
colorscheme onedark

set clipboard=unnamedplus
set clipboard+=unnamed
set number
set rnu
set smartindent
set tabstop=4
set shiftwidth=4
set expandtab
set backspace=indent,eol,start
set scrolloff=8

nmap zh ^
nmap zl $
nmap ,p "0p

vmap < <gv
vmap > >gv
```

## 參考資料

- [Why doesn't the backspace key work in insert mode?](https://vi.stackexchange.com/questions/2162/why-doesnt-the-backspace-key-work-in-insert-mode)
- [onedark.vim](https://github.com/joshdick/onedark.vim)
