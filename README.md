#WEB掲示板

##起動方法
1.dockerを起動する<br>
これらをダウンロードしたところで `docker compose up` を実行する
1.テーブルの作成<br>
各コンテナが起動している状態で `docker compose exec mysql mysql techc` を実行し、MySQLクライアントを起動する<br>
以下のSQLを実行し、テーブルを作成する<br>
``` sql
create table `posts` (
`id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
`reply_to` INT DEFAULT NULL,
`body` TEXT NOT NULL,
`image_filename` TEXT DEFAULT NULL,
`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

##ブラウザからのアクセス
以下のURLから掲示板にアクセスできる
> http://{サーバーのアドレス}/index.php
