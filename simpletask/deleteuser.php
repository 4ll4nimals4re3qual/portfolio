<?php
  session_start();
  session_regenerate_id(true);
  if(!isset($_SESSION['userid'])):
    exit('ログインしなおしてください<p><a href="login.html">log in</a></p>');
  endif;

  $userid = $_SESSION['userid'];

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
  <title>delete user</title>
</head>
<body>
  <header>
    <h1><span>S</span>impletask</h1>
  </header>
  <main>
    <p>全てのデータが削除されます。<br>本当に退会しますか？</p>
    <button class="deletebtn" onclick="deleteUser()">退会する</button>
    <button class="backbtn" onclick="history.go(-1)">back</button>
  </main>
  <script>
<?php echo "let userid = ".$userid.";",PHP_EOL; ?>

    // （Fetch）userテーブル、userdataテーブルからuser削除
    const postFetch_deleteuser = (obj) => {
      fetch('fetch_deleteuser.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify(obj)
      })
      .then(response => response.text())
      .then(data => console.log(data));
    }

    // user削除
    function deleteUser(){
        // DBから削除
        const obj = {
          userid: userid
        };
        postFetch_deleteuser(obj);// user_tableからuser削除
      
        location.href = 'https://simpletask.sakura.ne.jp/';
      // }
    }

  </script>
</body>
</html>