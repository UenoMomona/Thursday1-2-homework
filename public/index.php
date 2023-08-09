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
  $sql = 'SELECT * FROM `posts`;';
  $select = $dbh->prepare($sql);
  $select->execute();

}catch(Throwable $e){
  echo $e->getMessage();
  return;
}

?>
<h1>Web掲示板</h1>
<form method="POST" action="./index.php" enctype="multipart/form-data">
<textarea name="body" placeholder="掲示板に書き込もう！"></textarea>
<input type="file" accept="image/*" name="image" id="file">
<button type="submit">投稿</button>
</form>

<?php foreach($select as $post): ?>
<dl>
  <dt><?= $post['id'] ?></dt>
  <dd><?= $post['created_at'] ?> </dd>
  <dd><?= nl2br(htmlspecialchars($post['body'])) ?>
    <?php if(!empty($post['image_filename'])): ?>
    <img src="/image/<?=$post['image_filename'] ?>" style="max-height: 10em;">
    <?php endif; ?>
  </dd>
</dl>
<?php endforeach ?>
