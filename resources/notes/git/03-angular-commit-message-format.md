# Angular Commit Message Format

由 Angular 團隊制定的 commit message 規範。

每一個 commit message 的結構如下，其中 header 與 body 為必須要寫的，footer 可寫可不寫。

```text
<header>
<BLANK LINE>
<body>
<BLANK LINE>
<footer>
```

Commit message 的第一行為 header，其規範如下，可以根據公司內部的程式碼類型來進行調整。

```text
<type>(<scope>): <short summary>
  │       │             │
  │       │             └─> Summary in present tense. Not capitalized. No period at the end.
  │       │
  │       └─> Commit Scope: animations|bazel|benchpress|common|compiler|compiler-cli|core|
  │                          elements|forms|http|language-service|localize|platform-browser|
  │                          platform-browser-dynamic|platform-server|router|service-worker|
  │                          upgrade|zone.js|packaging|changelog|docs-infra|migrations|
  │                          devtools
  │
  └─> Commit Type: build|ci|docs|feat|fix|perf|refactor|test
```

Type 的類型說明如下：

- build: 影響構建系統或外部依賴套件的變更 (例如更新 npm 套件)
- ci: 修改 CI 配置文件和腳本（例如 GitHub Action）
- docs: 新增或是修改文件
- feat: 新增加的功能
- fix: Bug 修正
- perf: 修改程式碼以改進執行效率
- refactor: 重構程式碼，增加可讀性或方便之後擴寫
- test: 新增測試或是修正現有測試

> 注意每一個 commit 的範圍都應該盡可能小且目的明確。
> 一個 commit 如果修改了大量的檔案，不只難以進行 review，日後也很難找出有問題的 commit。

實際範例：

```text
fix($compile): couple of unit tests for IE9

Older IEs serialize html uppercased, but IE9 does not...
Would be better to expect case insensitive, unfortunately jasmine does
not allow to user regexps for throw expectations.

Closes #392
Breaks foo.bar api, foo.baz should be used instead
```

## 參考資料

- [GitHub - Contributing to Angular](https://github.com/angular/angular/blob/main/CONTRIBUTING.md#-commit-message-format)
