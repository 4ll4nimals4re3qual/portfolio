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

  // ユーザー名チェック
  $username = null;
  if(!isset($_POST['username']) || !strlen($_POST['username'])):
    $errors[] = '名前を入力してください';
  elseif(strlen($_POST['username'])>40):
    $errors[] = '名前は半角40文字以内で入力してください';
  elseif(!preg_match('/^[ぁ-んァ-ヶー々一-龠０-９a-zA-Z0-9]{1,40}+$/',$_POST["username"])):
    $errors[] = '名前を正しく入力してください';
  else:
    $sql = 'SELECT `username` FROM `user_table` WHERE `username`=:username';
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
      $errors[] = '名前が既に使用されています';
    endif;
    $stmt = null;
    $username = $_POST['username'];
  endif;

  // パスワードチェック
  $pw = null;
  if(!isset($_POST['pw']) || !strlen($_POST['pw'])):
    $errors[] = 'パスワードを入力してください';
  elseif(!preg_match('/^[a-zA-Z0-9]{6,12}$/u',$_POST['pw'])):
    $errors[] = 'パスワードは半角英数6～12文字で入力してください';
  else:
    $pw = password_hash($_POST['pw'],PASSWORD_DEFAULT);
  endif;

  // ユーザー追加
  if(count($errors) === 0):
    $sql = 'INSERT INTO `user_table` (`username`,`pw`) VALUE (:username,:pw)';
    $stmt = $pdo -> prepare($sql);
    $stmt -> bindParam(':username',$username);
    $stmt -> bindParam(':pw',$pw);
    $stmt -> execute();
    $userid = $pdo->lastInsertId();
    $stmt = null;

    $sql = 'INSERT INTO `userdata_table` (`userid`) VALUE (:userid)';
    $stmt = $pdo -> prepare($sql);
    $stmt -> bindParam(':userid',$userid);
    $stmt -> execute();
    $stmt = null;
  endif;
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
  <title>sign up</title>
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
<?php else: ?>
    <p>登録しました</p>
    <a href="login.html">log in</a>
  </main>
<?php endif; ?>
</body>
</html>