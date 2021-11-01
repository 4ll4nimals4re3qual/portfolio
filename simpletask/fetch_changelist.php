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
    if(preg_match('/listid/',$key)):
      $listid = '`'.$key.'`=:'.$key;//`listid`=:listid
    elseif(preg_match('/listname/',$key)):
      if(!preg_match('/^[ぁ-んァ-ヶー々一-龠０-９a-zA-Z0-9 ]{1,256}+$/',$value)):
        $errors['listname'] = 'タイトルは半角256文字以内で入力してください';
      else:
        $set .= '`'.$key.'`=:'.$key.',';//`listname`=:listname
      endif;
    endif;
  endforeach;
  $set = substr($set, 0, -1);

  if(count($errors) === 0):
    $sql = 'UPDATE `list_table` SET '.$set.' WHERE '.$listid;
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
