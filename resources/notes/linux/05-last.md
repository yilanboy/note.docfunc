# last 指令

last 指令可以用來檢查系統用戶的登入記錄。

```bash
last
```

指令結果如下：

```text
ubuntu   pts/2        61.220.176.180   Fri Aug  9 06:56   still logged in
ubuntu   pts/2        61.220.176.180   Fri Aug  9 06:08 - 06:09  (00:00)
ubuntu   pts/2        61.220.176.180   Fri Aug  9 05:42 - 05:43  (00:01)
ubuntu   pts/2        61.220.176.180   Fri Aug  9 05:40 - 05:41  (00:00)
ubuntu   pts/0        3.114.176.187    Fri Aug  9 05:38   still logged in
reboot   system boot  6.8.0-1010-azure Fri Aug  9 04:04   still running
reboot   system boot  6.8.0-1001-azure Fri Aug  9 03:53 - 04:04  (00:11)

wtmp begins Fri Aug  9 03:53:14 2024
```

你可以指定查看某一用戶的登入記錄：

```bash
last ubuntu
```

或是指定只想看前幾筆的紀錄：

```bash
last -n 5
```

last 也可以用來檢查開機的時間：

```bash
last reboot
```

`last` 預設是從 `/var/log/wtmp` 這個記錄檔案取得使用者登入的資料。

因為系統都會有 logrotate 服務定期清空記錄檔案並將舊紀錄移到其他檔案存放。這會導致 `last` 看不到比較舊的資料，此時可以用 `-f` 參數指定要查詢的記錄檔案路徑：

```bash
last -f /var/log/wtmp.1
```

## 參考資料

- [Linux 使用 last 指令檢查使用者登入記錄教學與範例](https://officeguide.cc/linux-last-command-tutorial-examples/)
