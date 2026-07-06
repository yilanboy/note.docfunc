# Service Principal

## 使用 Azure CLI 建立一個 Service Principal 給 Terraform 部署資源

> 必須先安裝 Azure CLI

```bash
az ad sp create-for-rbac --name terraform --role Owner --scopes /subscriptions/<subscription_id>
```

之後將產生的 app id 與 secret key 放到環境變數中，可以在 `.bashrc` 中設定

```bash
export ARM_SUBSCRIPTION_ID="<azure_subscription_id>"
export ARM_TENANT_ID="<azure_subscription_tenant_id>"
export ARM_CLIENT_ID="<service_principal_appid>"
export ARM_CLIENT_SECRET="<service_principal_password>"
```

要刪除 service principal，可以使用下方的指令

```bash
az ad sp delete --id <object_id>
```

## 參考資料

- [Configure Terraform in Azure Cloud Shell with Bash](https://learn.microsoft.com/en-us/azure/developer/terraform/get-started-cloud-shell-bash?tabs=bash)
- [Azure | Creating an Azure Service Principal using Azure Portal](https://www.youtube.com/watch?v=Kf1Tai_BkWU)
- [Application and service principal objects in Azure Active Directory](https://learn.microsoft.com/en-us/azure/active-directory/develop/app-objects-and-service-principals)
