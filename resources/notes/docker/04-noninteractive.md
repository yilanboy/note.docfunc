# 遇到交互式問題時的處理

當你在安裝套件時，有可能會遇到詢問時區的問題：

```text
Please select the geographic area in which you live. Subsequent configuration questions will narrow this down by presenting a list of cities, representing the time zones in which they are located.

  1. Africa  2. America  3. Antarctica  4. Australia  5. Arctic  6. Asia  7. Atlantic  8. Europe  9. Indian  10. Pacific  11. US  12. Etc
Geographic area:
```

這會導致 Dockerfile 打包的過程卡住，你可以透過設定環境變數來解決這個問題。

```bash
export TZ=Asia/Taipei
export DEBIAN_FRONTEND=noninteractive
```

但注意不建議使用 Dockerfile 的 `ENV` 來設定這個環境變數。

```dockerfile
# 時區設定沒關係
ENV TZ=Asia/Taipei
# 但這樣設定就不建議了
ENV DEBIAN_FRONTEND=noninteractive
```

因為這樣設定有可能導致別人在使用你的容器時，因為無法與系統互動而產生問題。

> 根據 Docker 官方文件的說明：
>
> While this may sound like a good idea, it may have side effects.
> The DEBIAN_FRONTEND environment variable is inherited by all images and containers built from your image, effectively changing their behavior.
> People using those images run into problems when installing software interactively, because installers do not show any dialog boxes.

正確的做法是只在該行指令中設定環境變數，讓環境變數只在該 pipeline 時有效。

```dockerfile
RUN DEBIAN_FRONTEND=noninteractive apt install php8.3 -y
```

## 參考資料

- [Why is DEBIAN_FRONTEND=noninteractive discouraged in Dockerfiles?](https://docs.docker.com/engine/faq/#why-is-debian_frontendnoninteractive-discouraged-in-dockerfiles)
