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
  $itemid = '';
  foreach($_POST as $key => $value):
    if(preg_match('/itemid/',$key)):
      $itemid .= '`'.$key.'`=:'.$key.',';//`itemid1`=:itemid1,`itemid2`=:itemid2...
    elseif(preg_match('/listid/',$key)):
      $listid = '`'.$key.'`=:'.$key;//`listid`=:listid
    endif;  
  endforeach;
  $itemid = substr($itemid, 0, -1);
  $sql = 'UPDATE `list_table` SET '.$itemid.' WHERE '.$listid;
  $stmt = $pdo->prepare($sql);
  foreach($_POST as $key => $value):
    $bind = ':'.$key;
    $stmt->bindValue($bind,$value);
  endforeach;
  $stmt->execute();
  $stmt = null;
  $pdo = null;

  
?>
