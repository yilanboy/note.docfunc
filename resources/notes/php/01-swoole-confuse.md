# Swoole 的疑惑

某次部落格的文章中，有訪客留言詢問一個問題：

「都有 Swoole，OPcache 還需要嗎？」

這個問題我並不是非常理解，但我認知上 Swoole 與 OPcache 是兩個不同的東西。
但是我也不是很確定，所以我上 Facebook 的 PHP 社群詢問，得到很多高手回答。

這個筆記整理各位高手的回答，也希望自己可以以此當作學習的起始點，去找尋回答中自己不知道的知識點並延伸學習

## 回答重點整理

OPcache 扮演的角色是在 interpret 過程中將 compile 成 opcode 的結果 cache 起來達到加速的目的。

Swoole、RoadRunner 或是 ReactPHP 這種 Server 運行模式是將程式常駐執行，避免每次請求需要重新載入重複的檔案與程式初始化來加速。

這兩種優化的方向是不同的，也互不衝突，至於 OPcache 有沒有效果是另外的事了，原則上開啟 OPcache 是能加速的，但會視你的機器、程式與 OPcache 設定而有不同的結果。

延伸思考題：那 JIT 對於 Swoole 模式下執行的程式是有幫助的嗎？

---

如果在 swoole, roadrunner, reactphp, amphhp 的架構下，且在服務沒有被重啟的狀況下 OPcache 應該只有第一次初始化會有幫助。

延伸思考題：所以 swoole 的架構下需要打開 JIT 嗎？

---

在 worker 沒有重啟的前提下 OPcache 的確只會發生在初始化的請求 (或是運行中有動態載入其他未初始化的 PHP 檔案時)，而 interpret 過的 opcode 就會一起被常駐在記憶體中，但通常為了防止 memory leak，都會將 worker 設計為執行 N 次請求後重新啟動，因此 OPcache 基本上還是會持續有作用。

而 JIT 則是基於 opcode 在背後進行 hot code detect 和 JIT compile，將部分 opcode 編譯為 machine code 來加速 CPU bound 的程式，但由於 JIT 流程本身需要一定程度的 CPU 開銷，所以使用錯誤的情況下有可能讓效率變得比原本更差，但通常對於 CPU 密集型的程式有顯著的加速效果。

所以在 Swoole 中開不開 JIT 也是看情境而決定，會特別這樣問是曾經聽過有人會誤解成：Swoole 本身是 C++ extension，所以 JIT 本身沒有額外作用。這是錯誤的迷思，因為只有 Swoole 提供的相關功能才是用 C++ 寫的，業務邏輯本身還是 PHP Code，而業務邏輯的 PHP Code 使用了 JIT 後會不會加速就端看情境了 (因為大部分的 web 情境下其實 IO 的比重來的高很多)。

---

OPcache 是省掉 parse script 這一段

JIT 是省掉 zend vm 這一段。

在比較長的生命週期的系統裡，程式負載高的部份應該都會一直存活著。OPcache 和 JIT 大部份都幫不上忙，所以 OPcache 或 JIT 開不開在效能上不會有太顯著的差異

---

但 JIT 就你所說的 CPU bound 會有顯著的效能差異。但要真的讓它有顯著差異會有幾個條件

1. PHP code 必須用強型別的方式來撰寫
2. 拿 PHP 來寫 CPU bound 的程式

第一個條件沒達到 JIT 也快不起來

---

worker 重啟次數越少 OPcache 的幫助的確是越有限，但 JIT 對於 CPU 密集場景下的幫助還是比較顯著的。

在有型別宣告的前提下能讓 JIT 在型別推斷上更有效率，但也不代表沒有型別宣告下 JIT 會完全沒作用，JIT 還是會在執行過程中推斷變數的形態來完成優化 (當然相較強型別的情況下效率沒那麼好)，以下面連結中的範例就是沒有加上任何型別宣告，但是 JIT 的效果還是很顯著的，可參考這篇[文章](https://stitcher.io/blog/jit-in-real-life-web-applications?fbclid=IwAR0inSoNurPYNNeXqUJ64Tm9E8TbSSUNNYZI2-50yzkd7m5UzOe38MYtNzI)。

## 參考資料

- [我問問題的地方 - Facebook](https://www.facebook.com/groups/laravel.tw/posts/6083135688422096)
- [PHP 8: JIT performance in real-life web applications](https://stitcher.io/blog/jit-in-real-life-web-applications)
- [PHP 8 新特性之 JIT 簡介](https://www.laruence.com/2020/06/27/5963.html)
