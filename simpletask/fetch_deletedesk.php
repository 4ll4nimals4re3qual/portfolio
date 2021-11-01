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
  // item_tableからリスト内のitem削除
  $arr_item = $_POST['userid'];
  $sql = 'DELETE FROM `item_table` WHERE `userid`=:userid and `deskid`=:deskid';
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':userid',$_POST['userid']);
  $stmt->bindParam(':deskid',$_POST['deskid']);
  $result = $stmt->execute();
  if(!$result):
    $stmt = null;
    $pdo = null;
    exit('サーバーエラー');
  endif;
  $stmt = null;

  // list_table
  $sql = 'DELETE FROM `list_table` WHERE `userid`=:userid and `deskid`=:deskid';
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':userid',$_POST['userid']);
  $stmt->bindParam(':deskid',$_POST['deskid']);
  $result = $stmt->execute();
  if(!$result):
    $stmt = null;
    $pdo = null;
    exit('サーバーエラー');
  endif;
  $stmt = null;

  // desk_table
  $sql = 'DELETE FROM `desk_table` WHERE `userid`=:userid and `deskid`=:deskid';
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':userid',$_POST['userid']);
  $stmt->bindParam(':deskid',$_POST['deskid']);
  $result = $stmt->execute();
  if(!$result):
    $stmt = null;
    $pdo = null;
    exit('サーバーエラー');
  endif;
  $stmt = null;

  // userdata_table
  $deskid = '';
  foreach($_POST as $key => $value):
    if(preg_match('/deskid[0-9]+/',$key)):
      $deskid .= '`'.$key.'`=:'.$key.',';//`deskid1`=:deskid1,`deskid2`=:deskid2...
    elseif(preg_match('/userid/',$key)):
      $userid = '`'.$key.'`=:'.$key;//`userid`=:userid
    endif;  
  endforeach;
  $deskid = substr($deskid, 0, -1);
  $sql = 'UPDATE `userdata_table` SET '.$deskid.' WHERE '.$userid;
  $stmt = $pdo->prepare($sql);
  foreach($_POST as $key => $value):
    if(preg_match('/deskid[0-9]+/',$key) || preg_match('/userid/',$key)):
      $bind = ':'.$key;
      $stmt->bindValue($bind,$value);
    endif;
  endforeach;
  $stmt->execute();
  $stmt = null;

  $pdo = null;

  
?>
