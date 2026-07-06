# tcpdump 使用方式

TL;DR。`tcpdump` 是 Linux 上最常用的封包擷取工具，直接在介面上抓封包並印出來，用來診斷「封包到底有沒有送出去 / 有沒有收到 / 內容長怎樣」。
診斷網路問題的第一原則是 **分段抓包**：在路徑上每一個節點都抓一次，就能定位封包是在哪一段消失的。

## 基本語法

```bash
tcpdump [選項] [過濾條件 (BPF filter)]
```

- **選項**：控制抓哪個介面、印多詳細、要不要存檔。
- **過濾條件**：用 BPF (Berkeley Packet Filter) 語法決定只看哪些封包，例如 `host`、`port`、`tcp`。

> 通常需要 `root` (或 `sudo`) 才能抓包。

## 常用選項

| 選項                  | 意思                                                              |
| --------------------- | ----------------------------------------------------------------- |
| `-i <介面>`           | 指定介面，例如 `-i eth0`；`-i any` 監聽**所有**介面               |
| `-n`                  | 不做 DNS 反解（IP 直接顯示數字）                                  |
| `-nn`                 | 連 port 也不解析成服務名（443 不顯示成 https）                    |
| `-v` / `-vv` / `-vvv` | 詳細度遞增，會多印 TTL、IP id、TCP options 等                     |
| `-e`                  | 顯示 link layer (MAC) 標頭                                        |
| `-c <數量>`           | 抓到 N 個封包就停                                                 |
| `-s <長度>`           | 每個封包擷取的位元組數，`-s 0` 表示抓完整封包（新版預設已是完整） |
| `-w <檔名>`           | 把原始封包寫入 `.pcap` 檔（之後可用 Wireshark 開）                |
| `-r <檔名>`           | 讀取先前存的 `.pcap` 檔來分析                                     |
| `-A`                  | 以 ASCII 顯示 payload（看 HTTP 等純文字協定好用）                 |
| `-X`                  | 以 hex + ASCII 顯示 payload                                       |
| `-tttt`               | 時間戳記顯示成可讀的日期時間                                      |
| `-l`                  | 行緩衝，配合 `grep` 即時過濾時用                                  |

## 過濾條件 (BPF) 速查

可用 `and` / `or` / `not` 組合，括號要加引號避免被 shell 吃掉。

```bash
# 主機
host 10.1.211.24                # 來源或目的是這個 IP
src host 10.1.211.24            # 只看來源
dst host 10.108.114.42          # 只看目的

# 網段
net 172.20.0.0/16

# port
port 443                        # 來源或目的 port 443
src port 50029
dst port 443
portrange 8000-8100

# 協定
tcp / udp / icmp / arp
proto gre                       # GRE 隧道封包

# 組合 (記得括號 + 引號)
'host 10.1.211.24 and tcp and port 443'
'tcp and (port 80 or port 443)'
'icmp and not host 10.0.0.1'

# 看 TCP flag (進階)
'tcp[tcpflags] & tcp-syn != 0'  # 只看含 SYN 的封包
'tcp[tcpflags] & (tcp-syn|tcp-ack) == tcp-syn'  # 只看純 SYN (排除 SYN-ACK)
```

## 怎麼讀輸出

以一行 TCP 封包為例：

```text
08:39:53.749161 gre203 In  IP (tos 0x0, ttl 59, id 0, ..., proto TCP (6), length 64)
    10.1.211.24.50029 > 10.108.114.42.443: Flags [SEW], cksum 0xe556 (correct),
    seq 1271223400, win 65535, options [mss 1200,...], length 0
```

| 欄位                                    | 意思                                                               |
| --------------------------------------- | ------------------------------------------------------------------ |
| `08:39:53.749161`                       | 時間戳記                                                           |
| `gre203 In` / `gre1 Out`                | 在哪個介面、方向是進 (In) 還是出 (Out)。用 `-i any` 才會顯示介面名 |
| `ttl 59`                                | TTL；每經過一個路由節點減 1，可用來確認封包有沒有被轉發            |
| `10.1.211.24.50029 > 10.108.114.42.443` | `來源IP.來源port > 目的IP.目的port`                                |
| `Flags [...]`                           | TCP 旗標（見下表）                                                 |
| `seq` / `win`                           | 序號 / 接收視窗大小                                                |
| `length 0`                              | payload 長度，0 代表純控制封包（如 SYN）無資料                     |

### TCP Flags 對照

| 縮寫 | 旗標    | 意思                             |
| ---- | ------- | -------------------------------- |
| `S`  | SYN     | 發起連線                         |
| `.`  | ACK     | 確認                             |
| `P`  | PSH     | 立即把資料交給應用層             |
| `F`  | FIN     | 正常關閉連線                     |
| `R`  | RST     | 強制重置連線（被拒絕 / 異常）    |
| `S.` | SYN-ACK | 連線回應（SYN+ACK）              |
| `E`  | ECE     | ECN-Echo（ECN 協商 / 回報壅塞）  |
| `W`  | CWR     | Congestion Window Reduced（ECN） |

> 例：`[SEW]` = SYN 並帶 ECN 協商；`[S.]` = SYN-ACK；`[R.]` = RST+ACK（連線被拒）。

## 實用範例

```bash
# 即時看某主機的 HTTPS 流量、不反解、所有介面
tcpdump -nni any host 10.1.211.24 and tcp and port 443

# 只抓 10 個封包就停，最高詳細度
tcpdump -vvv -c 10 -nni eth0 host 10.108.114.42

# 看 TCP 三向交握有沒有成功（只抓 SYN / SYN-ACK / RST）
tcpdump -nni any 'tcp[tcpflags] & (tcp-syn|tcp-rst) != 0 and host 10.1.211.24'

# 看 GRE 隧道封包本身（外層）
tcpdump -nni any proto gre

# 抓 ICMP，確認 ping 有沒有來回
tcpdump -nni any icmp and host 10.1.211.24

# 存成 pcap 檔，事後用 Wireshark 分析
tcpdump -nni any -w /tmp/capture.pcap host 10.1.211.24 and port 443

# 讀回 pcap 檔
tcpdump -nnr /tmp/capture.pcap -vvv

# 即時過濾關鍵字（記得加 -l）
tcpdump -nnl -i any port 53 | grep example.com
```

## 在公司專案的常見診斷情境

公司裡面的 FRR 邊緣節點大量使用 **GRE / VXLAN 隧道** 與 **strongSwan IPSec**，
封包會在多個介面之間被轉送（例如 `gre2 In` → `gre1 Out`），因此 `-i any` 特別有用。

```bash
# 在核心節點上確認某條連線的封包有沒有「進來又轉出去」
sudo tcpdump -vvvn -i any host <client-ip> and tcp and port <port>
```

判讀重點：

- **看得到 In 也看得到 Out** → 這台有正確轉發，TTL 應該減 1。
- **只有 SYN 出去、沒有 SYN-ACK 回來** → 去程通、回程斷（路由不對稱、對端沒在聽、或被 ACL/防火牆擋）。
- **看到 `R` (RST)** → 連線被對端或中間設備主動拒絕。
- **SYN 帶 `[SEW]` 後重送變純 `[S]`** → ECN fallback，作業系統懷疑 ECN 被中間設備丟棄而退回普通 SYN 重試。

> **分段抓包**：在「來源 → 本節點 → 對端隧道節點 → 目的端」每一段都抓一次，比對封包在哪一段消失，
> 就能精準定位是哪一跳出問題，而不是憑空猜。

## 小提醒

- `-i any` 抓到的封包在 Linux 上是 `LINUX_SLL2` (cooked) 格式，**看不到真實 MAC 標頭**；要看 MAC 請指定實體介面並加 `-e`。
- 高流量環境直接印到螢幕會洗版且可能漏抓，建議 `-w` 存檔後離線分析。
- 過濾條件下得越精準，對系統負擔越小、也越好讀；正式環境抓包請務必加足夠的 `host` / `port` 限制。
- 抓包看得到內容代表流量**未加密的外層**；TLS / IPSec 內層 payload 仍是加密的。

## 參考資料

- [tcpdump man page](https://www.tcpdump.org/manpages/tcpdump.1.html)
- [pcap-filter (BPF 語法) man page](https://www.tcpdump.org/manpages/pcap-filter.7.html)
- 相關筆記：[VXLAN](05-vxlan.md)、[BGP](02-bgp.md)、[Data Plane 與 Control Plane](08-data-plane-and-control-plane.md)
