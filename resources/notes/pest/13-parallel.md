# Parallel

Pest 的測試預設都是使用單線程 (Single process)

Pest 提供 parallel 套件，讓你可以使用多線程的方式執行測試，縮短測試時間

透過 composer 安裝

```bash
composer require pestphp/pest-plugin-parallel --dev --with-all-dependencies
```

安裝好在執行測試時就可以使用 `--parallel` 這個參數

```bash
php artisan test --parallel
```
