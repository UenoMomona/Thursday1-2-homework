<?php
try{

  $dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');
  
  if(isset($_POST['body'])){
    $sql = 'INSERT INTO `posts` (`reply_to`, `body`) VALUES (:reply_to, :body);';
    $pre = $dbh->prepare($sql);
    $pre->bindValue(':reply_to', $_POST['id']);
    $pre->bindValue(':body', $_POST['body']);
    $pre->execute();
    
    header("HTTP/1.1 302 Found");
    header("Location: ./index.php");
    return;
  }else{
    if(!isset($_GET['id'])){
      throw new Exception('idの未指定');
    }else{
      $id = intval($_GET['id']);
    }
    $sql = 'SELECT * FROM posts WHERE id = :id';
    $pre = $dbh->prepare($sql);
    $pre->bindValue(':id', $id);
    $pre->execute();
    $result = $pre->fetch(PDO::FETCH_ASSOC);

    $sql = 'SELECT * FROM posts WHERE reply_to = :reply_to;';
    $pre = $dbh->prepare($sql);
    $pre->bindValue(':reply_to', $id);
    $pre->execute();
    $replyPosts = $pre->fetchAll();
  }

}catch(Throwable $e){
  //header("HTTP/1.1 302 Found");
  //header("Location: ./index.php");
  echo $e;
  return;
}

?>

<!DOCTYPE HTML>
<head>
</head>
<body>
<h1><a href="./index.php">Web掲示板</a></h1>
<dl>
  <dt><?= $result['id'] ?></dt>
  <dd><?= $result['created_at'] ?> </dd>
  <dd><?= nl2br(htmlspecialchars($result['body'])) ?></dd>
  <dd>
  <?php if(!empty($result['image_filename'])): ?>
  <img src="/image/<?= $result['image_filename'] ?>" style="max-height: 10em;">
  <?php endif; ?>
  </dd>
</dl>
<form  method="POST" action="./detail.php" enctype="multipart/form-data">
  <textarea name="body" placeholder="コメントをしよう！"></textarea>
  <input type="hidden" value='<?= $id ?>' name="id">
  <button type="submit">投稿</button>
</form>
<h2>コメント</h2>
<?php if(!empty($replyPosts)): ?>
  <?php foreach($replyPosts as $reply): ?>
    <dl>
      <dt><a href="./detail.php?id=<?= $reply['id'] ?>"><?= $reply['id'] ?></a></dt>
      <dd><?= $reply['created_at'] ?> </dd>
      <dd><?= nl2br(htmlspecialchars($reply['body'])) ?></dd>
    </dl>
  <?php endforeach; ?>
<?php endif; ?>
<a href="./index.php">TOPへ</a>
