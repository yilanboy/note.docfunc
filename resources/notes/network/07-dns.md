# DNS

DNS 是網域名稱系統（Domain Name System）的縮寫，它就像網際網路的電話簿，將人們易於記憶的網域名稱（例如 google.com）轉換為電腦可以理解的 IP 位址（例如 142.250.189.142）。

當您在瀏覽器中輸入網址時，DNS 會在幕後工作，將網址轉換為 IP 位址，以便您的電腦可以找到正確的網站。

## DNS 的工作流程

1. 您在瀏覽器中輸入網址。
2. 您的電腦會先向其本地 DNS 伺服器查詢該網址的 IP 位址。本地 DNS 伺服器通常是您的 ISP 提供的。
3. 如果本地 DNS 伺服器沒有該網址的 IP 位址，它會向根 DNS 伺服器查詢。根 DNS 伺服器會將查詢引導到適當的頂級域名（TLD）伺服器。例如，如果您輸入的是 .com 網址，查詢會被引導到 .com TLD 伺服器。
4. TLD 伺服器會將查詢引導到授權的 DNS 伺服器，該伺服器負責該網址的域名。
5. 授權的 DNS 伺服器會向您提供該網址的 IP 位址。
6. 您的電腦會使用 IP 位址連接到該網站的伺服器。

## DNS 紀錄

DNS 有許多不同種的紀錄，用來使用在不同的場景。

### SOA 紀錄

SOA 意思是 Start Of Authority，這個紀錄用來說明域名的管理資訊

```bash
# 使用 dig 查詢 Cloudflare DNS Server 的 SOA 資訊
dig one.one.one.one soa

# dorthy.ns.cloudflare.com. dns.cloudflare.com. 2344886388 10000 2400 604800 1800
# dorthy.ns.cloudflare.com. 為主名稱伺服器 (Primary Name Server)，負責該區域的 DNS 伺服器的名稱
# 2344886388 是序列號 (Serial Number)
```

### NS 紀錄

NS 意思是 Name Server，用來指定負責管理特定域名的權威 DNS 伺服器，需注意的是不可以 IP 位址表示。

```bash
# 使用 dig 查詢某網站的 NS 紀錄
dig docfunc.com ns

# docfunc.com.      21600   IN  NS  michelle.ns.cloudflare.com.
# docfunc.com.      21600   IN  NS  frank.ns.cloudflare.com.

# 上述的 NS 為 Cloudflare 的 Name Server
```

> 當你從域名註冊商購買到域名時，如果你想將這個域名交給其他 DNS 服務商託管，你可以在域名註冊商那邊，將域名 NS 紀錄修改為 DNS 服務商的 NS。

### A 紀錄

用來指定域名所指向的 IPv4 位址。

### AAAA 紀錄

用來指定域名所指向的 IPv6 位址。

### CNAME 紀錄

指定另外一個域名，直接套用該域名的 A、AAAA 或 TXT 紀錄。

> 擁有 CNAME 紀錄就無法另外在設定其他紀錄。

## 反向 DNS

在計算機網絡中，反向 DNS 查找或反向 DNS 解析（rDNS）是查詢域名系統（DNS）來確定 IP 位址關聯的域名的技術。反向 DNS 查詢的過程使用 PTR (Pointer Record) 記錄。網際網路的反向 DNS 資料庫植根於 `.arpa` 頂級域名。

雖然在 RFC 1912 的 2.1 節中，建議「每一個網際網路可訪問的主機都應該有一個名字」和「每一個 IP 位址都應該有一個匹配 PTR 記錄」，但這並不是一個網際網路標準強制要求，所以並不是每一個 IP 位址都有一個反向記錄。

### IPv4 反向解析

反向解析 IPv4 地址時使用一個特殊的域名 `in-addr.arpa`，如果要查詢 `8.8.4.4` 這個 IP 位址的 PTR 記錄，那麼需要查詢 `4.4.8.8.in-addr.arpa`，**注意這裡的 IP 是反過來的**。

如果 PTR 紀錄的結果指到 `google-public-dns-b.google.com` 這個域名。而 `google-public-dns-b.google.com` 的 A 記錄反過來指向 `8.8.4.4`，那麼就可以說這是個帶正向確認的反向 DNS 記錄。
