# Project

在 GCP 中，如果要建立資源，就需要先建立一個 project (專案)，你可以將你的資源建立在專案中

## 建立專案

專案的名稱可以隨意自訂，不過 project id 必須是全球唯一性 (globally unique)，建立之後還會獲得一組 project number

> project id 在建立後不能修改

可以使用 gcloud CLI 來建立 project

```shell
gcloud projects create my-project
```

建立完成之後可以嘗試使用 `gcloud projects list` 指令查看目前建立了哪些 project

```shell
$ gcloud projects list

PROJECT_ID              NAME                    PROJECT_NUMBER
container-platform-123  container-platform      123456789012
```

## 切換專案

你可以透過 gcloud CLI 來切換專案。

```shell
gcloud config set project $MY_PROJECT_ID
```

## 刪除專案

你可以使用 gcloud CLI 刪除專案

```shell
gcloud projects delete my-project
```

但要注意刪除專案為軟性刪除 (soft deleting)，在**30 天之內**你都可以恢復 (restore) 已刪除的專案，可以上 GCP 主控台操作，也可以使用 gcloud CLI 的 restore 指令

```shell
gcloud projects restore my-project
```

專案在刪除後需要等待 30 天之後才會完全刪除，並沒有辦法立即刪除

此外 project id，**即使是在系統刪除專案之後，也無法重新使用**，假設你想保留 project id，你應該避免將專案刪除，而是只刪除 project 底下的資源

## 參考資料

- [Creating and managing projects](https://cloud.google.com/resource-manager/docs/creating-managing-projects)
