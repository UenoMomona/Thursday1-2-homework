<?php

try{
  $dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

  if(isset($_POST['body']) && $_POST['body'] !== ""){
    
    $image_filename = null;

    if( !empty($_POST['image_base64'])){
      // 先頭の部分をちょっと削る
      $base64 = preg_replace('/^data:.+base64,/', '', $_POST['image_base64']);

      // base64からバイナリにデコードする
      $image_binary = base64_decode($base64);

      //新しいファイル名を決めて、バイナリを出力する
      $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.jpg';

      $filepath = '/var/www/upload/image/' . $image_filename;
      file_put_contents( $filepath, $image_binary);
    }

    $sql = 'INSERT INTO `posts` (`body`, `image_filename`) VALUES (:body, :image_filename);';
    $insert = $dbh->prepare($sql);
    $insert->bindValue(':body', $_POST['body']);
    $insert->bindValue(':image_filename', $image_filename);
    $insert->execute();

    header("HTTP/1.1 302 Found");   
    header("Location: ./index.php");
    return;
  }

  if(!empty($_GET['page']) && intval($_GET['page']) !== 0){
    $page = intval($_GET['page']);
  }else{
    $page = 1;
  }

  $offset_num = ($page - 1) * 10;
  $sql = 'SELECT * FROM `posts` ORDER BY `id` DESC LIMIT 11 OFFSET :num;';
  $select = $dbh->prepare($sql);
  $select->bindParam(':num', $offset_num, PDO::PARAM_INT);
  $select->execute();
  $data = $select->fetchAll();

  if(count($data) === 11){
    $limit = 10;
  }else{
    $limit = count($data);
  }

}catch(Throwable $e){
  echo $e->getMessage();
  return;
}

function bodyFilter(string $body): string {
  $body = htmlspecialchars($body);
  $body = nl2br($body);

  $body = preg_replace('/&gt;&gt;(\d+)/', '<a href="./detail.php?id=$1">&gt;&gt;$1</a>', $body);

  return $body;
}

?>

<!DOCTYPE HTML>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="./css/style.css" rel="stylesheet">
  <link href="./css/style-min.css" rel="stylesheet" media="screen and (max-width: 480px)">
</head>
<body>
<h1>Web掲示板</h1>
<div class="wrapper">
  <div class="newPost">
    <form method="POST" action="./index.php">
      <textarea name="body" placeholder="掲示板に書き込もう！"></textarea><br>
      <div id="warning"></div>
      <input type="file" accept="image/*" name="image" id="file">
      <!-- base64を送るよう -->
      <input id="imageBase64Input" type="hidden" name="image_base64">
      <!-- 画像縮小用 -->
      <canvas id="imageCanvas" style="display: none;"></canvas>
      <div id="previews" style="display: none;">
        <p>プレビュー：</p>
        <canvas id="confirmCanvas"></canvas>
      </div>
      <button type="submit" id="submit">投稿</button>
    </form>
  </div> <!-- newPost --!>

  <div class="page">
    <?php if($page !== 1): ?>
      <div class="before">
        <a href="./index.php?page=<?= $page - 1 ?>">前のページへ</a>
      </div>
    <?php else: ?>
      <div class="before"></div>
    <?php endif ?>
    <div class="now">--- <?= $page ?>ページ ---</div>
    <?php if(count($data) === 11): ?>
      <div class="after">
        <a href="./index.php?page=<?= $page + 1 ?>">次のページへ</a>
      </div>
    <?php else: ?>
      <div class="after"></div>
    <?php endif ?>
  </div> <!-- page --!>
  <div class="posts">

    <?php for($i = 0; $i < $limit; $i++): ?>
    <?php $post = $data[$i]; ?>
    <dl>
      <dt><a href="./detail.php?id=<?= $post['id'] ?>"><?= $post['id'] ?></a>  
      <?php if(!empty($post['reply_to'])): ?>
        <a href="./detail.php?id=<?= $post['reply_to'] ?>">&gt;&gt;<?= $post['reply_to'] ?></a>
      <?php endif ?>
      </dt>
  
      <dd class="time"><?= $post['created_at'] ?> </dd>
      <dd><?= bodyFilter($post['body']); ?><br>
        <?php if(!empty($post['image_filename'])): ?>
          <img src="/image/<?=$post['image_filename'] ?>" style="max-height: 10em;">
        <?php endif; ?>
      </dd>
    </dl>
    <?php endfor ?>
   </div> <!-- posts --!>
</div> <!-- wrapper --!>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const ImageInput = document.getElementById('file');
  ImageInput.addEventListener("change", () => {
    const file = ImageInput.files[0];
    if(!file.type.startsWith('image/')){
      //画像ではなかった時
      return;
    }

    const imageBase64Input = document.getElementById("imageBase64Input");
    const canvas = document.getElementById("imageCanvas");
    const reader = new FileReader();
    const image = new Image();

    reader.onload = () => {
      image.onload = () => {

        // 元画像の大きさを取得
        const originalWidth = image.naturalWidth;
        const originalHeight = image.naturalHeight;

        const maxLength = 2000;

        if( originalWidth <= maxLength && originalHeight <= maxLength ){
          //どちらも許容サイズ以内の場合そのまま
          canvas.width = originalWidth;
          canvas.height = originalHeight;
        } else if (originalWidth > originalHeight){
          // 画像が横長の場合
          canvas.width = maxLength;
          canvas.height = maxLength * originalHeight / originalWidth;
        } else {
          canvas.width = maxLength * originalWidth / originalHeight;
          canvas.height = maxLength;
        }

        const context = canvas.getContext("2d");
        context.drawImage(image, 0, 0, canvas.width, canvas.height);

        imageBase64Input.value = canvas.toDataURL('image/jpeg', 0.9);

        // 選んだ画像を確認用に表示する
        const previews = document.getElementById('previews');
        const confirmCanvas = document.getElementById('confirmCanvas');
        const maxHeight = 160
        confirmCanvas.height = maxHeight;
        confirmCanvas.width = maxHeight * originalWidth / originalHeight;
        const confirmContext = confirmCanvas.getContext('2d');
        confirmContext.drawImage(image, 0, 0, confirmCanvas.width, confirmCanvas.height);
        previews.style.display = '';
      };
      image.src = reader.result;
    };
    reader.readAsDataURL(file);
  });
  console.log(imageBase64Input.value);
});
</script>
</body>
</html>
