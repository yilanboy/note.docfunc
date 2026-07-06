# Resource Hooks

K8s 本身並沒有提供類似於 Docker Compose 中 `depends_on` 的功能。因為 K8s 是宣告式的，只要寫好我們要的目標資源狀態，K8s 會自動幫我們處理每個資源的建立順序。不過有時候我們還是會需要這樣的功能，例如我想要在資料庫 pod 啟動之後，立刻執行一個資料庫初始化的指令，可能是新建資料庫或是匯入資料。

雖然 k8s 沒有提供 `depends_on` 的功能，但是我們可以透過 ArgoCD 的 Resource Hooks 來達到類似的效果。

## 使用 Resource Hooks 在不同的階段執行任務

Argo CD 提供多種 resource hooks，讓我們可以在資源建立的不同階段執行我們想做的事情。

- `PreSync`: 在資源建立之前執行。
- `Sync`：在所有 `PreSync` 操作與所有資源建立之後執行。
- `Skip`：請 Argo CD 忽略這個資源。
- `PostSync`：在所有 `Sync` 操作與所有資源建立之後執行。
- `SyncFail`：當 sync 相關操作失敗時執行。

以下是一個簡單的 resource hooks 範例：

```yaml
apiVersion: batch/v1
kind: Job
metadata:
    name: mysql-migration-job
    annotations:
        argocd.argoproj.io/hook: PostSync
spec:
    # ...
```

> Argo CD 的 resource hook 可以使用在任何資源上，但比較常見的使用場景是在 `Job` 資源上。

## Hook Deletion Policies

Argo CD 提供三種 Policy，讓我們可以在對應的階段刪除 hook 資源。

- `BeforeHookCreation`：在其他 hook 資源建立之前刪除。
- `HookSucceeded`：在 hook 資源執行成功之後刪除。
- `HookFailed`：在 hook 資源執行失敗之後刪除。

以剛剛的範例來說，如果我們想要在 `mysql-migration-job` 執行成功之後刪除這個 job，我們可以這樣寫：

```yaml
apiVersion: batch/v1
kind: Job
metadata:
    name: mysql-migration-job
    annotations:
        argocd.argoproj.io/hook: PostSync
        argocd.argoproj.io/hook-delete-policy: HookSucceeded
spec:
    # ...
```

## 參考資料

- [Resource Hooks](https://argo-cd.readthedocs.io/en/stable/user-guide/resource_hooks/)
