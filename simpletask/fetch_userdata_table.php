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
  $deskid = '';
  foreach($_POST as $key => $value):
    if(preg_match('/deskid/',$key)):
      $deskid .= '`'.$key.'`=:'.$key.',';//`deskid1`=:deskid1,`deskid2`=:deskid2...
    elseif(preg_match('/userid/',$key)):
      $userid = '`'.$key.'`=:'.$key;//`userid`=:userid
    endif;  
  endforeach;
  $deskid = substr($deskid, 0, -1);
  $sql = 'UPDATE `userdata_table` SET '.$deskid.' WHERE '.$userid;
  $stmt = $pdo->prepare($sql);
  foreach($_POST as $key => $value):
    $bind = ':'.$key;
    $stmt->bindValue($bind,$value);
  endforeach;
  $stmt->execute();
  $stmt = null;
  $pdo = null;

  
?>
