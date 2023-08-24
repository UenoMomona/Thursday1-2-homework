<?php

try{
  $dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

  if(isset($_POST['body']) && $_POST['body'] !== ""){
    
    $image_filename = null;

    if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
     // var_dump($_FILES);
     // return;
      $pre_filepath = $_FILES['image']['tmp_name'];
      if (preg_match('/^image\//', mime_content_type($pre_filepath)) !==1 ) {
        //アップロードされたものが画像でなかった時 
        header("HTTP/1.1 302 Found");   
        header("Location: ./index.php");
        return;
      }

      $pathinfo = pathinfo($_FILES['image']['name']);
      $extension = $pathinfo['extension'];
      $image_filename = strval(time()) . bin2hex(random_bytes(25)). '.' . $extension;
      $filepath = '/var/www/upload/image/' . $image_filename;
      move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
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

?>

<!DOCTYPE HTML>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="./css/style.css" rel="stylesheet">
</head>
<body>
<h1>Web掲示板</h1>
<div class="wrapper">
  <div class="newPost">
    <form method="POST" action="./index.php" enctype="multipart/form-data">
      <textarea name="body" placeholder="掲示板に書き込もう！"></textarea><br>
      <div id="warning"></div>
      <input type="file" accept="image/*" name="image" id="file">
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

    <?php foreach($data as $post): ?>
    <dl>
      <dt><a href="./detail.php?id=<?= $post['id'] ?>"><?= $post['id'] ?></a>  
      <?php if(!empty($post['reply_to'])): ?>
        <a href="./detail.php?id=<?= $post['reply_to'] ?>">＞＞<?= $post['reply_to'] ?></a>
      <?php endif ?>
      </dt>
  
      <dd class="time"><?= $post['created_at'] ?> </dd>
      <dd><?= nl2br(htmlspecialchars($post['body'])) ?><br>
        <?php if(!empty($post['image_filename'])): ?>
          <img src="/image/<?=$post['image_filename'] ?>" style="max-height: 10em;">
        <?php endif; ?>
      </dd>
    </dl>
    <?php endforeach ?>
   </div> <!-- posts --!>
</div> <!-- wrapper --!>


<!-- <script src="./script.js"></script> --!>
<script>
  const file = document.getElementById('file');
  const warning = document.getElementById('warning');
  const submit = document.getElementById('submit');

  file.addEventListener('change', function(e){
    var fileList = e.target.files;
    if( fileList[0].size / 1024 ** 2 > 5){
      warning.innerText = "最大5MBまで！";
      submit.disabled = true;
    }else{
      warning.innerText = "";
      submit.disabled = false;
    }
  });
</script>
</body>
</html>
