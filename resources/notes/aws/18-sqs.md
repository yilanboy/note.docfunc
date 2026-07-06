# Simple Queue Service (SQS)

- SQS 是一個全託管的服務，用來解耦服務間的依賴性 (例如一定要等到什麼工作完成才能繼續下一個工作)。
- 如果希望 premium 用戶的任務可以優先處理，建議是開兩個 SQS 佇列，一個給 premium 用戶，另外一個是一般用戶，premium 用戶的任務佇列空了才處理一般用戶的任務佇列。
- SQS Long polling 是指 consumer 可以等 SQS 佇列有訊息後再拉取，此外可以設置最長等多久。
- Long polling 可以有效減少 API 的請求次數。
- 可以設定 `ReceiveMessageWaitTimeSeconds` 來設定 long polling。
- SQS 上面的訊息，可以放置 1-14 天，其中 4 天是預設值。
- 佇列消息數量限制：單個 Amazon SQS 佇列可以包含無限數量的消息。這表示你可以將大量的消息發送到同一個佇列中。
- 對於標準佇列（standard queue），同時處於 "inflight" 狀態的消息數量上限為 120,000 條。
    - 所謂 "inflight" 指的是這些消息已經被接收到並由消費者元件處理，但尚未從佇列中刪除。
- 先進先出類型的佇列（FIFO queue），"inflight" 上限為 20,000 條。
- 在 CloudWatch 上可以監測 `ApproximateAgeOfOldestMessage` 狀態。
- SQS 的訊息大小，最大可以設定為 256KB。

## 當作 Lambda 的事件來源

SQS 可以當作 Lambda 的事件來源，當我們向 SQS 送入新的訊息時，接著觸發 Lambda 去處理新送入的訊息。

> Lambda 有可能重複處理 SQS 的訊息，因此要確保你的 Lambda 函式是幂等的。即多次執行相同的操作，結果都是一樣的。

如果你想要控制 Lambda 處理 SQS 訊息的最大並行數量，有兩種方式可以設定：

1. 在 Lambda 上面設定 `Reserved Concurrency`。假設 Lambda 數量已達到上限，新的 SQS 訊息會被保留在佇列中，直到有空出來的 Lambda 可以進行處理。但有個問題是，還沒被處理的 SQS 的訊息有可能會超時。超時的 SQS 訊息會被放入到死信佇列中 (Dead Letter Queue)。
2. 在 SQS 的 Event Source 設定 `Maximum Concurrency`。這個設定與上面雷同，差別在於訊息會被保留在 SQS 佇列中，直到有空出來的 Lambda 可以進行處理。不會有超時的問題。

在 Terraform 的寫法中，可以透過 `scaling_config` 方式來設定 `Maximum Concurrency`：

```hcl
resource "aws_lambda_event_source_mapping" "event_source_mapping" {
  event_source_arn = aws_sqs_queue.my_queue.arn
  enabled          = true
  function_name    = aws_lambda_function.my_function.arn
  batch_size       = 1

  scaling_config {
    maximum_concurrency = 20
  }
}
```

## 參考資料

- [Introducing maximum concurrency of AWS Lambda functions when using Amazon SQS as an event source](https://aws.amazon.com/tw/blogs/compute/introducing-maximum-concurrency-of-aws-lambda-functions-when-using-amazon-sqs-as-an-event-source/)
