---
date: '2023-04-30'
updated: '2026-11-04'
tags: [google-cloud-platform]
---

# Google Cloud CLI

紀錄 Google Cloud CLI 安裝流程。

## 安裝 Google Cloud CLI

詳細可以看[官方文件](https://docs.cloud.google.com/sdk/docs/install-sdk?hl=zh-tw#linux)，下面以我在 WSL (Windows Subsystem for Linux) 中安裝為例。

下載安裝檔案：

```bash
curl -O https://dl.google.com/dl/cloudsdk/channels/rapid/downloads/google-cloud-cli-linux-x86_64.tar.gz
```

> 連結請替換成符合你系統的版本。

進入資料夾並解壓縮，解壓縮之後會出現一個 `google-cloud-sdk` 的資料夾。

```shell
tar -xf google-cloud-cli-linux-x86_64.tar.gz
```

執行安裝 Script：

```shell
./google-cloud-sdk/install.sh
```

安裝過程會詢問各種問題，是否同意傳送匿名的報告協助他們改善用戶體練以及設定環境變數並修改你的 Shell 設定檔案 (`.bashrc` 或是 `.zshrc`)。

安裝好之後可以重新啟動終端機或是使用 `source ~/.zshrc` 來使用更新後的環境變數，這時候我們就可以使用 `gcloud` 指令了。

### 使用 Homebrew 安裝

在 MacOS 上，你可以使用 Homebrew 來安裝：

```shell
brew install --cask google-cloud-sdk
```

安裝好之後根據提示訊息，需要在 `~/.zshrc` 中加入下面這行：

```shell
source "$(brew --prefix)/share/google-cloud-sdk/path.zsh.inc"
source "$(brew --prefix)/share/google-cloud-sdk/completion.zsh.inc"
```

如果是 Bash 的話，則是在 `~/.bashrc` 加入下面這行：

```shell
source "$(brew --prefix)/share/google-cloud-sdk/path.bash.inc"
```

## 初始化 gcloud

初始化 `gcloud` 並登入帳戶：

```shell
gcloud init
```

## 設定地區 (Region)

查看可以設定的地區有哪些：

```shell
gcloud compute regions list
```

> 帳戶需要先設定付款方式才能使用。

修改地區為台灣：

```shell
gcloud config set compute/region asia-east1
```

查看地區是否修改成功：

```shell
gcloud config list compute/region
```

## 更新 CLI 元件 (Component)

查看目前的 Google Cloud CLI 有安裝那些元件：

```shell
gcloud components list
```

安裝指定元件：

```shell
gcloud components install COMPONENT_ID
```

更新所有元件：

```shell
gcloud components update
```

## 在本機生成憑證

登入並設定：

```shell
gcloud auth application-default login
```

登入之後會生成一個 `/home/<username>/.config/gcloud/application_default_credentials.json`，第三方的 Library 或是 Terraform 都會直接使用這個憑證。

如果要更換帳戶，可以撤銷原來的憑證：

```shell
gcloud auth application-default revoke
```

你也可以生成 Service Account 的憑證：

```bash
gcloud auth application-default login --impersonate-service-account="<SERVICE_ACCOUNT_EMAIL>"
```

## 參考資料

- [StackOverflow - How to change Region / Zone in Google Cloud?](https://stackoverflow.com/questions/45125143/how-to-change-region-zone-in-google-cloud)
- [Google Cloud 亞太地區據點](https://cloud.google.com/about/locations?hl=zh-tw#asia-pacific)
