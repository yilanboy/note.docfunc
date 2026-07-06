# PDM

PDM 是一個新興的 Python 套件管理工具，它提供了一個現代化的方式來管理 Python 套件

> PDM 的作者想要讓 Python 也能有使用像 Node.js 那樣的套件管理方式，並提出了 PEP 582，只可惜被 Python 社群否決了。
> 因此目前還是使用虛擬環境的方式來管理套件。

PDM 使用 [PEP 582](https://peps.python.org/pep-0582/) 來解析套件依賴關係，這個方法在解析時比其他套件管理工具更加快速和準確

PDM 使用 `pyproject.toml` 來定義項目配置，並支援許多增強功能，如自定義指令和配置選項

## 安裝 PDM

可以使用 pip 安裝 PDM

```bash
pip install pdm
```

在 Mac 上可以使用 HomeBrew 安裝 PDM

```bash
brew install pdm
```

## 初始化專案

初始化一個 python 專案

```bash
pdm init
```

初始化會詢問一些問題，例如 python interpreter 版本，以及要使用虛擬環境還是 PEP582 來管理依賴套件

```text
Creating a pyproject.toml for PDM...

Please enter the Python interpreter to use
0. /opt/homebrew/bin/python3.11 (3.11)
1. /opt/homebrew/bin/python3.10 (3.10)
2. /usr/bin/python3 (3.9)
3. /opt/homebrew/Cellar/python@3.11/3.11.2_1/Frameworks/Python.framework/Versions/3.11/bin/python3.11 (3.11)
Please select (0): 0
Using Python interpreter: /opt/homebrew/bin/python3.11 (3.11)

Would you like to create a virtualenv with /opt/homebrew/opt/python@3.11/bin/python3.11? [y/n] (y): n
You are using the PEP 582 mode, no virtualenv is created.
For more info, please visit https://peps.python.org/pep-0582/

Is the project a library that is installable?
A few more questions will be asked to include a project name and build backend [y/n] (n):

License(SPDX name) (MIT):

Author name (allen):

Author email (allen@example.com):

Python requires('*' to allow any) (>=3.11):

Changes are written to pyproject.toml.
```

初始化完成之後會出現 `pyproject.toml` 檔案，專案依賴的套件會紀錄在這裡，你也可以在這裡自定義指令

```toml
[tool.pdm]

[project]
name = ""
version = ""
description = ""
authors = [
    {name = "allen", email = "allen@example.com"},
]
dependencies = []
requires-python = ">=3.11"
license = {text = "MIT"}
```

## 選擇直譯器

雖然在初始化專案時，PDM 就會請你選擇 Python 直譯器，但如果你日後要切換直譯器版本的話，可以使用下面的指令

```bash
pdm use /opt/homebrew/bin/python3.11
```

## 安裝套件

可以透過以下指令幫專案安裝新的依賴套件

```bash
pdm add requests
```

安裝完成之後，會在專案目錄底下新增一個 `__pypackages__` 資料夾用來存放依賴的套件

可以使用 `tree -L 3 .` 指令查看此時的目錄結構

```text
.
├── __pypackages__
│   └── 3.11
│       ├── bin
│       ├── include
│       └── lib
├── pdm.lock
└── pyproject.toml

6 directories, 2 files
```

> 注意 `__pypackages__` 這個資料夾不應該加入版本控制，請在 `.gitignore` 中將此資料夾排除

此時 `pyproject.toml` 的 `dependencies` 內容也會一起更新

```toml
[project]

# ...

dependencies = [
    "requests>=2.28.2",
]

# ...
```

如果 `pyproject.toml` 檔案已經存在，可以使用下方的指令安裝依賴套件

```bash
pdm install
```

## 更新套件

可以使用下方的指令更新套件

```bash
pdm update
```

## 使用 PDM 執行 Python 程式

新增一個 `src/main.py` 檔案

```python
import requests

response = requests.get('https://api.github.com/events')

# <Response [200]>
print(response)
```

如果直接使用 python 執行會出現找不到 `requests` 套件的錯誤

```bash
# error: ModuleNotFoundError: No module named 'requests'
python3 src/main.py
```

我們可以使用 PDM 執行該程式

```bash
pdm run python3 src/main.py
```

## 設定 Scripts

我們可以在 `pyproject.toml` 新增自己定義的指令，以剛剛 `pdm run python3 src/main.py` 為例子

```toml
[tool.pdm.scripts]
start = "python3 src/main.py"
```

此時我們就可以將剛剛的 `pdm run python3 src/main.py` 簡寫成

```bash
pdm run start
```

## 整合 IDE

因為 PEP 582 算是比較新的標準，如果沒有做設定的話，你在寫程式時，IDE 會顯示找不到套件的錯誤，也無法使用 autocomplete 的功能

官方文件有提供與各種套件整合的說明，詳細可以參考此[連結](https://pdm.fming.dev/latest/usage/pep582/)

### VSCode

PDM 可以安裝插件，如果要與 VSCode 整合，我們可以先安裝 PDM 的 `pdm-vscode` 插件

```bash
pdm plugin add pdm-vscode
```

這樣在使用 `pdm init` 初始化專案，或是使用 `pdm use` 選擇 python 直譯器時，就會生成一個 `.vscode/settings.json` 檔案

```json
{
    "python.analysis.extraPaths": [
        "${workspaceFolder}/__pypackages__/3.11/lib"
    ],
    "python.autoComplete.extraPaths": [
        "${workspaceFolder}/__pypackages__/3.11/lib"
    ]
}
```
