# Route53

為 AWS 販售並管理域名的服務。

## SAA 筆記

- 這是個全球服務。
- Route 53 Alias Record 可以將流量導向至 AWS 資源，背後使用 A Record (讓 FQDN 指向 IP)。
- Route 53 可以藉由 Health Check 來設定 Failover Routing Policy Configuration：
    - 要設定 NACL (Network Access Control List) 允許 Route 53 對目標發送 Health Check 的請求。
    - Route 53 的 Evaluate Target Health 要設定為 yes。
- AWS Route53 Health Checking 提供兩種 Failover 設定
    - Active-Active Failover：確保所有資源都是可行的，如果發現某資源不健康，就會停止將流量轉過去該資源
    - Active-Passive Failover：有一個 Primary 與 Secondary 資源，Secondary 資源會是 Stand By 的狀態，只有當 Primary 無法，才會將流量轉送至 Secondary 的資源。
- Route 53 有幾種 Routing：
    - Latency Routing：根據最低 Latency 提供服務，注意最低延遲不一定來自於同一區。
    - Geoproximity Routing：根據"**用戶與服務的地理位置**提供服務，且提供調整 bias，將較多或較少的流量引導到你想要的地區。
    - Geolocation Routing：根據"**用戶的地理位置**提供服務，不提供調整 bias。
    - Weighted Routing：主要用在單一域名但需要指向多個資源的情況，可以設定流量轉發的權重。可以做到跨 Region LB 或是測試新功能。
- CloudWatch Events 無法直接監控 Route 53 端點的狀態。
