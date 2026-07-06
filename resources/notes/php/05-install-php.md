# 安裝 PHP

記錄如何在各個系統上安裝 PHP 開發環境。

## MacOS

Mac 上最推薦使用 Homebrew 安裝 PHP，並使用 Laravel Valet 來設定 Laravel 的開發環境。

```shell
# 使用 homebrew 安裝 php
brew install php

# 使用 composer 安裝 laravel valet
composer global require laravel/valet

# 想使用 valet 指令需要先確保 ~/.composer/vendor/bin 有加入環境變數中
# valet 會使用 homebrew 來安裝 nginx 與 dnsmasq，因此需要 sudo 權限
sudo valet install

# 如果不想要每次執行 valet 指令都使用 sudo，可以將其加入 sudoers.d 中
# valet 會在 /private/etc/sudoers.d 加入 valet 與 brew 兩個使用者
sudo valet trust
```

建議可以使用 [PHP Monitor](https://phpmon.app/) 這個工具來查看 Valet 的狀態、
調整 PHP 的設定與**管理 PHP Extension**。

```shell
# 安裝 php monitor
brew tap nicoverbruggen/homebrew-cask
brew install phpmon --cask

# 如果想透過 php monitor 來安裝 php extension，需要先引入第三方的 php extension 庫
brew tap shivammathur/extensions
```

啟動 PHP Monitor 之後，就可以很清楚地看到各種 PHP 相關的設定，也能很方便的進行調整。
