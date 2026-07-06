# 安裝本地環境上的 Composer 套件

如果你的專案想引入本地套件進行測試。我們需要修改一下專案的 `composer.json`。

首先新增一個 `repositories` 區塊，用來表明套件的來源位置。
因為我們想引入本地環境上的套件，因此我們需要在 `url` 欄位寫上套件的絕對路徑。

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "/Users/allen/code/php/livewire",
            "options": {
                "symlink": true
            }
        }
    ]
}
```

將 `symlink` 設定為 `true`，代表 `vendor` 底下的套件資料夾，會以軟連結的方式連結到本地套件的資料夾。

```bash
# 使用 tree 指令查看資料夾
tree vendor/livewire -L 1

# 顯示結果 livewire 資料夾為軟連結，直接指向我們在 url 設定的路徑
# vendor/livewire
# └── livewire -> /Users/allen/code/php/livewire/
```

接下來繼續修改 `composer.json`，我們需要更新套件的版本號碼。

```json
"require": {
    "livewire/livewire": "@dev"
}
```

這個 `@dev` 源自於套件 `composer.json` 的 `minimum-stability`。

```json
{
    "minimum-stability": "dev"
}
```

`@` 符號為 stability flags，代表你可以超出最小穩定性設定的範圍，以允許你安裝某個套件的非穩定版本。

> 以下是 Composer 官方文件對於 `@` 符號用途的解釋
>
> These allow you to further restrict or expand the stability of a package beyond the scope of the minimum-stability setting.
> You can apply them to a constraint, or apply them to an empty constraint if you want to allow unstable packages of a dependency for example.

專案的 `composer.json` 設定好了之後就可以在專案引入本地套件了！
在專案底下使用 `composer update` 指令來更新套件。

```bash
composer update
```

更新成功之後，你會發現當你直接修改本地套件的程式碼時，引用該套件的專案就會直接使用你修改的程式碼。

## 參考資料

- [Installing a local Composer package in your PHP project](https://aschmelyun.com/blog/installing-a-local-composer-package-in-your-php-project/)
- [Composer - Versions and constraints](https://getcomposer.org/doc/articles/versions.md)
