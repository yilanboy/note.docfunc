# Azure Managed Identity

當應用程式需要訪問 Azure 資源時，經常需要一組認證，例如使用者名稱和密碼、憑證等等。然而，管理和存儲這些認證可能會帶來風險，例如認證資料外洩、密碼不當管理等等。為了解決這些問題，Azure 提供了 Managed Identity 服務

Azure Managed Identity 可以為應用程式提供一個自動化的管理識別解決方案。Managed Identity 可以讓應用程式在訪問 Azure 資源時，自動以 Azure AD 為基礎建立身份識別

應用程式使用 Managed Identity 可以完全避免在程式碼中明確地存儲認證，從而減少憑證管理上的風險

在 Azure 中，有兩種 Managed Identity

- 系統管理的 managed identity (system assigned managed identity)
- 用戶端管理的 managed identity (user assigned managed identity)

## System Assigned Managed Identity

假設我在 azure 上啟動了一個 A 資源，我可以幫 A 資源開啟 system assigned managed identity，azure 會自動幫我在 azure ad 建立一個身份，並把這個身份與 A 資源綁定在一起

如果我想要讓 B 資源被 A 資源給存取，只需要在 B 服務的權限設定中，將 A 服務的 system assigned managed identity 加入即可

### Example

例如我建立一個 azure function app，我想要讓 function app 獲取能夠輸入資料到 azure data explorer database 的權限，那我需要這麼設定

- 在 azure function 頁面上，點選側邊欄的 **Settings -> Identity**，**將 System assigned managed identity 給開啟**
- 到 azure data explorer 的 database 頁面上，點選 **Overview -> Permissions**，點選上方的 **Add**，選擇 Ingestor 角色，並加入 **function app managed identity**

Python SDK 的範例程式碼

```python
# https://learn.microsoft.com/zh-tw/azure/azure-functions/functions-event-grid-blob-trigger?pivots=programming-language-python
# https://learn.microsoft.com/zh-tw/python/api/azure-functions/azure.functions.blob.inputstream?view=azure-python
import logging

import azure.functions as func
from azure.kusto.data import DataFormat, KustoConnectionStringBuilder
from azure.kusto.ingest import (IngestionProperties, QueuedIngestClient,
                                StreamDescriptor)


def ingest_blob_to_data_explorer(blob_stream):
    ingestion_client_url = "https://ingest-adx-cluster.eastus.kusto.windows.net"

    # use managed identity to get the ingestion permission
    kcsb = KustoConnectionStringBuilder.with_aad_managed_service_identity_authentication(
        ingestion_client_url)
    ingestion_client = QueuedIngestClient(kcsb)

    # All ingestion properties are documented here:
    # https://learn.microsoft.com/azure/kusto/management/data-ingest#ingestion-properties
    ingestion_properties = IngestionProperties(
        database="TestDatabase",
        table="TestTable",
        data_format=DataFormat.JSON,
        ingestion_mapping_reference="TestMapping"
    )

    stream_descriptor = StreamDescriptor(blob_stream)

    ingestion_client.ingest_from_stream(
        stream_descriptor=stream_descriptor,
        ingestion_properties=ingestion_properties
    )

    logging.info("Done queuing up ingestion with Azure Data Explorer")


# parameter 'myblob' will set in 'function.json' file
def main(myblob: func.InputStream):

    ingest_blob_to_data_explorer(myblob)

    logging.info(f"Python blob trigger function processed blob \n"
                 f"Name: {myblob.name}\n"
                 f"Blob Size: {myblob.length} bytes")
```

## Use Curl to Get Token in Azure VM

Azure VM 可以建立一個 Managed Identity，只要在其它服務給予 VM 的 Managed Identity 權限。
就可以在 VM 中取得 API Token，然後呼叫 Azure API 來取得其他服務的資源。

```bash
# azure service
export SCOPE = "https://management.azure.com"
# key vault service
export SCOPE = "https://vault.azure.net"

# get api token
curl --header "Metadata: true" -X GET "http://169.254.169.254/metadata/identity/oauth2/token?api-version=2018-02-01&resource=${SCOPE}"

export API_TOKEN = '...'

# use api token to call azure api
curl -H "Accept: application/json" -H "Authorization: Bearer ${API_TOKEN}" -X GET "https://datacenter-hlcqygfouf.vault.azure.net/secrets/sdz-vpn-tunnel-psk/74050e1725584f449fe81d14b2e2b779?api-version=7.4"
```

## References

- [Unable to create Azure Data Explorer linked service in Azure Data Factory](https://learn.microsoft.com/en-us/answers/questions/758303/unable-to-create-azure-data-explorer-linked-servic)
