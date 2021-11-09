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
    if(preg_match('/itemid/',$key)):
      $itemid = '`'.$key.'`=:'.$key;//`itemid`=:itemid
    elseif(preg_match('/listid/',$key)):
      $set .= '`'.$key.'`=:'.$key.',';//`listid`=:listid
    elseif(preg_match('/itemname/',$key)):
      if(!preg_match('/^[ぁ-んァ-ヶー々一-龠０-９a-zA-Z0-9 \r\n]{0,256}+$/',$value)):
        $errors['itemname'] = 'タスク：許可されていない文字が入力されたか文字数が長すぎます';
      else:
        $set .= '`'.$key.'`=:'.$key.',';//`itemname`=:itemname
      endif;
    elseif(preg_match('/limitdate/',$key)):
      $set .= '`'.$key.'`=:'.$key.',';//`limitdate`=:limitdate
    elseif(preg_match('/text/',$key)):
      if(!preg_match('/^[ぁ-んァ-ヶー々一-龠０-９a-zA-Z0-9 \r\n]{0,256}+$/',$value)):
        $errors['text'] = 'コメント：許可されていない文字が入力されたか文字数が長すぎます';
      else:
        $set .= '`'.$key.'`=:'.$key.',';//`text`=:text
      endif;
    elseif(preg_match('/image/',$key)):
      //添付画像がないとき
      $set .= '`'.$key.'`=:'.$key.',';//`image`=:image
    elseif(preg_match('/checkable/',$key)):
      if(!isset($value) || !strlen($value)):
        $errors['checkable']='入力エラーです';
      else:
        $set .= '`'.$key.'`=:'.$key.',';//`checkable`=:checkable
      endif;
    elseif(preg_match('/checked/',$key)):
      if(!isset($value) || !strlen($value)):
        $errors['checked']='入力エラーです';
      else:
        $set .= '`'.$key.'`=:'.$key.',';//`checked`=:checked
      endif;
    elseif(preg_match('/color/',$key)):
      if(!preg_match('/^#[a-f0-9]{6,6}+$/',$value)):
        $errors['color'] = '入力エラーです';
      else:
        $set .= '`'.$key.'`=:'.$key.',';//`color`=:color
      endif;
    elseif(preg_match('/tag/',$key)):
      if(!preg_match('/^[#a-zA-Z0-9 ]{0,256}+$/',$value)):
        $errors['tag'] = 'タグ：許可されていない文字が入力されたか文字数が長すぎます';
      else:
        $set .= '`'.$key.'`=:'.$key.',';//`tag`=:tag
      endif;
    elseif(preg_match('/pin/',$key)):
      if(!isset($value) || !strlen($value)):
        $errors['pin']='入力エラーです';
      else:
        $set .= '`'.$key.'`=:'.$key.',';//`pin`=:pin
      endif;
    elseif(preg_match('/updatedate/',$key)):
      $set .= '`'.$key.'`=:'.$key.',';//`updatedate`=:updatedate
    endif;  
  endforeach;
  // 添付画像があるとき
  if(count($_FILES)):
    $set .= '`image`=:image,';//`image`=:image
    // 送信ファイルチェック
    $arr=explode(".",$_FILES['image']['name']);//ファイル名と拡張子を分割
    $filename="images/".date('Ymd-His').".".$arr[count($arr)-1];
    $e_code=$_FILES['image']['error'];
    if($e_code!=0){
      $errors['image']='ファイル送信エラーです';
    }else{
      $result=@move_uploaded_file($_FILES["image"]["tmp_name"],$filename);
      if(!$result){
        exit("ファイル保存エラーです");
      }
    }
  endif;
  $set = substr($set, 0, -1);
  if(count($errors) === 0):
    $sql = 'UPDATE `item_table` SET '.$set.' WHERE '.$itemid;
    $stmt = $pdo->prepare($sql);
    foreach($_POST as $key => $value):
      $bind = ':'.$key;
      $stmt->bindValue($bind,$value);
    endforeach;
    // 添付画像があるとき
    if(count($_FILES)):
      $stmt->bindValue(':image',$filename);
    endif;
    $stmt->execute();
    $stmt = null;
  endif;
  $pdo = null;
  
  $json = json_encode($errors);
  echo $json;
?>
