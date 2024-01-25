文件位置：
`/docs.yaml`

Initialization：

```shell
docker-compose up --build
## web: 127.0.0.1:8888
## mysql: mysql -u root -h 127.0.0.1 -P 33060 -D trident_test
```

---
Command:

- 將爽約三次的客人加入黑名單：
  `php artisan app:blocking-phone`

---
Test:

`php artisan test`
