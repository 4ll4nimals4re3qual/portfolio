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
    exit('データベースに接続できません：'.$e -> getmessage());
    // exit('データベースに接続できません');
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
    $pdo = null;
    $username = $_POST['username'];
  endif;

  // パスワードチェック
  $pw = null;
  if(!isset($_POST['pw']) || !strlen($_POST['pw'])):
    $errors[] = 'パスワードを入力してください';
  elseif(!preg_match('/^[a-zA-Z0-9]{6,12}$/u',$_POST['pw'])):
    $errors[] = 'パスワードは半角英数6～12文字で入力してください';
  else:
    $pw = $_POST['pw'];
  endif;
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
    <h1>sign up</h1>
    <form action="signup3.php" method="post">
      <table>
        <tbody>
          <tr>
            <td><p>ユーザー名：<?php echo htmlspecialchars($username,ENT_QUOTES,'UTF-8'); ?></p></td>
          </tr>
          <tr>
            <td><p>パスワード：<?php echo htmlspecialchars($pw,ENT_QUOTES,'UTF-8'); ?></p></td>
          </tr>
          <tr>
            <td><p>で登録します。</p></td>
          </tr>
          <tr>
            <td class="submit">
              <input type="hidden" name="username" value="<?php echo htmlspecialchars($username,ENT_QUOTES,'UTF-8'); ?>">
              <input type="hidden" name="pw" value="<?php echo htmlspecialchars($pw,ENT_QUOTES,'UTF-8'); ?>">
              <input type="submit" value="sign up" class="submitbtn">
            </td>
          </tr>
          <tr>
            <td><input type="button" value="back"  class="backbtn" onclick="history.go(-1)"></td>
          </tr>
        </tbody>
      </table>
    </form>
  </main>
<?php endif; ?>
</body>
</html>