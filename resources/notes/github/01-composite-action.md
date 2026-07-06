# Composite Action

GitHub Action 是一個很方便的 CI/CD 工具，你可以用它來自動化程式部署前的多項任務，例如測試、靜態分析和排版檢查。但如果任務太多，用來描述 CI/CD 流程的定義檔案可能會變得冗長，並包含重複的部分。

舉個例子，假設我有一個 CI/CD 流程需要打包 4 個 Docker 映像檔，為了提高效率，這 4 個映像的打包會分別在不同的 job 中同時進行。這也意味著，我需要在定義檔中重複寫 4 次 Docker Hub 登入操作。

```yaml
# 超級長的 workflow 定義檔案
name: Build images

# ...

jobs:
  build-hello-1-image:
    # 登入 Docker Hub 並打包映像檔
    name: Start to build images and publish to docker hub
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Set up QEMU
        uses: docker/setup-qemu-action@v3

      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v3

      - name: Login to Docker Hub
        uses: docker/login-action@v3
        with:
          username: ${{ secrets.REGISTRY_USERNAME }}
          password: ${{ secrets.REGISTRY_PASSWORD }}

      - name: Build and push image to Docker Hub
        uses: docker/build-push-action@v5
        with:
          file: dockerfiles/hello-1.Dockerfile
          platforms: linux/amd64,linux/arm64
          push: true
          tags: |
            allen/hello-1:latest
            allen/hello-1:1.0.0
          cache-from: type=registry,ref=allen/hello-1:buildcache
          cache-to: type=registry,ref=allen/hello-1:buildcache,mode=max

  # 上面的流程改一下 image 名稱後，重複三次... 😰
  build-hello-2-image:
    # ...

  build-hello-3-image:
    # ...

  build-hello-4-image:
    # ...
```

為了避免反覆書寫相同的流程定義，GitHub Action 提供了一種叫做 Composite Action 的功能。你可以將重複的定義抽出來，形成一個獨立的 action。透過重複使用這個 action，你能夠更有效的組織你的流程定義，使檔案的內容更加精簡。

首先我們在 `.github` 資料夾中建立一個 `actions` 資料夾，這個資料夾底下可以放入各種 `action` 的定義檔案。

接下來建立一個用來登入 Docker Hub 並打包 Docker 映像檔的 action，在 `actions` 底下，我們建立一個 `build-image` 資料夾， 並在裡面建立一個 `action.yaml` 檔案，這個檔案就是我們的 action 定義檔案。

此時的資料夾結構如下：

```text
.github
└── actions
    └── build-image
        └── action.yml
```

`action.yml` 的內容如下：

```yaml
# .github/actions/build-image/action.yaml
name: Login to Docker Hub and build images

description: A GitHub Action to login to Docker Hub, then build images and publish to docker hub

# Composite Action 的輸入參數，可以用來設定 Docker Hub 的帳號密碼以及映像檔的名稱
inputs:
  registry_username:
    description: “Username for image registry”
    required: true
  registry_password:
    description: “Password for image registry”
    required: true
  file:
    description: “Path to the Dockerfile to build”
    required: true
  image_name:
    description: “Name of the image to push”
    required: true
  image_tag:
    description: “Tag of the image to push”
    required: true

# Composite Action 的執行步驟
runs:
  using: "composite"
  steps:
    - name: Set up QEMU
      uses: docker/setup-qemu-action@v3

    - name: Set up Docker Buildx
      uses: docker/setup-buildx-action@v3

    - name: Login to Docker Hub
      uses: docker/login-action@v3
      with:
        # 使用剛剛的輸入參數
        username: ${{ inputs.registry_username }}
        password: ${{ inputs.registry_password }}

    - name: Build and push image to Docker Hub
      uses: docker/build-push-action@v5
      with:
        file: ${{ inputs.file }}
        platforms: linux/amd64,linux/arm64
        push: true
        tags: |
          ${{ inputs.image_name }}:latest
          ${{ inputs.image_name }}:${{ inputs.image_tag }}
        cache-from: type=registry,ref=${{ inputs.image_name }}:buildcache
        cache-to: type=registry,ref=${{ inputs.image_name }}:buildcache,mode=max
```

可以看到 Composite Action 就好像 Terraform 的模組，你可以設定需要提供哪些參數，並在內部使用外部傳入的參數。

接下來我們修改原本的 workflow 定義檔案，把映像檔的打包步驟改為使用剛剛建立的 action：

```yaml
# .github/workflows/build-image.yml
name: Build images

# ...

jobs:
  build-hello-1-image:
    name: Start to build images and publish to docker hub
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      # 使用 Composite Action 並傳入參數
      # 流程不變，但可以少打很多行字 👍
      - name: Build and push image to Docker Hub
        uses: ./.github/actions/build-image
        with:
          registry_username: ${{ secrets.REGISTRY_USERNAME }}
          registry_password: ${{ secrets.REGISTRY_PASSWORD }}
          file: dockerfiles/hello-1.Dockerfile
          image_name: allen/hello
          image_tag: 1.0.0

  # 下面的流程也同樣使用 Composite Action，並傳入不同的輸入參數
  build-hello-2-image:
    # ...

  build-hello-3-image:
    # ...

  build-hello-4-image:
    # ...
```

上述的做法是將 action 與 workflow 放在同一個專案底下，如果想讓不同專案共用相同的 action，你可以單獨建立一個 GitHub 專案來放常常使用到的 action。

然後在 workflow 中使用 `uses` 來指定專案中的 action：

```yaml
# ...

jobs:
  hello_world_job:
    runs-on: ubuntu-latest
    name: A job to say hello
    steps:
      - uses: actions/checkout@v4

      # 使用 GitHub 上的 action
      - uses: my-github-account/reusable-actions/hello-world-composite-action@v1
```

有了 Action Composition，我們就可以將流程模組化，除了可以少寫很多行字，流程上的管理也會更輕鬆，當有流程的內容需要修改時，我們只需要修改 action 的內容即可，而不用修改每一個專案中 workflow。

## 參考資料

- [Creating a composite action](https://docs.github.com/en/actions/creating-actions/creating-a-composite-action)
