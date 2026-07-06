# 在 Linux 中建立記憶體硬碟

記憶體硬碟 (RAM Disk) 相較於傳統機械硬碟或固態硬碟，其讀寫速度更快，但是資料無法永久保存，只要一重新開機，資料就會消失。

在 Linux 中，你有兩種方式可以新增一個記憶體硬碟：

1. 使用 `mount` 指令
2. 修改 `/etc/fstab` 檔案

## 使用 `mount` 指令

首先使用 `mkdir` 建立要掛載的資料夾：

```bash
sudo mkdir /mnt/ramdisk
```

接著使用 `mount` 指令掛載記憶體硬碟：

```bash
sudo mount -t tmpfs -o size=512M tmpfs /mnt/ramdisk
```

各項參數說明：

- `-t tmpfs`：指定檔案系統為 tmpfs
- `-o size=512M`：指定記憶體硬碟大小為 512MB
- `/mnt/ramdisk`：指定掛載的目錄

## 修改 `/etc/fstab` 檔案

如果想再重開機之後自動設定 RAM Disk，可以修改 `/etc/fstab` 檔案。

首先使用 `root` 權限以 `vim` 開啟 `/etc/fstab` 檔案：

```bash
sudo vim /etc/fstab
```

在檔案最後加入以下內容：

```text
LABEL=cloudimg-rootfs    /            ext4    discard,commit=30,errors=remount-ro    0 1
LABEL=BOOT               /boot        ext4    defaults                               0 2
LABEL=UEFI               /boot/efi    vfat    umask=0077                             0 1
tmpfs                    /ramdisk     tmpfs   rw,nodev,nosuid,size=10M               0 0
```

編輯完成之後，按下 `Esc` 鍵，輸入 `:wq` 並按下 `Enter` 儲存並離開。

最後使用 `mount -a` 指令重新掛載 `/etc/fstab` 中的檔案系統：

```bash
sudo systemctl daemon-reload
sudo mount -a
```

## 參考資料

- [Linux 中如何建立 RAM Disk，兩個快速簡單的方式，tmpfs、fstab | 適用各式 Linux 系統 | 挨踢實驗室](https://www.youtube.com/watch?v=xnSlYGeqlNA)
