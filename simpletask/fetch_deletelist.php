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
  $sql = 'DELETE FROM `item_table` WHERE `userid`=:userid and `deskid`=:deskid and `listid`=:listid';
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':userid',$_POST['userid']);
  $stmt->bindParam(':deskid',$_POST['deskid']);
  $stmt->bindParam(':listid',$_POST['listid']);
  $result = $stmt->execute();
  if(!$result):
    $stmt = null;
    $pdo = null;
    exit('サーバーエラー');
  endif;
  $stmt = null;

  // list_table
  $sql = 'DELETE FROM `list_table` WHERE `userid`=:userid and `deskid`=:deskid and `listid`=:listid';
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':userid',$_POST['userid']);
  $stmt->bindParam(':deskid',$_POST['deskid']);
  $stmt->bindParam(':listid',$_POST['listid']);
  $result = $stmt->execute();
  if(!$result):
    $stmt = null;
    $pdo = null;
    exit('サーバーエラー');
  endif;
  $stmt = null;

  // desk_table
  $listid = '';
  foreach($_POST as $key => $value):
    if(preg_match('/listid[0-9]+/',$key)):
      $listid .= '`'.$key.'`=:'.$key.',';//`listid1`=:listid1,`listid2`=:listid2...
    elseif(preg_match('/deskid/',$key)):
      $deskid = '`'.$key.'`=:'.$key;//`deskid`=:deskid
    endif;  
  endforeach;
  $listid = substr($listid, 0, -1);
  $sql = 'UPDATE `desk_table` SET '.$listid.' WHERE '.$deskid;
  $stmt = $pdo->prepare($sql);
  foreach($_POST as $key => $value):
    if(preg_match('/listid[0-9]+/',$key) || preg_match('/deskid/',$key)):
      $bind = ':'.$key;
      $stmt->bindValue($bind,$value);
    endif;
  endforeach;
  $stmt->execute();
  $stmt = null;



  // $listid = '`listid'.$_POST['index'].'`=null';//`listid**`=null
  // $sql = 'UPDATE `desk_table` SET '.$listid.' WHERE `userid`=:userid and `deskid`=:deskid';
  // $stmt = $pdo->prepare($sql);
  // $stmt->bindParam(':userid',$_POST['userid']);
  // $stmt->bindParam(':deskid',$_POST['deskid']);
  // $result = $stmt->execute();
  // if(!$result):
  //   $stmt = null;
  //   $pdo = null;
  //   exit('サーバーエラー');
  // endif;
  // $stmt = null;
  
  $pdo = null;

  
?>
