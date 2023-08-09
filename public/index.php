<?php

try{
  $dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

  $sql = 'SELECT * FROM `posts`;';
  $select = $dbh->prepare($sql);
  $select->execute();

  if(isset($_POST['body']) && $_POST['body'] != ""){
    $sql = 'INSERT INTO `posts` (`body`) VALUES (:body);';
    $insert = $dbh->prepare($sql);
    $insert->bindValue(':body', $_POST['body']);
    $insert->execute();

    header("HTTP/1.1 302 Found");
    header("Location: ./index.php");
  }
}catch(Throwable $e){
  echo $e->getMessage();
}

?>
<h1>Web掲示板</h1>
<form method="POST" action="./index.php">
<textarea name="body" placeholder="掲示板に書き込もう！"></textarea>
<button type="submit">投稿</button>
</form>

<?php foreach($select as $post): ?>
<dl>
  <dt><?= $post['id'] ?></dt>
  <dd><?= $post['created_at'] ?> </dd>
  <dd><?= nl2br(htmlspecialchars($post['body'])) ?> </dd>
</dl>
<?php endforeach ?>
