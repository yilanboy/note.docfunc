# Static Route

靜態路由是一種手動設定的路由，通常用於小型網路或是網路設備。靜態路由的優點是簡單、容易設定，但缺點是不具備自動更新的功能，需要手動維護。

你可以使用 `ip route` 指令來設定靜態路由，例如：

```bash
ip route add 172.16.11.13/32 via 192.168.0.1
```

也可以透過 `dev` 參數來指定網路介面：

```bash
ip route add 172.16.11.13/32 dev eth0
```

## DHCP Lease 會清除靜態路由

跟 DHCP 租借而來的 IP 地址有一定的期限。**時間一到，伺服器會重新向 DHCP 重新取得 IP 位址，並重新啟動網卡，這個過程會讓原本設定的靜態路由被清除**。如果你希望靜態路由能夠在 IP 位址變更後仍然保留，可以寫增加靜態路由的一個腳本，並放入 `/usr/lib/networkd-dispatcher/routable.d` 目錄中。

```bash
#!/bin/bash

# 撰寫一個簡單的腳本，用來設定靜態路由，並放入 /usr/lib/networkd-dispatcher/routable.d 目錄
ip route add 172.16.11.13/32 via 192.168.0.1
```

當網卡重新啟動時，這個腳本會被執行，並重新設定靜態路由。
