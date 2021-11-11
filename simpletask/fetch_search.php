<?php
  $_POST = json_decode(file_get_contents('php://input'), true);

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
  // item_tableからキーワード検索
  $result_flg = false;
  // 検索キーワードがあるとき
  if(strlen($_POST['search'])):
    $sql = 'SELECT * FROM `item_table` WHERE `userid`=:userid and `deskid`=:deskid and (`itemname` LIKE \'%'.htmlspecialchars($_POST['search']).'%\' or `text` LIKE \'%'.htmlspecialchars($_POST['search']).'%\' or `tag` LIKE \'%'.htmlspecialchars($_POST['search']).'%\')';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userid',$_POST['userid']);
    $stmt->bindParam(':deskid',$_POST['deskid']);
    $result = $stmt->execute();
    if(!$result):
      $stmt = null;
      $pdo = null;
      exit('サーバーエラー');
    else:
      while($result = $stmt->fetch(PDO::FETCH_ASSOC)):
        // if(!$result)://$resultが0件の場合falseが返る
        $arr_itemdata[] = $result;
        $result_flg = true;
      endwhile;
    endif;
  endif;
  // 検索キーワードがない or 検索結果が0件のとき
  if(!strlen($_POST['search']) || !$result_flg):
    $arr_itemdata[] = '';
  endif;

  $json = json_encode($arr_itemdata);
  echo $json;
  $stmt = null;
  $pdo = null;

?>
