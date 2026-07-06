# 清理用不到的 Docker 資源 (容器、映像檔與資料存儲)

在使用 `docker build` 多次建立 image 時，即使 `image:tag` 相同，先前 build 出來的 image 也不會被刪除，而是呈現 untag 的狀態

這時我們可以用下方的指令查看 dangling images

```shell
docker images --filter "dangling=true"
```

移除 dangling 的 images

```shell
docker image prune
```

剛 pull 下來但沒有建立容器的 image 不算是 dangling images，而是 unused images，如果也想清除這些 images 的話，可以使用下面的指令

```shell
docker image prune -a
```

移除所有沒用到的 Docker 物件。會連同停用的容器、無用的網路、無用的映像檔案與無用的建置快取全部刪除

```shell
docker system prune -a
```

當 Docker 資料存儲不再被容器使用時，可以使用下方的指令清理這些不再使用的 Docker 資料存儲

```shell
docker volume prune
```

## 參考資料

- [如何快速刪除所有已經無用的 Docker 資源 (容器,容器映像,網路)](https://blog.miniasp.com/post/2022/11/18/docker-image-prune-notes)
