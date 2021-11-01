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
  $col = '`userid`,`deskid`,`listname`';
  $val = ':userid,:deskid,"untitled"';
  $sql = 'INSERT INTO `list_table` ('.$col.') VALUE ('.$val.')';
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':userid',$_POST['userid']);
  $stmt->bindParam(':deskid',$_POST['deskid']);
  $stmt->execute();
  echo $pdo->lastInsertId();
  $stmt = null;
  $pdo = null;
  
?>
