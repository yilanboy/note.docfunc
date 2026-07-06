# Auto Scaling Group (ASG)

- ASG 的 Policy 種類：
    - **Target tracking scaling policy**：允許你指定目標值，ASG 將盡力保持 EC2 實例數量以滿足這個目標值。例如平均 CPU 使用率維持在 50%，或是平均網路流量維持在 100MB。
    - **Simple scaling policy**：最基本的自動擴展策略。它根據 CloudWatch 指標的閾值進行擴展和縮減。例如 CPU 使用率超過 50% 時，擴展 2 台機器，CPU 使用率低於 20% 時，縮減 1 台機器。
    - **Step scaling policy**：允許你在多個指標範圍定義的區域進行擴展和縮減，以應對不同負載情境。例如 CPU 50% 時，擴展 2 台機器，CPU 80% 時，擴展 4 台機器。
    - **Scheduled scaling policy**：如果你早就知道什麼時候會有較大的流量，可以使用此策略，在大流量的時間點前擴展機器。
    - **Predictive scaling policy**：如果你想讓 AWS 使用機器學習，根據過去幫你預測未來流量，可以使用此策略。
- ASG 在做 scale in 時，會優先關掉**啟動設定檔最舊**的機器，如果是在 Multi-AZ 的架構下，會優先選擇擁有最多機器的 AZ。
- ASG 在一次 scale in/out 活動之後會有一段冷卻期 (cool down period)，冷卻期內即使達到 scale in/out 條件，ASG 也不會有任何動作。冷卻期的預設時間是 300 秒。
- 可以使用 CloudWatch Alarm 來觸發 ASG 的 scale in/out。
- ASG 定義好 launch template 後就無法在修改，只能新增一個新的。
