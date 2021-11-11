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
  $where = '`userid`=:userid';
  if(array_key_exists('deskid',$_POST)){// デスク削除の時
    $where .= ' and `deskid`=:deskid';
  }
  $sql = 'SELECT `image` FROM `item_table` WHERE '.$where;
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':userid',$_POST['userid']);
  if(array_key_exists('deskid',$_POST)){// デスク削除の時
    $stmt->bindParam(':deskid',$_POST['deskid']);
  }
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
