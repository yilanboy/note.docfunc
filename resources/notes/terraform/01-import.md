# Terraform Import

隨著 DevOps 概念的普及，越來越多公司都開始使用 IaC (Infrastructure as Code) 工具來管理他們日益漸多的基礎建設資源。小弟我在工作上也很常使用 Terraform 這個 IaC 工具來管理雲端資源。理由無它，不論是建立資源還是刪除資源都很方便，還能避免自己忘記刪除不必要的資源，減少支出成本。

前陣子在工作上聽到其他部門的同事需要把已經存在的資源納入 Terraform 的管理，同時還想將一個超級大的 Terraform 專案切成多個小專案。因此他們大量的使用到了 Terraform 的 `import` 指令。

我對 Terraform 的 `import` 指令並不熟悉，在好奇心的驅使下，我決定來研究一下如何使用 Terraform 的 Import 功能來引入已經存在的資源。

## 使用 `import` 指令將資源納入 Terraform 管理

假設我們想要將現有的 EC2 機器納入 Terraform 的管理，可以怎麼做呢？

首先新增一個 `main.tf` 檔案，並寫入以下內容：

```hcl
provider "aws" {
  region = "us-west-2"
}

# 新增一個沒有指定任何屬性的 aws_instance 資源
resource "aws_instance" "my_instance" {}
```

> `main.tf` 寫好之後記得輸入指令 `terraform init` 來下載相關的 Provider。

在引入資源前，我們需要先知道資源的 ID。ID 可以在 AWS 的 EC2 管理頁面上找到。假設 ID 為 `i-01f18d79e045d5074`。

有了 ID 後，我們就可以使用 `terraform import` 指令將 EC2 機器納入 Terraform 的管理：

```bash
# 將資源引入我剛剛新增的 aws_instance.my_instance 資源
terraform import aws_instance.my_instance i-01f18d79e045d5074
```

資源引入成功後，Terraform 就會產生一個 `terraform.tfstate` 檔案，用來記錄資源的詳細狀態。接著我們就可以用`terraform state show` 指令來列出 EC2 機器的所有參數：

```shell
terraform state show aws_instance.my_instance
```

指令結果會鉅細彌遺的列出 EC2 機器的所有參數。我們需要把這些屬性依序填入我們原本空白的 `aws_instance.my_instance` 資源中。避免在下次執行 `terraform apply` 時，覆寫原本 EC2 機器的設定。

## 使用 `import` 語法來一次引入多個資源

> 注意！這個功能在 Terraform 1.5 版本後才支援 。

如果想要引入的資源很多，按照上面的做法，一個一個輸入指令來引入可能會是一個很冗長的過程。幸好 Terraform 在 1.5 版本後推出了 `import` 語法，可以一次引入多個資源，大幅增加了引入資源的效率。

假設我們除了 EC2 機器外，還有 VPC 與 Subnet 想要一起引入，可以新增一個 `imports.tf` 檔案，並寫上以下內容：

```hcl
# 使用 EC2 instance ID 引入 EC2 instance
import {
  id = "i-01f18d79e045d5074"
  to = aws_instance.my_instance
}

# 使用 VPC ID 引入 VPC
import {
  id = "vpc-0d04c17717889419c"
  to = aws_vpc.my_vpc
}

# 使用 Subnet ID 引入 Subnet
import {
  id = "subnet-075d4c0ef70c4dd91"
  to = aws_subnet.my_subnet
}
```

接著使用 `terraform plan` 指令來產生資源檔案 `generated.tf`：

```shell
terraform plan -generate-config-out=generated.tf
```

指令執行後，你會看到資源檔案 `generated.tf` 是生成出來了沒錯，但同時也 …

爆出一大堆錯誤訊息！

這是因為資源的參數很可能過於複雜，Terraform 不一定可以產生完全沒問題的資源檔案。此時還是需要手動調整一下，我這次遇到的的錯誤在[官方文件](https://developer.hashicorp.com/terraform/language/import/generating-configuration#conflicting-resource-arguments)也有提到，就是資源的兩個參數互相衝突 (一山不容二虎)，只要手動刪除其中一個參數即可。

```text
│ Error: Conflicting configuration arguments
│
│   with aws_instance.my_instance,
│   on generated.tf line 15:
│   (source code not available)
│
│ "ipv6_addresses": conflicts with ipv6_address_count
```

將檔案中的衝突問題解決後，接下來執行 `terraform apply` 即可完成對資源的引入。

## 參考資料

- [Terraform Config-driven Import | Safely and securely import multiple resources to Terraform at once](https://www.youtube.com/watch?v=y8_5Ud29W8o)
-
