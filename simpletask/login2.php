<?php
  if($_SERVER['REQUEST_METHOD'] !== 'POST'):
    exit('直接アクセス禁止');
  endif;

  // データベースに接続
  try{
    $dbhost = 'mysql:host=mysql729.db.sakura.ne.jp;dbname=simpletask_db;charset=utf8';
    $dbid = 'simpletask';
    $dbpass = 'oM-6GvLb';
    $pdo = new PDO($dbhost,$dbid,$dbpass,array(PDO::ATTR_EMULATE_PREPARES=>false));
  }catch(PDOException $e){
    // exit('データベースに接続できません：'.$e -> getmessage());
    exit('データベースに接続できません');
  }

  $errors = array();

  // ログイン
  $sql = 'SELECT `userid`,`username`,`pw` FROM `user_table` WHERE `username`=:username';
  $stmt = $pdo -> prepare($sql);
  $stmt -> bindParam(':username',$_POST['username']);
  $result = $stmt -> execute();
  if(!$result):
    $stmt = null;
    $pdo = null;
    exit('サーバーエラー');
  endif;
  $result = $stmt -> fetch(PDO::FETCH_ASSOC);
  if($result != false):
    $userid = $result['userid'];
    $username = $result['username'];
    $pw = $result['pw'];
    if(password_verify($_POST['pw'],$pw)):
      session_start();
      session_regenerate_id(true);
      $_SESSION['userid'] = $userid;
      $_SESSION['username'] = $username;
      header('Location: http://'.$_SERVER['HTTP_HOST'].'/select.php');
    else:
      $errors[] = 'パスワードが違います';
    endif;
  else:
    $errors[] = 'ユーザーが登録されていません';
  endif;
  $stmt = null;
  $pdo = null;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="login.css">
  <link rel="icon" href="favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Coiny&family=Fredoka+One&family=Palette+Mosaic&display=swap" rel="stylesheet">
  <title>log in</title>
</head>
<body>
  <header>
    <h1><span>S</span>impletask</h1>
  </header>
  <main>
<?php if(count($errors)): ?>
<?php foreach($errors as $value): ?>
    <p><?php echo htmlspecialchars($value,ENT_QUOTES,'UTF-8'); ?></p>
<?php endforeach; ?>
    <button class="backbtn" onclick="history.go(-1)">back</button>
<?php endif; ?>
  </main>
</body>
</html>