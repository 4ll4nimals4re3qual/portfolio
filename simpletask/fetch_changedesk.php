<?php
  // $_POST = json_decode(file_get_contents('php://input'), true);
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
  $errors=array();
  $set = '';
  foreach($_POST as $key => $value):
    if(preg_match('/deskid/',$key)):
      $deskid = '`'.$key.'`=:'.$key;//`deskid`=:deskid
    elseif(preg_match('/deskname/',$key)):
      if(!preg_match('/^[ぁ-んァ-ヶー々一-龠０-９a-zA-Z0-9 ]{1,256}+$/',$value)):
        $errors['deskname'] = '許可されていない文字が入力されたか文字数が長すぎます';
      else:
        $set .= '`'.$key.'`=:'.$key.',';//`deskname`=:deskname
      endif;
    endif;
  endforeach;
  $set = substr($set, 0, -1);

  if(count($errors) === 0):
    $sql = 'UPDATE `desk_table` SET '.$set.' WHERE '.$deskid;
    $stmt = $pdo->prepare($sql);
    foreach($_POST as $key => $value):
      $bind = ':'.$key;
      $stmt->bindValue($bind,$value);
    endforeach;
    $stmt->execute();
    $stmt = null;
  endif;
  $pdo = null;
  
  $json = json_encode($errors);
  echo $json;
?>
