# lsof (list open files) 指令

在 Linux 系統之下，幾乎所有的系統資源都是以檔案的形式呈現的 (包含一般檔案、目錄、連結檔、裝置檔、管線檔、網路 socket 等)

所以對於管理者來說，若要查詢一個程式使用了哪些系統資源，就可以透過 `lsof` 查看這個程式開啟了哪些檔案

`lsof` 指令可以用來查詢行程 (process) 所開啟的檔案列表，這對於系統管理者來說非常實用，以下是這個 lsof 的使用方式與範例

## 查看指令參數

```bash
lsof -h
```

## 列出所有行程

```bash
lsof
```

## 查詢開啟指定檔案的行程

查詢用 VS Code 開啟的檔案

```bash
lsof linux/lsof.md
```

顯示結果

```text
COMMAND     PID         USER   FD   TYPE DEVICE SIZE/OFF     NODE NAME
Code\x20H 84084        allen   30r   REG   1,17      730 25028666 linux/lsof.md
```

## 列出指定使用者所開啟的檔案

查詢使用者開啟的檔案

```bash
lsof -u allen
```

也可以查詢 root 開啟的檔案，但要加上 `sudo`

```bash
sudo lsof -u root
```

## 列出指定程式所開啟的檔案

```bash
lsof -c docker
```

## 根據 PID 搜尋開啟的檔案

```bash
# 查詢單一 port
lsof -p 14662

# 查詢多個 port
lsof -p 14662, 14663, 14665
```

## 查詢網路連線

列出有網路連線的行程

```bash
lsof -i
```

可以只列出 TCP 或是 UDP 的連線

```bash
lsof -i tcp
lsof -i udp
```

查詢特定 port 的使用情況

```bash
lsof -i:9000
```

顯示結果

```text
COMMAND    PID         USER   FD   TYPE             DEVICE SIZE/OFF NODE NAME
com.docke 1803        allen   92u  IPv6 0x4ddc2e7d739981cd      0t0  TCP *:cslistener (LISTEN)
```

一些其他類型的範例

```bash
# 列出 SMTP 的網路連線
lsof -i :smtp

# 列出 3642 連接埠的 TCP 連線
lsof -i tcp:3642

# 列出 1 到 1024 連接埠的 TCP 網路連線
lsof -i :1-1024

# 列出所有處於 LISTEN 狀態的 TCP 連線
lsof -i tcp -s tcp:listen
```

`lsof` 也可以搭配 `grep` 做使用

```bash
# 查詢目前使用的 port 與 process id
lsof -i | grep LISTEN

# 查詢特定的 port
lsof -n -i:80 | grep LISTEN
```

## 參考資料

- [Linux 列出行程開啟的檔案，lsof 指令用法教學與範例](https://blog.gtwang.org/linux/linux-lsof-command-list-open-files-tutorial-examples/)
