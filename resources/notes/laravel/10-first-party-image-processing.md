# First Party Image Processing

Laravel 在 13.20 版本中加入了圖片處理功能，現在你可以直接從請求中取得圖片資料，並對其進行裁剪、轉換成 WebP 格式等處理。

```php
$request->image('avatar')
    ->cover(200, 200)
    ->toWebp()
    ->store('avatars');
```

要使用 Laravel 的圖片處理功能，你必須先確認 PHP 已經載入 [gd](https://www.php.net/manual/en/book.image.php) 或 [imagick](https://www.php.net/manual/en/book.imagick.php) 擴充模組。

此外，你還需要安裝 `intervention/image` 套件，這是 PHP 中非常熱門的圖片處理函式庫。

```bash
composer require intervention/image:^4.0
```

安裝好必要的擴充模組與函式庫後，你就可以開始使用 Laravel 的圖片處理功能了。

## 使用 WebP 提升網頁載入速度

這個功能推出後，我第一時間就修改了部落格的圖片上傳功能，把用戶上傳的圖片全部轉換成 WebP 格式，藉此提升網站的載入速度。

```php
public function __invoke(UploadImageRequest $request): JsonResponse
{
    // 將用戶上傳的圖片轉換成 WebP 格式
    $image = $request->image('upload')->toWebp();
    $name = $this->fileService->generateFileName();
    $filename = "$name.{$image->extension()}";
    // Image 實體提供 storeAs 方法，可以將圖片儲存到指定的硬碟，例如 AWS S3
    $image->storeAs(path: 'images', name: $filename, disk: config('filesystems.default'));
    $url = Storage::disk()->url('images/' . $filename);

    return response()->json(['url' => $url]);
}
```

> WebP 是 Google 於 2010 年推出的現代圖片格式，副檔名為 .webp。
> 它最大的特色是在維持極佳畫質的同時大幅縮小檔案體積，能有效加快網頁載入速度並節省儲存空間。

需要注意的是，Image 實體是 Immutable（不可變）的，每次呼叫圖片處理方法都會回傳一個新的 Image 實體。

```php
$image = $request->image('avatar')
    ->orient()
    ->cover(400, 400)
    ->sharpen(10);
```

更多圖片處理的操作，可參閱 [Laravel 官方文件](https://laravel.com/docs/13.x/images)。

原本 13.20 版本只能轉換成 WebP、JPG 與 JPEG 格式，Laravel 13.21 版本則新增了 `toPng()`、`toGif()`、`toAvif()` 與 `toBmp()` 等方法。

## 參考資料

- [Laravel Image Manipulation](https://laravel.com/docs/13.x/images)
- [First-Party Image Processing in Laravel 13.20](https://laravel-news.com/laravel-13-20-0)
- [RouteKey Model Attribute in Laravel 13.21](https://laravel-news.com/laravel-13-21-0)
