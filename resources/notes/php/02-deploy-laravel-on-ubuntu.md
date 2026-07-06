# 在 Ubuntu 上面部署 Laravel

最近想將 Laravel 部署在 Ubuntu 上，記錄一下 PHP 8.3 的安裝方式。

## 安裝 PHP 8.3

```bash
# 在 Dockerfile 中 需要先安裝 add-apt-reposit
sudo apt-get install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update
sudo apt install php8.3 php8.3-{cli,common,curl,xml,mbstring} -y
# 如果你是搭配 Nginx 使用，需要安裝 php-fpm
sudo apt install php8.3-fpm -y
# redis 與 swoole 依照需求安裝
sudo apt install php8.3-{redis,swoole} -y
```
