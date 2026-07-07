# Git

Git 就好比遊戲中的存檔機制，讓我們可以儲存未完成的程式碼，保留開發進度。也就是所謂的**版本控制（Version Control）**。

## Git 的本質：內容定址儲存 (Content Addressable Storage)

當我們在一個專案中使用 `git init` 指令，就會生成一個 `.git` 資料夾，此後專案中所有的變更、歷史紀錄和結構都藏在這個隱藏資料夾裡，刪掉它 Git 就會停止追蹤。

### Key-Value 機制

Git 會對檔案進行雜湊（Hash）並回傳一個 40 個字元的雜湊值（Key）。只要內容相同，雜湊值就相同。

兩大優勢：

- 不重複儲存： **同樣內容的檔案**就算有多個，而且放在不同資料夾，Git 也**只會儲存一份實體**。
- 防毀損： 硬碟只要有一個位元（bit）出錯，雜湊值就會對不上，Git 能立刻發現資料損毀。

### 三大核心物件類型 (Git Objects)

Git 在底層主要靠這三種物件來建構你的專案世界：

- 📄 Blob（原始檔案）： 只儲存檔案的「原始二進位內容」，不包含檔名、路徑或時間戳記。
- 📁 Tree（目錄/樹狀物件）： 解決 Blob 沒有檔名的問題。它的本質是一個文字清單，記錄了「檔案模式、物件類型（Blob 或另一個 Tree）、雜湊值、實際檔名」。透過 Tree 指向 Tree，就能模擬出專案的資料夾層級。
- 🕒 Commit（提交物件）： 記錄歷史的文本。裡面包含「根目錄 Tree 的雜湊值」、「前一次 Commit 的雜湊值（Parent）」、作者、時間戳記和提交訊息。

我們可以使用 `git cat-file -p <commit_hash|tree_hash>` 查看 Commit 物件與 Tree 物件的內容。

Commit 是一個很簡單的檔案，會記錄 Tree 的作者與時間戳。

```bash
# 查看 Commit
git cat-file -p 680c28f
# tree 7aab1b525b942b7acde1b2e559d4423b97df88cc
# parent 7b0f9e97e2020fe0520d48097e4c72ea63735b53
# author Allen <allen@example.com> 1783327951 +0800
# committer Allen <allen@example.com> 1783327951 +0800

# feat: use own static site
```

Tree 是一個列表，它會記錄檔案的模式、物件類型、雜湊值與實際檔名。

```bash
# 查看 Tree
git cat-file -p 7aab1b5
# 100644 blob 094007b9aa58566503612acc15122617f75634a9    .editorconfig
# 100644 blob c0660ea143a7a23e6a182cbe42dbd7fc6e242b45    .env.example
# 100644 blob fcb21d396d657f597ef8b6729f73d89b0a871c9b    .gitattributes
# 040000 tree f35909f25faf0f9530834e7501dd7a9b2647863f    app
```

> 因為每一個 Commit 的雜湊值都是用父 Commit 計算出來的，所以修改父 Commit 的檔案，會導致後續所有 Commit 都要重新計算雜湊值。

> 💡 重要觀念導正： Git 儲存的不是每次變更的補丁（Diffs），而是每次提交時整個專案的完整快照（Snapshot）。你看到的 Diff 都是 Git 在你查看時「即時運算」出來的。

### 指標與分支的真相 (Pointers & Branches)

分支（Branch）不是平行的時空： 在 Git 中，一個分支其實只是一個 41 位元組的文字檔，裡面只寫著一行它所指向的 Commit 雜湊值。

**HEAD（目前位置）**： 決定你工作目錄（Working Directory）長什麼樣子的檔案，它通常指向你目前所在的分支。

**快進合併 (Fast-Forward Merge)**： 當兩個合併分支沒有分叉時，Git 不會建立任何新物件，它只是單純把 Main 分支檔案裡的雜湊值覆蓋成 Feature 分支的雜湊值，讓指標「滑」過去而已。

**衝突合併 (Merge Commit)**： 當歷史分叉時，Git 會找出共同祖先並融合出一個新的快照，這個 Merge Commit 的特殊之處在於它擁有 兩個 Parent 指標。

**分離頭部 (Detached HEAD)**： 當你 Checkout 到某個具體的 Commit 而不是分支時，HEAD 就會直接指向該 Commit 雜湊值。

### 常用指令的底層運作

一旦理解了物件與指標，那些原本很抽象的指令就變得很好理解：

#### Git Add & Commit：

- `git add` 會立刻把檔案做成 Blob 存入物件庫，並更新暫存區（Index）。
- `git commit` 則是把暫存區打包成 Tree 物件，外層套上 Commit 物件，最後把分支指標往前移。

#### Git Reset（移動指標）：

`git reset` 本質上就是移動目前分支的指標到某個舊 Commit。後面的參數只是決定要不要動到暫存區與工作目錄：

- `--soft`：只動指標，保留暫存區與工作目錄。
- `--mixed`（預設）：動指標、更新暫存區，保留工作目錄。
- `--hard`：指標、暫存區、工作目錄全部同步回舊狀態，未提交的修改會被抹除。

#### Git Rebase（重新定基）：

Git 物件是不可變（Immutable）且唯讀的。Rebase 並不是真的去「移動」舊的 Commit，而是讀取舊 Commit 的變更，並在新的基底上複製並建立全新的 Commit 物件（產生全新雜湊值），舊的 Commit 依然完好地留在原地。

#### ⚠️ 最強後盾 Reflog：

因為 Git 是唯讀且採取「只增不減（Append-only）」的模式，即使你用了看起來很危險的 `git reset --hard` 或搞砸了 `rebase`，原本的 Commit 並沒有不見，它們依然躺在資料庫裡。只要透過 `git reflog` 查詢 `HEAD` 過去 90 天的移動紀錄，隨時都能把資料找回來！

## 參考資料

- [Stop Memorizing Git Commands. Learn The Data Model](https://www.youtube.com/watch?v=Csd4lMKPC5g)
