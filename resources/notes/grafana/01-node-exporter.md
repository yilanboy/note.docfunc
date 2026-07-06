# Use Node Exporter to monitor the host

在主機上安裝 Node Exporter，以便 Prometheus 可以收集主機的監控資料。

## Install Node Exporter

新增一個 node-exporter 使用者，並且不要建立家目錄，也不要讓他可以登入。

```bash
sudo useradd --no-create-home --shell /bin/false exporter
```

下載 Node Exporter，解壓縮，並且複製到 /usr/local/bin/ 目錄下。

```bash
wget https://github.com/prometheus/node_exporter/releases/download/v1.6.0/node_exporter-1.6.0.linux-arm64.tar.gz

tar xvfz node_exporter-1.6.0.linux-arm64.tar.gz

sudo cp node_exporter-1.6.0.linux-arm64/node_exporter /usr/local/bin/

sudo chown node-exporter:node-exporter /usr/local/bin/node_exporter

rm -rf node_exporter-1.6.0.linux-arm64.tar.gz node_exporter-1.6.0.linux-arm64
```

## Create Node Exporter Service

建立一個 service 檔案，讓 Node Exporter 可以在背景執行。

```bash
sudo touch /etc/systemd/system/node-exporter.service

sudo vim /etc/systemd/system/node-exporter.service
```

Service 檔案內容如下：

```ini
[Unit]
Description=Node Exporter

[Service]
User=exporter
ExecStart=/usr/local/bin/node_exporter --web.disable-exporter-metrics
Restart=always

[Install]
WantedBy=multi-user.target
```

`--web.disable-exporter-metrics` 參數是為了避免 Node Exporter 收集自己軟體本身的監控資料，
會移除 `go_*`、`process_*` 與 `promhttp_*` 等開頭的 Metrics 資訊，詳細可以參考這這個 [PR 討論串](https://github.com/prometheus/node_exporter/pull/1148)。

```bash
sudo systemctl daemon-reload

sudo systemctl start node-exporter.service
```

## Test Node Exporter

```bash
curl localhost:9100/metrics
```

## 參考資料

- [Monitoring a Linux host using Prometheus and node_exporter](https://grafana.com/docs/grafana-cloud/quickstart/noagent_linuxnode/)
- [教學課程：在 Amazon Lightsail 中於以 Linux 為基礎的執行個體上安裝 Prometheus](https://lightsail.aws.amazon.com/ls/docs/zh_tw/articles/amazon-lightsail-install-prometheus)
- [How To Configure a Linux Service to Start Automatically After a Crash or Reboot – Part 1: Practical Examples](https://www.digitalocean.com/community/tutorials/how-to-configure-a-linux-service-to-start-automatically-after-a-crash-or-reboot-part-1-practical-examples)
- [鳥哥私房菜 - 服務管理與開機流程管理](https://linux.vbird.org/linux_basic_train/centos7/unit13.php)
- [Add --web.disable-exporter-metrics flag](https://github.com/prometheus/node_exporter/pull/1148)
