# AWS Identity and Access Management (IAM)

為 AWS 上面管理資源存取權限的全球服務，不分地區。

## 跨帳號的授予權限

如果你想要讓別人能夠存取你 AWS 帳號上的資源，最安全的做法，就是新增一個 IAM Role，並透過 Assume Role 的方式，將這個 Role 賦予給對方。對方可以透過 STS (Security Token Service) 來取得這個 Role 的暫時性憑證，並使用這個憑證來存取你的資源。

舉個例子，假設你想讓 `Development` 帳號可以讀取 `Production` 帳號底下的的 S3 資源，可以怎麼做？

首先在 `Production` 帳號中新增一個讀取 S3 Bucket 的 Policy，並命名為 `read_my_bucket`：

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": "s3:ListBucket",
            "Resource": "arn:aws:s3:::my-bucket"
        },
        {
            "Effect": "Allow",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::my-bucket/*"
        }
    ]
}
```

然後一樣在 `Production` 帳號新增一個 Role，並命名為 `delegate_read_my_bucket`，並且在 Trust Relationship 設定讓該 Role 能被 `Development` 帳號使用：

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Principal": {
                "AWS": "arn:aws:iam::<DEVELOPMENT_ACCOUNT_ID>:root"
            },
            "Action": "sts:AssumeRole"
        }
    ]
}
```

設定完成之後，將剛剛新增的 Policy 附加到 Role 上。此時帳號 `Development` 就可以透過 `AssumeRole` API 來取得一個暫時性的 `Credential`，並且使用該 `Credential` 來讀取 S3 Bucket。

> `--role-session-name` 參數取一個你喜歡的名字即可。

```bash
aws sts assume-role --role-arn arn:aws:iam::<PRODUCTION_ACCOUNT_ID>:role/delegate_s3_bucket_role --role-session-name "my-s3-session"
```

指令的結果如下：

```json
{
    "Credentials": {
        "SecretAccessKey": "wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY",
        "SessionToken": "AQoDYXdzEGcaEXAMPLE2gsYULo+Im5ZEXAMPLEe...",
        "Expiration": "2014-12-11T23:08:07Z",
        "AccessKeyId": "AKIAIOSFODNN7EXAMPLE"
    }
}
```

將上述資訊設成環境變數，就可以在 `Development` 帳號中讀取 `Production` 的 S3 Bucket 了：

```bash
export AWS_ACCESS_KEY_ID="AKIAIOSFODNN7EXAMPLE"
export AWS_SECRET_ACCESS_KEY="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"
export AWS_SESSION_TOKEN="AQoDYXdzEGcaEXAMPLE2gsYULo+Im5ZEXAMPLEe..."
```

## SAA 筆記

- IAM User 可以設定 Access Key，可以讓程式使用 Access Key 來獲得操作 AWS 資源的權限。
- 如果你的程式會運行在 EC2 或 ECS 等服務上，建議使用 IAM Role 而不是 Access Key，前者更為安全。
- AWS 帳戶與 IAM User 皆可以使用 MFA (Multi Factor Authentication) 來提升安全性。
- 可以透過 IAM Policy 強制開啟 MFA。
- AWS Security Token Service (STS) 的流程是讓 IAM User 可以透過 Assumes Role 向 IAM Role 取得暫時性的憑證 (Credential)。
- Web Identity Federation 可以讓你藉由其他廠商的 IdP (identity provider) 來取得 AWS 的使用授權 (例如透過 OpenID Connect，簡寫 OIDC)。
- AWS Single Sign-On (SSO) 是使用 STS 做一次性的身份驗證登入多個應用程式 (ex. Slack、Dropbox)。
- AWS IAM Identity Center 提供一個 SSO 入口，讓你可以連結多個服務，背後可以使用微軟 AD (Active Directory)。

## 參考資料

- [IAM tutorial: Delegate access across AWS accounts using IAM roles](https://docs.aws.amazon.com/IAM/latest/UserGuide/tutorial_cross-account-with-roles.html#tutorial_cross-account-with-roles-2)
- [小信豬的原始部落 - [AWS IAM] 學習重點節錄(3) - Assuming IAM Role](https://godleon.github.io/blog/AWS/learn-AWS-IAM-3-assuming-role/)
