# 使用自己的 Proxy

部分公司較為嚴謹，內部伺服器會要求使用公司的 proxy 來對外連線，這時候就需要修改伺服器系統預設的 proxy。

我們可以在 `.bashrc` 或是 `/etc/environment` 中設定環境變數來修改 proxy，例如：

```bash
export HTTP_PROXY=http://proxy.example.com:8888
export HTTPS_PROXY=http://proxy.example.com:8888
```

但這麼做你會發現 `docker pull` 還是會失敗，**因為 Docker 的 Daemon 並不會去讀取系統的環境變數**。

我們需要在 `/etc/systemd/system/docker.service.d/` 中建立一個 `http-proxy.conf` 檔案，內容如下：

```conf
[Service]
Environment="HTTP_PROXY=http://proxy.example.com:8888"
Environment="HTTPS_PROXY=http://proxy.example.com:8888"
```

接著重新啟動 Docker Daemon：

```bash
sudo systemctl daemon-reload
sudo systemctl restart docker
```

別忘記啟動的 container 也要使用 `--env` 參數來設定環境變數，否則 container 內無法對外連線：

```bash
docker run \
--env HTTP_PROXY=http://proxy.example.com:8888 \
--env HTTPS_PROXY=http://proxy.example.com:8888 \
-it ubuntu:20.04 bash
```

## 參考資料

- [How to use proxy in Dockerfile](https://stackoverflow.com/questions/23111631/how-to-use-proxy-in-dockerfile)
