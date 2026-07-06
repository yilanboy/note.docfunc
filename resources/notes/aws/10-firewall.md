# WAF (Firewall)

AWS 相關的防火牆服務，可以對訪問服務的請求做各種過濾。

## WAF

- WAF 可以設定 rate-based rule 來過濾流量
- WAF 可以過濾 SQL injection 攻擊
- WAF 可以使用 Web ACL 過濾：
    - 特定來源 IP
    - 特定國家
    - request 的內容
- AWS Firewall Manager 簡化跨多個帳戶和資源的管理和維護任務 (包含 WAF 與 AWS Shield)
- AWS WAF 可以與 CloudFront、ALB 還有 API Gateway 一起使用

## Network Firewall

- 可以設定 domain list rule 過濾掉來自惡意 domain 的請求，作用在 HTTP 與 HTTPS 上
