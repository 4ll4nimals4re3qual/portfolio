<?php
  session_start();
  if(!isset($_SESSION['userid'])):
    exit('ログインしなおしてください<p><a href="login.html">log in</a></p>');
  endif;
  $_SESSION = array();
  if(isset($_COOKIE[session_name()])):
    setcookie(session_name(),'',time()-1000);
  endif;
  session_destroy();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="index.css">
  <link rel="icon" href="favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Coiny&family=Fredoka+One&family=Palette+Mosaic&display=swap" rel="stylesheet">
  <title>log out</title>
</head>
<body>
  <header>
    <h1><span>S</span>impletask</h1>
    <a href="login.html" class="right">log in</a>
    <a href="signup.html">sign up</a>
  </header>
  <main>
    <p>log outしました</p>
  </main>
</body>
</html>