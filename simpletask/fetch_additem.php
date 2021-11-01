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
  $col = '`itemname`,`userid`,`deskid`,`listid`,`createdate`,`updatedate`,`limitdate`,`text`,`image`,`checkable`,`checked`,`color`,`tag`,`pin`';
  $val = ':itemname,:userid,:deskid,:listid,:createdate,:updatedate,:limitdate,:text,:image,:checkable,:checked,:color,:tag,:pin';
  $sql = 'INSERT INTO `item_table` ('.$col.') VALUE ('.$val.')';
  $stmt = $pdo->prepare($sql);
  $date = date('Y-m-d H:i:s');
  $stmt->bindParam(':itemname',$_POST['itemname']);
  $stmt->bindParam(':userid',$_POST['userid']);
  $stmt->bindParam(':deskid',$_POST['deskid']);
  $stmt->bindParam(':listid',$_POST['listid']);
  $stmt->bindParam(':createdate',$date);
  $stmt->bindParam(':updatedate',$date);
  $stmt->bindParam(':limitdate',$_POST['limitdate']);
  $stmt->bindParam(':text',$_POST['text']);
  $stmt->bindParam(':image',$_POST['image']);
  $stmt->bindParam(':checkable',$_POST['checkable']);
  $stmt->bindParam(':checked',$_POST['checked']);
  $stmt->bindParam(':color',$_POST['color']);
  $stmt->bindParam(':tag',$_POST['tag']);
  $stmt->bindParam(':pin',$_POST['pin']);
  $stmt->execute();
  echo $pdo->lastInsertId();
  $stmt = null;
  $pdo = null;
  
?>
