<?php
  $_POST = json_decode(file_get_contents('php://input'), true);
  // echo $_POST['listid'];

  // データベースに接続
  try{
    $dbhost = 'mysql:host=mysql729.db.sakura.ne.jp;dbname=simpletask_db;charset=utf8';
    $dbid = 'simpletask';
    $dbpass = 'oM-6GvLb';
    $pdo = new PDO($dbhost,$dbid,$dbpass,array(PDO::ATTR_EMULATE_PREPARES=>false));
  }catch(PDOException $e){
    // exit('データベースに接続できません：'.$e->getmessage());
    exit('データベースに接続できません');
  }
  // データベース操作
  $sql = 'SELECT * FROM `item_table` WHERE `userid`=:userid and `deskid`=:deskid and `listid`=:listid and `itemid`=:itemid';
  $stmt = $pdo->prepare($sql);
  foreach($_POST as $key => $value):
    $bind = ':'.$key;
    $stmt->bindValue($bind,$value);
  endforeach;
  $result = $stmt->execute();
  if(!$result):
    $stmt = null;
    $pdo = null;
    exit('サーバーエラー');
  else://
    while($result = $stmt->fetch(PDO::FETCH_ASSOC)):
      // if(!$result)//$resultが0件の場合falseが返る
      $arr_itemdata[] = $result;
    endwhile;
  endif;

  $json = json_encode($arr_itemdata);
  echo $json;
  $stmt = null;
  $pdo = null;

  
?>
