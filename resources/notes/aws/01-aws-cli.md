# AWS CLI

AWS CLI 是一個用來操作 AWS 的指令列工具，可以用來建立還有管理 EC2、S3、IAM 等服務。

## 安裝

在 Mac 上可以透過 Homebrew 安裝：

```bash
brew install awscli
```

## 設定權限

要使用 AWS CLI 操作 AWS 服務，必須要有一組 Access Key 跟 Secret Key，可以在 AWS Console 的 IAM 服務裡面建立。然後在本機上執行下面的指令設定 Key：

```bash
aws configure
```

這個指令會建立 `~/.aws/credentials` 跟 `~/.aws/config` 兩個檔案，分別用來存放 Key 跟其他設定。

## S3

列出所有 S3 bucket:

```bash
aws s3 ls
```

從雲端上下載檔案可以使用 `cp` 指令，例如下載檔案到本機上:

```bash
aws s3 cp s3://bucket-name/file-name .
```

上傳檔案到雲端:

```bash
aws s3 cp <file-name> s3://bucket-name
```

可以使用 sync 指令同步本機跟雲端的檔案，例如下面的指令會把本機上的檔案同步到雲端:

```bash
aws s3 sync <file-name> s3://bucket-name
```

也可以同步雲端上面的兩個 bucket:

```bash
aws s3 sync s3://bucket-name1 s3://bucket-name2
```
