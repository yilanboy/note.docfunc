---
date: '2023-08-05'
updated: '2026-10-26'
tags: [aws]
---

# AWS CLI

AWS CLI 是一個用來操作 AWS 的指令列工具，可以用來建立還有管理 EC2、S3、IAM 等服務。

## 安裝

在 Mac 上可以透過 Homebrew 安裝：

```bash
brew install awscli
```

## 設定權限

### 使用 Secret Key (不推薦)

要使用 AWS CLI 操作 AWS 服務，必須要有一組 Access Key 跟 Secret Key，可以在 AWS Console 的 IAM 服務裡面建立。然後在本機上執行下面的指令設定 Key：

```bash
aws configure
```

這個指令會建立 `~/.aws/credentials` 跟 `~/.aws/config` 兩個檔案，分別用來存放 Key 跟其他設定。

### 使用 SSO (推薦)

> SSO 全稱 Single Sign-On

我們需要在 AWS 中啟用 IAM Identity Center。

1. 登入 AWS Console，前往 IAM Identity Center 服務
2. 啟用 IAM Identity Center，並完成初始設定
3. 新增 User，輸入信箱與姓名等資訊後，AWS 會寄出邀請信件請你設定密碼
4. 設定 Permission Set（例如選擇預設的 AdministratorAccess）
5. 在 AWS accounts 中，勾選你的 AWS 帳號，並將建立的 User 與 Permission Set 指派給該帳號

完成上述步驟之後，就可以在終端機輸入指令設定 SSO：

```bash
aws configure sso
```

```text
SSO session name [None]: <隨意的名稱，例如 my-sso>
SSO start URL [None]: <AWS IAM Identity Center 儀表板中顯示的 AWS access portal URL>
SSO region [None]: us-west-2
SSO registration scopes [sso:account:access]:
```

> **說明**：送出後會自動開啟瀏覽器跳轉至 SSO 登入頁面進行授權。授權成功後回到終端機，依序選擇要綁定的 AWS 帳號、Role，並自訂 Profile 名稱即可完成設定。

設定完成後，AWS 會幫你產生對應的 profile 存放在 `~/.aws/config` 中：

```toml
[profile <profile-name>]
sso_session = <sso-session-name>
sso_account_id = <sso-account-id>
sso_role_name = <sso-role-name>

[sso-session <sso-session-name>]
sso_start_url = <AWS access portal URL>
sso_region = us-west-2
sso_registration_scopes = sso:account:access
```

之後我們就可以使用 `aws sso login` 指令登入 AWS 了：

```bash
aws sso login --profile <profile-name>
```

登入後會取得有效期限通常為 1 小時的暫時性憑證（Temporary credentials），相較於長效期的 Access Key / Secret Key 更為安全。

如果你不想每次執行指令或登入時都要特地指定 `--profile`，可以將 `~/.aws/config` 中的 `[profile <profile-name>]` 改為 `[default]`：

```toml
[default]
sso_session = <sso-session-name>
sso_account_id = <sso-account-id>
sso_role_name = <sso-role-name>
```

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
