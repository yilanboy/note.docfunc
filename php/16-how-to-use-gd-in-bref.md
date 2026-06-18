---
layout: default
parent: PHP
nav_order: 16
---

# 在 Bref (Laravel on Lambda) 使用 GD 擴充套件

> 情境：用 Bref 把 Laravel 跑在 AWS Lambda（`provided.al2023`、arm64、PHP 8.5）。
> Bref 官方 base layer **不包含 `gd`**，而 Bref 的 `extra-php-extensions` 專案**不支援 arm64**，
> 所以要在 arm64 上用 GD，只能自己 build 一個 custom layer。

本筆記涵蓋四個重點：

1. 如何自己 build 一個 PHP extension layer
2. 如何在 Lambda 使用 custom layer（是自動載入的嗎？）
3. 想在 Lambda 直接 stream 圖片要怎麼做
4. 啟用 binary response 的代價

---

## 1. 如何自己 build PHP extension layer

### 1.1 Bref custom extension 的規則

依照 [Bref 官方文件](https://bref.sh/docs/environment/php#custom-extensions)，一個 layer 解壓後會掛到 Lambda 的 `/opt`，
所以 **layer zip 的根目錄就等於 `/opt`**。檔案要照以下結構擺放：

| 檔案               | 放置位置（zip 內路徑）                                           | 說明                                                                         |
| ------------------ | ---------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| 編譯出來的 `.so`   | `bref-extra/gd.so` → `/opt/bref-extra/gd.so`                     | 擴充套件本體                                                                 |
| 啟用用的 `.ini`    | `bref/etc/php/conf.d/gd.ini` → `/opt/bref/etc/php/conf.d/gd.ini` | 內容：`extension=/opt/bref-extra/gd.so`，**檔名要唯一**避免和其他 layer 衝突 |
| 相依的 native libs | `lib/*.so` → `/opt/lib/*.so`                                     | Lambda 會自動把 `/opt/lib` 加進 `LD_LIBRARY_PATH`                            |

### 1.2 用 Bref 官方的 ARM build image

build image：[`bref/arm-build-php-85`](https://hub.docker.com/r/bref/arm-build-php-85)
（內容是 Amazon Linux 2023、arm64，PHP 工具鏈在 `/opt/bin/{php,php-config,phpize}`）。

> ⚠️ **重要踩雷點（這個 image 特有）**
>
> 1. **Image 有 Lambda entrypoint**，會把你下的指令當成 handler 吃掉。要檢查 / 進去操作時要用
>    `docker run --entrypoint bash <image> -c '...'`。
> 2. **`dnf` / `microdnf` 會 segfault**：image 把 Bref 自己的 `/opt/lib`（裡面有自訂的 `libsqlite3`）
>    放進 `LD_LIBRARY_PATH`，而 `dnf` 用 sqlite 當 rpmdb，載到這個不相容的 sqlite 就直接 crash。
>    **解法**：安裝套件時把 `LD_LIBRARY_PATH` 清掉 → `env -u LD_LIBRARY_PATH dnf -y install ...`
> 3. **一定要 bundle `.so` 的「完整」相依**：GD 連到 FreeType，而 FreeType 又會拉進
>    `libharfbuzz` / `libbrotli` / `libglib-2.0` / `libgraphite2` / `libpcre2-8` / `libbz2`，
>    這些 `provided.al2023` runtime **沒有**。只 bundle libpng/jpeg/freetype/webp 四個會在執行時失敗。
>    做法：對 `gd.so` 跑 `ldd`，把整串相依（排除 glibc 核心：libc/libm/libpthread/libdl/librt）都複製進 `/opt/lib`。

### 1.3 Dockerfile（多階段、組出 `/opt` 結構）

```dockerfile
# syntax=docker/dockerfile:1
ARG BUILD_IMAGE=bref/arm-build-php-85
FROM ${BUILD_IMAGE} AS build

SHELL ["/bin/bash", "-o", "pipefail", "-c"]

# PHP source 版本：minor 要跟 build image 一致；patch 不必完全相同
# （Zend module ABI 在同一個 minor 版本內是固定的）
ARG PHP_VERSION=8.5.7

# GD 的 native 相依。注意要清掉 LD_LIBRARY_PATH，否則 dnf 會 segfault。
RUN env -u LD_LIBRARY_PATH dnf -y install \
        libpng-devel libjpeg-turbo-devel freetype-devel libwebp-devel \
 && env -u LD_LIBRARY_PATH dnf clean all

# 用 image 內的 PHP 工具鏈，從對應版本的 PHP source 編 ext/gd
RUN set -eux; \
    cd /tmp; \
    curl -fsSL "https://www.php.net/distributions/php-${PHP_VERSION}.tar.gz" -o php.tar.gz; \
    tar xzf php.tar.gz; \
    cd "php-${PHP_VERSION}/ext/gd"; \
    /opt/bin/phpize; \
    ./configure --with-php-config=/opt/bin/php-config \
        --enable-gd --with-jpeg --with-freetype --with-webp; \
    make -j"$(nproc)"; \
    cp modules/gd.so /tmp/gd.so

# 組出 /opt layout，並 bundle gd.so 的完整 ldd 相依（排除 glibc 核心）
RUN set -eux; \
    mkdir -p /layer/bref-extra /layer/bref/etc/php/conf.d /layer/lib; \
    cp /tmp/gd.so /layer/bref-extra/gd.so; \
    ldd /tmp/gd.so \
      | awk '/=> \//{print $3}' \
      | grep -vE '/(libc|libm|libpthread|libdl|librt)\.so' \
      | sort -u \
      | xargs -I{} cp -Lv {} /layer/lib/; \
    printf 'extension=/opt/bref-extra/gd.so\n' > /layer/bref/etc/php/conf.d/gd.ini; \
    find /layer -type f | sort
```

### 1.4 build script（編譯 → 解壓 → 打包成 zip）

```bash
#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"

BUILD_IMAGE="${BUILD_IMAGE:-bref/arm-build-php-85}"
PHP_VERSION="${PHP_VERSION:-8.5.7}"
IMAGE_TAG="gd-arm-layer-builder"

# arm64：Apple Silicon 原生即可；x86_64 / GitHub ubuntu-latest 要先裝 QEMU
docker buildx build --platform linux/arm64 \
  --build-arg "BUILD_IMAGE=${BUILD_IMAGE}" \
  --build-arg "PHP_VERSION=${PHP_VERSION}" \
  --load -t "$IMAGE_TAG" docker

cid="$(docker create --platform linux/arm64 "$IMAGE_TAG")"
trap 'docker rm -f "$cid" >/dev/null 2>&1 || true' EXIT
rm -rf dist && mkdir -p dist/opt
docker cp "$cid:/layer/." dist/opt/        # dist/opt/{bref-extra,bref,lib}
( cd dist/opt && zip -qr ../gd-arm.zip . ) # zip 根目錄 = /opt
```

最後 zip 內容（實際 build 結果，共 12 個相依 lib）：

```text
bref-extra/gd.so
bref/etc/php/conf.d/gd.ini          # extension=/opt/bref-extra/gd.so
lib/libfreetype.so.6  lib/libharfbuzz.so.0  lib/libpng16.so.16  lib/libz.so.1
lib/libbrotlidec.so.1 lib/libglib-2.0.so.0  lib/libgraphite2.so.3
lib/libbrotlicommon.so.1 lib/libwebp.so.7  lib/libbz2.so.1
lib/libjpeg.so.62     lib/libpcre2-8.so.0
```

### 1.5 用 Terraform 發佈 layer

用一個獨立的 module（自己的 state），`terraform_data` 跑上面的 build script，再用
`aws_lambda_layer_version` 發佈：

```hcl
resource "terraform_data" "build" {
  triggers_replace = local.build_hash         # Dockerfile/build.sh/php_version 變了才重建
  provisioner "local-exec" {
    command     = "${path.module}/build.sh"
    interpreter = ["/usr/bin/env", "bash"]
  }
}

resource "aws_lambda_layer_version" "gd" {
  layer_name               = "gd-php-85"
  filename                 = "${path.module}/dist/gd-arm.zip"
  source_code_hash         = local.build_hash # 用「輸入」的 hash，避免 plan 時去讀還沒 build 的 zip
  compatible_runtimes      = ["provided.al2023"]
  compatible_architectures = ["arm64"]
  depends_on               = [terraform_data.build]
}

output "gd_layer_arn" { value = aws_lambda_layer_version.gd.arn }
```

`terraform apply` 後會印出 layer ARN，格式形如：

```text
arn:aws:lambda:<region>:<account-id>:layer:gd-php-85:<version>
```

> 驗證 build 是否成功（在 build image 內）：
> `php -d extension=.../gd.so -r 'var_dump(extension_loaded("gd")); print_r(gd_info());'`
> 應該看到 `JPEG / PNG / FreeType / WebP` 都是 enabled，且 `ldd gd.so` 沒有 `not found`。

---

## 2. 如何在 Lambda 使用 custom layer（是自動載入的嗎？）

要分兩個層次理解「自動」：

- **Layer 內容會自動出現在 `/opt`** —— 對。Lambda 啟動時會把所有 attach 的 layer 解壓疊到 `/opt`。
- **`.ini` 會自動被 PHP 載入** —— 對。因為它放在 Bref 會掃描的 `conf.d` 目錄，
  PHP 啟動時讀到 `extension=/opt/bref-extra/gd.so` 就會載入這個擴充套件。
- **但 layer 本身「不會自動掛到」function** —— ❌ 不會。**你必須自己把 layer ARN 加到 Lambda function 的 `layers`**。

也就是說：`.so` 不會莫名其妙被載入，是靠 layer 裡那個 `.ini` 去啟用；
而那個 layer 要不要生效，取決於你有沒有把它的 ARN 掛到 function 上。

建議讓 main module 通用化，用一個變數接受任意 extra layer：

```hcl
# variables.tf
variable "extra_lambda_layer_arns" {
  type    = list(string)
  default = []      # 預設空 → 只有 Bref PHP layer（不影響既有部署）
}

# lambda.tf —— 每個 function 都這樣寫
layers = concat([var.php_lambda_layer_arn], var.extra_lambda_layer_arns)
```

然後在 `terraform.tfvars`（或 CI workflow 產生的 tfvars）填入剛剛的 ARN：

```hcl
extra_lambda_layer_arns = ["arn:aws:lambda:<region>:<account-id>:layer:gd-php-85:<version>"]
```

部署後驗證：在 Laravel 裡（route / Tinker / artisan command）執行
`extension_loaded('gd')` 應為 `true`，`gd_info()` 可看到各格式支援；
若失敗就去 CloudWatch 看 web Lambda 有沒有 `Unable to load dynamic library` 之類缺 `.so` 的錯誤。

---

## 3. 想在 Lambda 直接 stream 圖片要怎麼做？

有兩種路線，依圖片大小 / 是否可快取來選：

### 路線 A：直接從 PHP 輸出 binary（例如 `imagepng()` 直接吐到 response）

需要設環境變數：

```bash
BREF_BINARY_RESPONSES=1
```

它讓 Bref 把 binary response 做 base64 編碼，並在回傳給 API Gateway 時帶上 `isBase64Encoded: true`。

**API Gateway 要不要設定？要看你用哪種 API：**

| API 型態                               | 是否要設 `binaryMediaTypes`                                                                                                 |
| -------------------------------------- | --------------------------------------------------------------------------------------------------------------------------- |
| **HTTP API（v2，payload format 2.0）** | **不用**。API Gateway 會依 `isBase64Encoded` 旗標自動把 base64 decode 回原始 bytes。只要設 `BREF_BINARY_RESPONSES=1` 即可。 |
| REST API（v1）                         | 要。需設 `binaryMediaTypes: ['*/*']`。                                                                                      |

> Bref 官方文件的 `binaryMediaTypes: '*/*'` 範例是針對 **REST API (v1)**。
> 如果你用的是 HTTP API (v2)，就**不需要動 API Gateway 設定**，只要加那個 env var。

通常只需把這個 env var 加在負責出 HTTP response 的 **web** function（CLI / queue 的 function 不需要）：

```hcl
# aws_lambda_function.web 的 environment
BREF_BINARY_RESPONSES = "1"
```

### 路線 B：寫到 S3，再回傳 URL / redirect（建議用於大圖或可快取的圖）

- **不需要** `BREF_BINARY_RESPONSES`。
- 沒有 6MB / 4.5MB 的限制，圖片可以遠大於此。
- 可以走 CDN / 瀏覽器快取，對效能與成本更友善。
- 常見做法是把靜態資源放 S3（例如透過 `ASSET_URL`），動態產生的圖也可比照辦理。

**選擇原則**：小張、即時產生（頭像、縮圖、動態徽章）→ 路線 A；
大張、靜態、可快取 → 路線 B。

---

## 4. 啟用 binary response 的代價

`BREF_BINARY_RESPONSES=1` 的缺點來自它是 **全域、非逐筆** 的設定：

1. **所有 response 都會被 base64 編碼**（HTML / JSON / 純文字也一樣，不只圖片）。
2. **所有 response 的大小上限變小**：base64 會膨脹約 **33%**，而 Lambda → API Gateway 的 response payload
   硬上限是 **6MB**，所以實際可回傳的內容上限從 ~6MB 掉到約 **~4.5MB**（對所有 response 都成立）。
   原本接近 6MB 的大 HTML / JSON 可能因此爆掉。
3. **每個 request 多一點 CPU / 記憶體** 花在編碼上（對純文字 response 是純浪費，通常很小但確實存在）。

**不會壞掉的部分（correctness 沒問題）**：
API Gateway v2 會在送給 client 之前把 base64 decode 回原始 bytes，
所以文字 / HTML / JSON 一樣正常往返，client 不會收到被編碼的內容。
而且只影響負責 HTTP 的 **web** function；CLI / queue 的 function 不受影響。

**結論**：代價是「**所有流量**都付出較小的 max size（~4.5MB）+ 少量編碼成本」，換來「binary response 能正常運作」。
若想完全避免這個代價，就走 **路線 B（圖片放 S3）**，不要開這個 flag。

---

## 附錄：建議的檔案結構與參考

典型的擺放方式（可依需要調整）：

- `layers/gd/` —— 獨立的 GD layer builder（Terraform module + Dockerfile + build.sh）
- `variables.tf` —— `extra_lambda_layer_arns` 變數
- `lambda.tf` —— `layers = concat(...)`、web function 的 `BREF_BINARY_RESPONSES=1`
- 發佈後的 layer ARN 形如：`arn:aws:lambda:<region>:<account-id>:layer:gd-php-85:<version>`

參考連結：

- [Bref custom extensions](https://bref.sh/docs/environment/php#custom-extensions)
- [Bref binary responses](https://bref.sh/docs/use-cases/http/binary-requests-responses)
- [Build image](https://hub.docker.com/r/bref/arm-build-php-85)
