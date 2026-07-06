# Simple Notification Service (SNS)

- SNS 是一個全託管的消息訂閱服務，訂閱者可以是 AWS SQS、Lambda、HTTP endpoint、email address 或是 mobile SMS (簡訊)。
- 使用 Pub/Sub 模式，想要接收消息請訂閱 (Subscription)。
- SNS 可以做到 fan out，也就是當 publisher 產生消息時，SNS 會將其複製並推送給所有的訂閱者。
- SNS 可以設定 filter policy，讓訂閱者只接收他們感興趣的消息。
