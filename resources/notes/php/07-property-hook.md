# 簡單介紹 PHP 8.4 的屬性掛鉤

前陣子 PHP 8.4 終於正式發佈啦！ 🎉🎉🎉

本次年度大更新一樣帶來許多新的功能與語法糖，除了終於支援 HTML 5 的解析以外，還帶來了新的 array 方法、非對稱可見性 (Asymmetric Visibility) 以及屬性掛鉤 (Property Hooks)。

其中屬性掛鉤是許多 PHPer 敲碗已久的新功能，本篇文章就來簡單介紹一下什麼是屬性掛鉤？以及如何使用它。希望可以幫助你在看完文章後，對屬性掛鉤有了初步的認識，並開始思考如何將屬性掛鉤運用在自己的專案上。😉

## 什麼是屬性掛鉤 (Property Hooks)？

屬性掛鉤的用途，一言以蔽之，就是讓你可以在存取屬性時進行額外的邏輯行為。例如在取用屬性的值之前先對值進行轉換，又或者在對屬性賦值之前，先對值進行檢查，確保值符合規則。

在以往的版本中，若想要實現相似的功能，除了可以透過宣告 Getter 與 Setter 方法外，亦可利用魔術方法 `__get` 與 `__set`。但是後者對性能有著負面影響，而且可讀性不佳，因此屬性掛勾的出現，就是想要改善這兩個問題。在大多情境下，屬性掛鉤能有效簡化程式碼，提升可讀性。透過 `get` 和 `set` 兩個語法糖，我們可以更直觀的操作屬性，接下來將簡單的介紹它們的用法。

## `get` 掛鉤

假設有一個 User 類別，擁有 `$firstName` 與 `$lastName` 兩個屬性，如果我想取得用戶的全名，可以怎麼做呢？

以下方的程式碼為例，在 PHP 8.4 出來之前，我們可以宣告一個方法將屬性 `$firstName` 與 `$lastName` 組合起來變成全名。

```php
class User
{
    public function __construct(
        public string $firstName,
        public string $lastName
    ) {}

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }
}

$user = new User('Foo', 'Bar');
echo $user->getFullName(); // Foo Bar
```

而在 PHP 8.4 後，我們可以不使用方法，而是在屬性上面新增一個 `get` 掛鉤。當你嘗試取得屬性的值時，就會觸發這個 `get` 掛鉤。

```php
class User
{
    public string $fullName {
        get {
            return $this->firstName.' '.$this->lastName;
        }
    }

    public function __construct(
        public string $firstName,
        public string $lastName
    ) {}
}

$user = new User('Foo', 'Bar');
echo $user->fullName; // Foo Bar
```

如果你的 `get` 掛鉤並未包含屬性自己本身，那麼這個屬性就是**虛擬屬性 (Virtual Property)**，以上面的例子為例，因為 `get` 掛鉤中並未包含 `$this→fullName`，所以 `$fullName` 屬於虛擬屬性。虛擬屬性在物件中並不會佔用記憶體，有點類似其他語言的 Computed Properties 或是 Derived Properties。

需要注意的是，虛擬屬性不能手動對其賦值。

```php
$user->fullName = 'Allen'; // Error: Property User::$fullName is read-only
```

`get` 掛鉤可以使用表達式 (Expression) 讓寫法更爲精簡。

```php
class User
{
    public string $fullName {
        get => $this->firstName.' '.$this->lastName;
    }

    public function __construct(
        public string $firstName,
        public string $lastName
    ) {}
}

$user = new User('Foo', 'Bar');
echo $user->fullName; // Foo Bar
```

## `set` 掛鉤

如果想要在設定屬性前執行一些額外的操作，那麼可以考慮使用接下來要介紹的 `set` 掛鉤。舉一個常見的例子，在設定屬性前檢查要賦予的值是否符合規則，這種情境就很適合使用 `set` 掛鉤。

過去沒有屬性掛鉤時，我們可以使用一個 Setter 方法來檢查賦予屬性的值。

```php
class Computer
{
    // 將屬性設定為唯讀，外部無法直接修改 $ip
    readonly public string $ip;

    // 使用 setter 方法來修改唯讀屬性，並在這裡檢查 $ip 的格式是否正確
    public function setIp(string $ip): void
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid IP');
        }

        $this->ip = $ip;
    }
}

$computer = new Computer();
$computer->setIp('10.0.0.1');
echo $computer->ip; // 10.0.0.1

$computer->setIp('256.256.256.256'); // InvalidArgumentException: Invalid IP
```

在 PHP 8.4 後，我們可以在屬性上使用 `set` 掛鉤來進行檢查。

```php
class Computer
{
    public string $ip {
        set (string $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                throw new InvalidArgumentException('Invalid IP');
            }

            $this->ip = $ip;
        }
    }
}

$computer = new Computer();
$computer->ip = '10.0.0.1';
echo $computer->ip; // 10.0.0.1

$computer->ip = '256.256.256.256'; // InvalidArgumentException: Invalid IP
```

`set` 掛鉤是可以設定初始值的。

```php
class Computer
{
    // set 掛鉤可以設定初始值
    public string $ip = '127.0.0.1' {
        set (string $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                throw new InvalidArgumentException('Invalid IP');
            }

            $this->ip = $ip;
        }
    }
}


$computer = new Computer();
echo $computer->ip; // 127.0.0.1
```

如果邏輯較為簡單，例如將值的首字母改為大寫，那麼 `set` 掛鉤一樣可以使用表達式的精簡寫法。

```php
class User
{
    public string $name {
        set (string $name) => $this->name = ucfirst($name);
    }
}

$user = new User();
$user->name = 'allen';
echo $user->name; // Allen
```

剛剛的例子都只有使用其中一種掛鉤，如果你要一起使用當然也是可以的。

```php
class User
{
    public string $email {
        get => 'mailto:'.$this->email;
        set (string $mail) {
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Invalid email');
            }

            $this->email = $mail;
        }
    }
}


$user = new User();
$user->email = '...';
echo $user->email; // mailto:...

$user->email = 'hello world!'; // InvalidArgumentException: Invalid email
```

## 在介面中定義屬性

PHP 8.4 還帶來一個新的特性，我們都知道 PHP 的介面無法定義屬性，但現在你可以在介面中定義含有屬性掛勾的屬性。

```php
interface Example
{
    // 實作介面的類別必須擁有公開可讀的屬性，但是否公開可寫則不受限制
    public string $readable {
        get;
    }

    // 實作類必須有公開可寫的屬性，但是否有公開可讀則不受限制
    public string $writeable {
        set;
    }

    // 實作類必須有公開可讀與公開可寫的屬性。
    public string $both {
        get;
        set;
    }
}
```

屬性掛鉤在使用上不只好入門，還能有效提升程式碼的可讀性，是很棒的新功能。

本篇文章稍微介紹了一下屬性掛鉤的用法，如果想要更深入的了解屬性掛鉤，建議可以參考下面的連結，來了解更多有關於屬性掛鉤的知識。

## 參考資料

- [PHP RFC: Property hooks](https://wiki.php.net/rfc/property-hooks)
- [A Guide to PHP 8.4 Property Hooks](https://www.zend.com/blog/php-8-4-property-hooks)
- [歷經多年醞釀 PHP 8.4 終於引入屬性掛勾](https://www.ithome.com.tw/news/166207)
- [PHP 8.4 Property Hooks](https://ashallendesign.co.uk/blog/php-84-property-hooks#content-type-compatibility)
