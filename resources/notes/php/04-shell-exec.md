# Shell Exec

在 PHP 中，你可以使用 `exec()` 來執行外部指令。

```php
$output=null;
$exitCode=null;
// 在可以執行 whoami 指令的機器上，執行 whoami 指令
exec('whoami', $output, $exitCode);
echo "Returned with status $exitCode and output:\n";
print_r($output);

// 結果
// Returned with status 0 and output:
// Array
// (
//     [0] => allen
// )
```

PHP 其實提供另外一種更方便的方式來執行外部指令 - 反引號。

```php
$output = `whoami`;

echo $output; // allen
```

如果你擔心安全問題，想要關閉這個功能，可以在 `php.ini` 加入以下這行

```ini
disable_functions = "shell_exec"
```

## 參考資料

- [Execution Operators](https://www.php.net/manual/en/language.operators.execution.php)
