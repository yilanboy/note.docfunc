# CloudTrail

- 紀錄 AWS API 的呼叫紀錄，並且可以將紀錄送至 S3 或是 CloudWatch
- 預設會對 Log 加密
- Event 有兩種
    - Management event：管理事件，例如設定安全性的 IAM AttachRolePolicy API 操作。也包含非 API 的事件，例如登入 AWS Console
    - Data event：資料事件，例如對 S3 Bucket 內資料的操作，例如呼叫 GetObject、DeleteObject 或是 PutObject 的 API
