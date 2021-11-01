<?php
  // post以外はdesk選択
  if($_SERVER['REQUEST_METHOD'] !== 'POST'):
    exit('ログインしなおしてください<p><a href="login.html">log in</a></p>');
  else:
    $deskid = $_POST['deskid'];
  endif;

  session_start();
  session_regenerate_id(true);
  if(!isset($_SESSION['userid'])):
    exit('ログインしなおしてください<p><a href="login.html">log in</a></p>');
  endif;

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

  $userid = $_SESSION['userid'];

  // データベース操作
  // userdata_tableからdeskid取得
  function getDeskId($userid){
    $sql = 'SELECT * FROM `userdata_table` WHERE `userid`=:userid';
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userid',$userid);
    $result = $stmt->execute();
    if(!$result):
      $stmt = null;
      $pdo = null;
      exit('サーバーエラー');
    else://
      while($result = $stmt->fetch(PDO::FETCH_ASSOC)):
        // if(!$result)//$resultが0件の場合falseが返る
        return $result;
      endwhile;
    endif;
    $stmt = null;
  }

  // desk_tableからテーブルデータ（配列）取得
  function getListId($userid,$deskid){
    $sql = 'SELECT * FROM `desk_table` WHERE `userid`=:userid and `deskid`=:deskid';
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userid',$userid);
    $stmt->bindParam(':deskid',$deskid);
    $result = $stmt->execute();
    if(!$result):
      $stmt = null;
      $pdo = null;
      exit('サーバーエラー');
    else://
      while($result = $stmt->fetch(PDO::FETCH_ASSOC)):
        // if(!$result)//$resultが0件の場合falseが返る
        return $result;
      endwhile;
      $stmt = null;
    endif;
  }

  // desk_tableから全desk名（配列）取得
  function getDeskName($userid){
    $sql = 'SELECT * FROM `desk_table` WHERE `userid`=:userid';
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userid',$userid);
    $result = $stmt->execute();
    if(!$result):
      $stmt = null;
      $pdo = null;
      exit('サーバーエラー');
    else:
      $arr = [];
      while($result = $stmt->fetch(PDO::FETCH_ASSOC)):
        // if(!$result)//$resultが0件の場合falseが返る
        // $arr[] = array(
        //   'deskid'=>$result['deskid'],
        //   'deskname'=>$result['deskname'],
        // );
        $arr[$result['deskid']] = $result['deskname'];
      endwhile;
      return $arr;
      $stmt = null;
    endif;
  }

  // list_tableからテーブルデータ(配列) 取得
  function getItemId($userid,$deskid,$listid){
    $sql = 'SELECT * FROM `list_table` WHERE `userid`=:userid and `deskid`=:deskid and `listid`=:listid';
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userid',$userid);
    $stmt->bindParam(':deskid',$deskid);
    $stmt->bindParam(':listid',$listid);
    $result = $stmt->execute();
    if(!$result):
      $stmt = null;
      $pdo = null;
      exit('サーバーエラー');
    else://
      while($result = $stmt->fetch(PDO::FETCH_ASSOC)):
        // if(!$result)//$resultが0件の場合falseが返る
        return $result;
      endwhile;
      $stmt = null;
    endif;
  }
  
  // item_tableからテーブルデータ（配列）取得
  function getItemData($userid,$deskid,$listid,$itemid){
    $sql = 'SELECT * FROM `item_table` WHERE `userid`=:userid and `deskid`=:deskid and `listid`=:listid and `itemid`=:itemid';
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userid',$userid);
    $stmt->bindParam(':deskid',$deskid);
    $stmt->bindParam(':listid',$listid);
    $stmt->bindParam(':itemid',$itemid);
    $result = $stmt->execute();
    if(!$result):
      $stmt = null;
      $pdo = null;
      exit('サーバーエラー');
    else://
      while($result = $stmt->fetch(PDO::FETCH_ASSOC)):
        // if(!$result)//$resultが0件の場合falseが返る
        return $result;
      endwhile;
      $stmt = null;
    endif;
  }
  
  // list描画
  function viewList($userid,$deskid,$list_table){
    echo '<div id="list'.$list_table['listid'].'" class="list" draggable="true" onclick="openList(event)">',PHP_EOL;
    echo '<p id="listname'.$list_table['listid'].'" class="listname">'.htmlspecialchars($list_table['listname'],ENT_QUOTES,'UTF-8').'</p>';
    echo '<ul class="drag-item">',PHP_EOL;
    echo '<li id="plus'.$list_table['listid'].'" class="item plus" onclick="addItem(event)">'.'+'.'</li>',PHP_EOL;
    $arr_item = array();
    foreach($list_table as $key => $value):
      if($value === null):
        break;
      elseif(preg_match('/itemid/',$key)):
        // item描画
        $arr_item[] = $value;// jsにitemの配列を作成 
        $itemdata = getItemData($userid,$deskid,$list_table['listid'],$value);
        viewItem($itemdata);
      endif;
    endforeach;
    $arr_item = json_encode($arr_item);
    echo '</ul>',PHP_EOL;
    echo '<script>',PHP_EOL;
    echo "let arr_item{$list_table['listid']} = $arr_item;",PHP_EOL;
    echo '</script>',PHP_EOL;
    echo '</div>',PHP_EOL;
  }

  // item描画
  function viewItem($itemdata){
    $attribute = '';
    $value = '';
    $class = '';
    $checkable = 'hidden';
    $checked = '';
    $imgstyle = '';
    if($itemdata['checkable']):
      $checkable = 'checkbox';
    endif;
    if($itemdata['checked']):
      $checked = ' checked';
    endif;
    if($itemdata['image']):
      $imgstyle = 'style="display:inherit"';
    else:
      $imgstyle = 'style="display:none"';
    endif;
    $value .= '<input type="'.$checkable.'" id="checkable'.$itemdata['itemid'].'" class="checkable" onchange="changeChecked(event)"'.$checked.'>';
    $value .= '<p class="itemname">'.nl2br(htmlspecialchars($itemdata['itemname'],ENT_QUOTES,'UTF-8')).'</p>';
    $value .= '<img src="'.$itemdata['image'].'" alt="thumbnail" class="thumbnail" draggable="false" '.$imgstyle.'>';
    if($itemdata['pin']):
      $class .= ' pin';
    endif;
    $attribute .= ' style="background:'.$itemdata['color'].'"';
    echo '<li id="item'.$itemdata['itemid'].'" class="item'.$class.'" draggable="true" onclick="openItem(event)"'.$attribute.'>'.$value.'</li>',PHP_EOL;
  }

  $userdata_table = getDeskId($userid);
  $desk_table = getListId($userid,$deskid);
  $desk_name = getDeskName($userid);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="my_top.css">
  <link rel="icon" href="favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Coiny&family=Fredoka+One&family=Palette+Mosaic&display=swap" rel="stylesheet">
  <title>simpletask</title>
</head>
<body>
  <div id="wrapper">
    <header>
      <h1><span>S</span></h1>
      <form action="" method="post" onchange="submit(this.form)">
        <select class="dropdown" name="deskid">
<?php
  foreach($userdata_table as $key => $value):
    if($value === null):
      break;
    elseif(preg_match('/deskid/',$key)):
      // option要素追加
      $select = "";
      if($value == $deskid):
        $select = " selected";
      endif;
      echo '<option value="'.$value.'"'.$select.'>'.htmlspecialchars($desk_name[$value],ENT_QUOTES,'UTF-8').'</option>',PHP_EOL;
    endif;
  endforeach;
?>
        </select>
      </form>
      <div id="nav_menu">
        <input type="checkbox" id="nav_check">
        <label for="nav_check"><span></span><span></span><span></span></label>
        <div id="hidden_show">
          <nav>
            <ul id="nav_list">
              <li><a href="select.php">デスクの編集</a></li>
              <li><a href="logout.php">log out</a></li>
            </ul>
          </nav>
        </div>
      </div>
    </header>
    <main>
      <div class="open" id="js-openitem">
        <div class="open-inner">
          <div class="close-btn" id="js-close-btn" onclick="closeItem(event)">×</div>
          <form action="" method="post" name="form_item">
            <table>
              <tbody>
                <tr>
                  <th width="150">タスク</th>
                </tr>
                <tr>
                  <td><textarea name="itemname" cols="100" rows="10"></textarea></td>
                </tr>
                <tr>
                  <th>コメント</th>
                </tr>
                <tr>
                  <td><textarea name="text" cols="100" rows="10"></textarea></td>
                </tr>
                <tr>
                  <th>画像添付</th>
                </tr>
                <tr>
                  <td><img src="" alt="image" id="imageview"><br>
                      <input type="file" alt="添付画像" name="image" accept=".jpg, .png, .gif" onchange="imgChange(event)">
                      <button type="button" name="deleteimg" onclick="deleteimage()">削除</button></td>
                </tr>
                <tr>
                  <th>カラー</th>
                </tr>
                <tr>
                  <td><input type="radio" name="color" value="#ffffff" id="color1"><label for="color1" class="color">ホワイト</label>
                      <input type="radio" name="color" value="#ff9999" id="color2"><label for="color2" class="color">レッド</label>
                      <input type="radio" name="color" value="#ffbb77" id="color3"><label for="color3" class="color">オレンジ</label>
                      <input type="radio" name="color" value="#ffdd99" id="color4"><label for="color4" class="color">イエロー</label>
                      <input type="radio" name="color" value="#bbdd99" id="color5"><label for="color5" class="color">グリーン</label>
                      <input type="radio" name="color" value="#99ddee" id="color6"><label for="color6" class="color">シアン</label>
                      <input type="radio" name="color" value="#99bbff" id="color7"><label for="color7" class="color">ブルー</label>
                      <input type="radio" name="color" value="#ee99ee" id="color8"><label for="color8" class="color">マゼンタ</label></td>
                </tr>
                <tr>
                  <th>タグ</th>
                </tr>
                <tr>
                  <td><input type="text" name="tag" class="tag"></td>
                </tr>
                <tr>
                  <th>ピン</th>
                </tr>
                <tr>
                  <td><input type="checkbox" name="pin" value="0" id="pin"><label for="pin">ピン止め</label></td>
                </tr>
                <tr>
                  <th>チェックボックス</th>
                </tr>
                <tr>
                  <td><input type="checkbox" name="checkable" value="0" id="checkable"><label for="checkable">チェックボックスにする</label><br>
                  <input type="checkbox" name="checked" value="0" id="checked"><label for="checked">完了</label></td>
                </tr>
              </tbody>
            </table>
          </form>
          <button id="itemchange_btn" onclick="changeItem(event)">更新</button>
          <button id="itemdelete_btn" onclick="deleteItem(event)">削除</button>
          <p id="createdate"></p>
          <p id="updatedate"></p>
          <p id="limitdate"></p>
        </div>
        <div class="black-background" onclick="closeItem(event)"></div>
      </div>
      <div class="open" id="js-openlist">
        <div class="open-inner">
          <div class="close-btn" id="js-close-btn" onclick="closeList(event)">×</div>
          <form action="" method="post" name="form_list">
            <table>
              <tbody>
                <tr>
                  <th wihth="150">リスト</th>
                </tr>
                <tr>
                  <td><input type="text" name="listname" size="68"></td>
                </tr>
              </tbody>
            </table>
          </form>
          <button id="listchange_btn" onclick="changeList(event)">更新</button>
          <button id="listdelete_btn" onclick="deleteList(event)">削除</button>
        </div>
        <div class="black-background" onclick="closeList(event)"></div>
      </div>
<?php 
  echo '<section id="desk'.$deskid.'" class="drag-list">',PHP_EOL;
  $arr_list = array();
  // list描画
  foreach($desk_table as $key => $value):
    if($value === null):
      break;
    elseif(preg_match('/listid/',$key)):
      $arr_list[] = $value;// jsにlistの配列を作成 
      $list_table = getItemId($userid,$deskid,$value);
      viewList($userid,$deskid,$list_table);
    endif;
  endforeach;
  echo '<div id="listplus" class="list plus" onclick="addList(event)">'.'+'.'</div>',PHP_EOL;
  echo '</section>',PHP_EOL;
?>
<?php $pdo = null; ?>
    </main>
    <footer>
      
    </footer>
  </div>
  <script>
    // jsにlistの配列を作成  
<?php $arr_list = json_encode($arr_list); ?>
<?php echo "let arr_list = $arr_list;",PHP_EOL; ?>
<?php echo "let userid = ".$desk_table['userid'].";",PHP_EOL; ?>
<?php echo "let deskid = ".$desk_table['deskid'].";",PHP_EOL; ?>
    
    let obj_openitem = {};//itemの中身（変更前）
    let obj_openlist = {};//listの中身（変更前）

    // （Fetch）deskテーブル更新
    const postFetch_desk_table = (obj) => {
      fetch('fetch_desk_table.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify(obj)
      });
      // .then(response => response.text())
      // .then(data =>  console.log(data));
    }
    
    // （Fetch）listテーブル更新
    const postFetch_list_table = (obj) => {
      fetch('fetch_list_table.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify(obj)
      });
      // .then(response => response.text())
      // .then(data =>  console.log(data));
    }

    // （Fetch）listテーブル取得
    const postFetch_getlist = (obj) => {
      return fetch('fetch_getlist.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify(obj)
      })
      .then(response => response.json());
      // .then(data =>  console.log(data));
    }

    // （Fetch）listテーブルにlist追加
    const postFetch_addlist = (obj) => {
      return fetch('fetch_addlist.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify(obj)
      })
      .then(response => response.text());
      // .then(data => console.log(data));
    }

    // （Fetch）listテーブル（リスト名）更新
    const postFetch_changelist = (obj) => {
      return fetch('fetch_changelist.php', {
        method: 'POST',
        body: obj
      })
      .then(response => response.json());
      // .then(data => console.log(data));
    }

    // （Fetch）listテーブル、deskテーブルからlist削除
    const postFetch_deletelist = (obj) => {
      fetch('fetch_deletelist.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify(obj)
      });
      // .then(response => response.text())
      // .then(data => console.log(data));
    }

    // （Fetch）itemテーブル取得
    const postFetch_getitem = (obj) => {
      return fetch('fetch_getitem.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify(obj)
      })
      .then(response => response.json());
      // .then(data =>  console.log(data));
    }

    // （Fetch）itemテーブルにitem追加
    const postFetch_additem = (obj) => {
      return fetch('fetch_additem.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify(obj)
      })
      .then(response => response.text());
      // .then(data => console.log(data));
    }

    // （Fetch）itemテーブル更新
    const postFetch_changeitem = (obj) => {
      return fetch('fetch_changeitem.php', {
        method: 'POST',
        body: obj
      })
      .then(response => response.json());
      // .then(data => console.log(data));
    }

    // （Fetch）itemテーブル、listテーブルからitem削除
    const postFetch_deleteitem = (obj) => {
      fetch('fetch_deleteitem.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify(obj)
      });
      // .then(response => response.text())
      // .then(data => console.log(data));
    }

    // （Fetch）/imagesの画像ファイルを削除
    const postFetch_deleteimage = (text) => {
      fetch('fetch_deleteimage.php', {
        method: 'POST',
        body: text
      });
      // .then(response => response.text())
      // .then(data => console.log(data));
    }

    // list追加
    function addList(e){
      if(arr_list.length < 10){// list上限10件まで
        // DBに書き込み
        let obj = {
          userid: userid,
          deskid: deskid,
        };
        let promise = postFetch_addlist(obj);// list_tableにlist追加
        promise.then(result => {
          let obj2;
          eval("arr_list.push('" + result + "');");// arr_listにlist追加
          array = arr_list;
          obj2 = Object.assign({}, ...array.map((value,index) => ({
            ['listid' + (index + 1)]: value,
          })));
          obj2.deskid = deskid;
          postFetch_desk_table(obj2);// desk_table更新


          // 要素追加
          let newElement = document.createElement("div"); // div要素作成
          newElement.setAttribute("id","list" + result); // 要素に属性を設定
          newElement.setAttribute("class","list");
          newElement.setAttribute("draggable","true");
          newElement.setAttribute("onclick","openList(event)");
          listDrag(newElement);
          e.target.parentNode.insertBefore(newElement,e.target); // 要素追加

          newElement = document.createElement("p"); // p要素作成
          newElement.setAttribute("id","listname" + result); // 要素に属性を設定
          newElement.setAttribute("class","listname");
          newElement.innerHTML = "untitled";
          let elm_div;
          eval("elm_div = document.getElementById('list" + result + "');");
          elm_div.appendChild(newElement); // 要素追加

          newElement = document.createElement("ul"); // ul要素作成
          newElement.setAttribute("class","drag-item"); // 要素に属性を設定
          elm_div.appendChild(newElement); // 要素追加

          newElement = document.createElement("li"); // li要素作成
          newElement.setAttribute("id","plus" + result); // 要素に属性を設定
          newElement.setAttribute("class","item plus");
          newElement.setAttribute("onclick","addItem(event)");
          newElement.innerHTML = "+";
          let elm_ul = elm_div.querySelector('ul');
          elm_ul.appendChild(newElement); // 要素追加

          newElement = document.createElement("script"); // script要素作成
          newElement.innerHTML = "let arr_item" + result + " = new Array();";
          elm_div.appendChild(newElement); // 要素追加
        });
      }
    }

    // list編集画面開く
    function openList(e) {
      if(e.target.id.match(/listname/)){
        // listテーブル取得
        let listid = e.target.id.substr(8);
        let obj = {
          userid: userid,
          deskid: deskid,
          listid: listid,
        };
        let promise = postFetch_getlist(obj);// listテーブル取得
        promise.then(result => {
          // データの中身だけ抽出
          for (const [key, value] of Object.entries(result)) {
            if(key == 0){
              obj_openlist = value;
            }
          }
          // フォームに転記
          for (const [key, value] of Object.entries(obj_openlist)) {
            if(key.match(/listname/)){
              eval("document.form_list." + key + ".value = '" + value + "';");
            }
          }
          // ダイアログ表示
          let openlist = document.getElementById('js-openlist');
          openlist.classList.add('is-show');
        });
      }
    }

    // list編集画面閉じる
    function closeList(e){
      // ダイアログ閉じる
      let openlist = document.getElementById('js-openlist');
      openlist.classList.remove('is-show');
    }

    // list更新
    function changeList(e){
      // obj_openlistとformdataを比較して変更されたものだけformdataにいれる
      let change_flg = false;
      let change_listname_flg = false;
      const formdata = new FormData();
      formdata.append('listid', obj_openlist['listid']);
      let openlist,changelist,changelistname;
      for (const elm of document.form_list) {
        eval("openlist = obj_openlist." + elm.name);
        eval("changelist = document.form_list." + elm.name);
        if(elm.name.match(/listname/)){
          if(openlist != changelist.value){
            formdata.append(elm.name, changelist.value);
            change_flg = true;
            change_listname_flg = true;
            changelistname = changelist.value;
          }
        }
      }
      if(change_flg){
        // formdataをfetchで更新
        let promise = postFetch_changelist(formdata);// list更新
        promise.then(result => {
          if(Object.keys(result).length){//返値（エラー）があるときアラート表示
            let arr = "";
            for (const [key, value] of Object.entries(result)) {
              arr += value + "\n";
            }
            alert(arr);
          }else{
            closeList();//編集画面を閉じる
            // listのlistname変更
            if(change_listname_flg){
              let listelm;
              eval("listelm = document.getElementById('listname" + obj_openlist['listid'] + "');");
              listelm.innerHTML = changelistname;
            }
          }
        });
      }
    }

    // list削除
    function deleteList(e){
      // DBから削除
      let elm,obj;
      eval("elm = document.getElementById('list" + obj_openlist['listid'] + "');");
      let div = elm.parentNode.querySelectorAll("div");
      let index = Array.prototype.indexOf.call(div,elm) + 1 ;
      arr_list.splice(index - 1,1);// arr_list配列から削除
      arr_list.push(null);// nullを追加
      array = arr_list;
      obj = Object.assign({}, ...array.map((value,index) => ({
        ['listid' + (index + 1)]: value,
      })));
      obj.userid = obj_openlist['userid'];
      obj.deskid = obj_openlist['deskid'];
      obj.listid = obj_openlist['listid'];
      postFetch_deletelist(obj);// list_table、desk_tableからlist削除
      arr_list.pop();// nullを削除
      closeList();//編集画面を閉じる
      elm.remove();//list要素削除
    }

    // item追加
    function addItem(e){
      let listid = e.target.id.substr(4);
      let flg;
      eval("flg = arr_item" + listid + ".length < 30");
      if(flg){// item上限30件まで
        // DBに書き込み
        let obj = {
          userid: userid,
          deskid: deskid,
          listid: listid,
          itemname: "",
          text: "",
          image: "",
          color: "#ffffff",
          tag: "",
          checkable: 0,
          checked: 0,
          pin: 0,
          limitdate: "0000-00-00 00:00:00",
        };
        let promise = postFetch_additem(obj);// item_tableにitem追加
        promise.then(result => {
          let obj2;
          eval("arr_item" + listid + ".push('" + result + "');");// arr_item*にitem追加
          eval("array = arr_item" + listid + ";");
          obj2 = Object.assign({}, ...array.map((value,index) => ({
            ['itemid' + (index + 1)]: value,
          })));
          obj2.listid = listid;
          postFetch_list_table(obj2);// list_table更新

          // 要素追加
          let newElement = document.createElement("li"); // li要素作成
          newElement.setAttribute("id","item" + result); // 要素に属性を設定
          newElement.setAttribute("class","item");
          newElement.setAttribute("draggable","true");
          newElement.setAttribute("onclick","openItem(event)");
          newElement.setAttribute("style","background:#ffffff");
          itemDrag(newElement);
          e.target.parentNode.appendChild(newElement); // 要素追加

          // liの中に要素追加
          // input
          newElement = document.createElement("input"); // input要素作成
          newElement.setAttribute("type","hidden"); // 要素に属性を設定
          newElement.setAttribute("id","checkable" + result);
          newElement.setAttribute("class","checkable");
          newElement.setAttribute("onchange","changeChecked(event)");
          newElement.checked = false;
          let elm_li = e.target.parentNode.querySelector('#item' + result);
          elm_li.appendChild(newElement); // 要素追加

          // p
          newElement = document.createElement("p"); // p要素作成
          newElement.setAttribute("class","itemname"); // 要素に属性を設定
          elm_li.appendChild(newElement); // 要素追加

          // img
          newElement = document.createElement("img"); // img要素作成
          newElement.setAttribute("src",""); // 要素に属性を設定
          newElement.setAttribute("class","thumbnail");
          newElement.setAttribute("alt","thumbnail");
          newElement.style.display = "none";
          elm_li.appendChild(newElement); // 要素追加
        });
      }
    }

    // item編集画面開く
    function openItem(e) {
      let clickelm;
      if(e.target.id.match(/item/)){
        clickelm = e.target;
      }else if(e.target.id.match(/checkable/)){// チェックボックスクリックのときは何もしない
        return;
      }else{
        clickelm = e.target.parentNode;
      }
      // 選択ファイルクリア
      let imgselectelm = document.getElementsByName('image');
      imgselectelm[0].value = "";
      // itemテーブル取得
      let listid = clickelm.parentNode.parentNode.id.substr(4);
      let itemid = clickelm.id.substr(4);
      let obj = {
        userid: userid,
        deskid: deskid,
        listid: listid,
        itemid: itemid,
      };
      let promise = postFetch_getitem(obj);// itemテーブル取得
      promise.then(result => {
        // データの中身だけ抽出
        for (const [key, value] of Object.entries(result)) {
          if(key == 0){
            obj_openitem = value;
          }
        }
        // フォームに転記
        for (const [key, value] of Object.entries(obj_openitem)) {
          if(key.match(/itemname/) || key.match(/text/) || key.match(/tag/)){
            eval("document.form_item." + key + ".value = value;");
          }else if(key.match(/checkable/) || key.match(/checked/) || key.match(/pin/)){
            eval("document.form_item." + key + ".checked = " + value + ";");
          }else if(key.match(/color/)){
            if(value.match(/#ffffff/i)){// ホワイト
              eval("document.form_item." + key + "[0].checked = true;");
            }else if(value.match(/#ff9999/i)){// レッド
              eval("document.form_item." + key + "[1].checked = true;");
            }else if(value.match(/#ffbb77/i)){// オレンジ
              eval("document.form_item." + key + "[2].checked = true;");
            }else if(value.match(/#ffdd99/i)){// イエロー
              eval("document.form_item." + key + "[3].checked = true;");
            }else if(value.match(/#bbdd99/i)){// グリーン
              eval("document.form_item." + key + "[4].checked = true;");
            }else if(value.match(/#99ddee/i)){// シアン
              eval("document.form_item." + key + "[5].checked = true;");
            }else if(value.match(/#99bbff/i)){// ブルー
              eval("document.form_item." + key + "[6].checked = true;");
            }else if(value.match(/#ee99ee/i)){// マゼンタ
              eval("document.form_item." + key + "[7].checked = true;");
            }
          }else if(key.match(/createdate/) || key.match(/updatedate/) || key.match(/limitdate/)){
            let elm = document.getElementById(key);
            if(key.match(/createdate/)){
              elm.innerHTML = '作成日時：';
              elm.innerHTML += value;
            }else if(key.match(/updatedate/)){
              elm.innerHTML = '更新日時：';
              elm.innerHTML += value;
            }else if(key.match(/limitdate/)){
              // elm.innerHTML = '期限：';
              // elm.innerHTML += value;
            }
          }else if(key.match(/image/)){
            let imageview = document.getElementById('imageview');
            if(value.length){
              eval("imageview.setAttribute('src','" + value + "');");
              imageview.style.display = "inherit";
            }else{
              imageview.setAttribute("src","");
              imageview.style.display = "none";
            }
          }
        }
        // ダイアログ表示
        let openitem = document.getElementById('js-openitem');
        openitem.classList.add('is-show');
      });
    }

    // item編集画面閉じる
    function closeItem(e){
      // ダイアログ閉じる
      let openitem = document.getElementById('js-openitem');
      openitem.classList.remove('is-show');
    }

    // item更新
    function changeItem(e){
      // obj_openitemとformdataを比較して変更されたものだけformdataにいれる
      let obj;
      let change_flg = false;
      let change_itemname_flg = false;
      let change_checkable_flg = false;
      let change_checked_flg = false;
      let change_pin_flg = false;
      let change_color_flg = false;
      let change_image_flg = false;
      const formdata = new FormData();
      formdata.append('itemid', obj_openitem['itemid']);
      let openitem,changeitem,changeitemname,changecheckable,changechecked,changepin,changecolor,changeimage;
      for (const elm of document.form_item) {
        eval("openitem = obj_openitem." + elm.name);
        eval("changeitem = document.form_item." + elm.name);
        if(elm.name.match(/itemname/) || elm.name.match(/text/) || elm.name.match(/tag/)){
          if(openitem != changeitem.value){
            // DB更新用
            formdata.append(elm.name, changeitem.value);
            change_flg = true;
            // 画面更新用
            if(elm.name.match(/itemname/)){
              change_itemname_flg = true;
              changeitemname = changeitem.value;
            }
          }
        }else if(elm.name.match(/checkable/) || elm.name.match(/checked/) || elm.name.match(/pin/)){
          if(openitem != changeitem.checked){
            // DB更新用
            if(changeitem.checked){
              formdata.append(elm.name, 1);
            }else{
              formdata.append(elm.name, 0);
            }
            change_flg = true;
            // 画面更新用
            if(elm.name.match(/checkable/)){
              change_checkable_flg = true;
              changecheckable = changeitem.checked;
            }else if(elm.name.match(/checked/)){
              change_checked_flg = true;
              changechecked = changeitem.checked;
            }else if(elm.name.match(/pin/)){
              change_pin_flg = true;
              changepin = changeitem.checked;
            }
          }
        }else if(elm.name.match(/color/)){
          if(openitem != changeitem.value){
            // DB更新用
            formdata.append(elm.name, changeitem.value);
            change_flg = true;
            // 画面更新用
            change_color_flg = true;
            changecolor = changeitem.value;
          }
        }else if(elm.name.match(/image/)){
          let imgelm = document.getElementById('imageview');
          if(openitem != imgelm.getAttribute("src")){// 変更あり
            // DB更新用
            if(imgelm.getAttribute("src")){// 変更後の画像あり
              formdata.append(elm.name, elm.files[0]);
            }else{// 変更後画像なし（削除）
              formdata.append(elm.name, imgelm.getAttribute("src"));
            }
            if(openitem){// 旧画像はimagesから削除する
              postFetch_deleteimage(openitem);// image削除
            }
            change_flg = true;
            // 画面更新用
            change_image_flg = true;
          }
        // }else if(elm.name.match(/limitdate/)){
        //   // 未実装
        }
      }
      if(change_flg){
        const date = new Date();
        const Y = date.getFullYear();
        const M = ("00" + (date.getMonth()+1)).slice(-2);
        const D = ("00" + date.getDate()).slice(-2);
        const h = ("00" + date.getHours()).slice(-2);
        const m = ("00" + date.getMinutes()).slice(-2);
        const s = ("00" + date.getSeconds()).slice(-2);
        formdata.append('updatedate', Y + "-" + M + "-" + D + " " + h + ":" + m + ":" + s);

        // formdataをfetchで更新
        let promise = postFetch_changeitem(formdata);// item更新
        promise.then(result => {
          if(Object.keys(result).length){//返値（エラー）があるときアラート表示
            let arr = "";
            for (const [key, value] of Object.entries(result)) {
              arr += value + "\n";
            }
            alert(arr);
          }else{
            closeItem();//編集画面を閉じる
            // listのitem要素変更
            let itemelm,itemnameelm,inputelm,imgelm;
            eval("itemelm = document.getElementById('item" + obj_openitem['itemid'] + "');");
            if(change_itemname_flg){
              itemnameelm = itemelm.querySelector('p');
              itemnameelm.innerHTML = changeitemname.replace(/\r?\n/g, '<br>');
            }
            if(change_color_flg){
              itemelm.setAttribute('style','background:' + changecolor);
            }
            if(change_pin_flg){
              itemelm.classList.toggle('pin');
              // pinがオンになったときitemを先頭に移動
              if(changepin){
                // DB更新用
                let li = itemelm.parentNode.querySelectorAll("li");
                let index = Array.prototype.indexOf.call(li,itemelm);
                eval("arr_item" + obj_openitem['listid'] + ".splice(0,0,obj_openitem['itemid']);");// arr_item*の先頭にitem移動
                eval("arr_item" + obj_openitem['listid'] + ".splice(index,1);");
                eval("array = arr_item" + obj_openitem['listid'] + ";");
                obj = Object.assign({}, ...array.map((value,index) => ({
                  ['itemid' + (index + 1)]: value,
                })));
                obj.listid = obj_openitem['listid'];
                let promise = postFetch_list_table(obj);// listテーブル更新
                // 画面更新用
                itemelm.parentNode.insertBefore(itemelm, li[1]);
              }
            }
            if(change_image_flg){
              imgelm = itemelm.querySelector('img');
              // 画像url取得
              obj = {
                userid: obj_openitem['userid'],
                deskid: obj_openitem['deskid'],
                listid: obj_openitem['listid'],
                itemid: obj_openitem['itemid'],
              };
              let promise = postFetch_getitem(obj);// itemテーブル取得
              promise.then(result => {
                imgelm.setAttribute("src",result[0].image); // 要素に属性を設定images
                if(result[0].image.length){
                  imgelm.style.display = "inherit"
                }else{
                  imgelm.style.display = "none"
                }
              });
            }
            if(change_checkable_flg){
              inputelm = itemelm.querySelector('input');
              if(changecheckable){
                inputelm.setAttribute("type","checkbox");
              }else{
                inputelm.setAttribute("type","hidden");
              }
            }
            if(change_checked_flg){
              inputelm = itemelm.querySelector('input');
              if(inputelm){
                inputelm.checked = changechecked;
              }
            }
          }
        });
      }
    }

    // item削除
    function deleteItem(e){
      // DBから削除
      let elm,obj;
      eval("elm = document.getElementById('item" + obj_openitem['itemid'] + "');");
      let li = elm.parentNode.querySelectorAll("li");
      let index = Array.prototype.indexOf.call(li,elm);
      eval("arr_item" + obj_openitem['listid'] + ".splice(index - 1,1);");// arr_item*配列から削除
      eval("arr_item" + obj_openitem['listid'] + ".push(null);");// nullを追加
      eval("array = arr_item" + obj_openitem['listid'] + ";");
      obj = Object.assign({}, ...array.map((value,index) => ({
        ['itemid' + (index + 1)]: value,
      })));
      obj.userid = obj_openitem['userid'];
      obj.deskid = obj_openitem['deskid'];
      obj.listid = obj_openitem['listid']; 
      obj.itemid = obj_openitem['itemid'];
      postFetch_deleteitem(obj);// item_table、list_tableからitem削除
      eval("arr_item" + obj_openitem['listid'] + ".pop();");// nullを削除
      closeItem();//編集画面を閉じる
      elm.remove();//item要素削除
    }

    // image変更
    function imgChange(e){
      let imgelm = document.getElementById('imageview');
      // FileReaderを生成
      let fileReader = new FileReader();
      let file = e.target.files[0];
      // 読み込み完了時の処理を追加
      fileReader.onload = function(){
        imgelm.setAttribute("src",this.result);
        imgelm.style.display = "inherit";
      };
      // ファイルの読み込み(Data URI Schemeの取得)
      fileReader.readAsDataURL(file);
    }

    // image削除
    function deleteimage(){
      let imgelm = document.getElementById('imageview');
      imgelm.setAttribute("src","");
      imgelm.style.display = "none"
    }
    
    // checked更新
    function changeChecked(e){
      // list上のチェックボックスを変更したとき
      const formdata = new FormData();
      let itemid = e.target.parentNode.id.substr(4);
      formdata.append('itemid', itemid);
      // DB更新用
      if(e.target.checked){
        formdata.append("checked", 1);
      }else{
        formdata.append("checked", 0);
      }
      // 画面更新用
      const date = new Date();
      const Y = date.getFullYear();
      const M = ("00" + (date.getMonth()+1)).slice(-2);
      const D = ("00" + date.getDate()).slice(-2);
      const h = ("00" + date.getHours()).slice(-2);
      const m = ("00" + date.getMinutes()).slice(-2);
      const s = ("00" + date.getSeconds()).slice(-2);
      formdata.append('updatedate', Y + "-" + M + "-" + D + " " + h + ":" + m + ":" + s);
      // formdataをfetchで更新
      let promise = postFetch_changeitem(formdata);// item更新
      promise.then(result => {
        if(Object.keys(result).length){//返値（エラー）があるときアラート表示
          let arr = "";
          for (const [key, value] of Object.entries(result)) {
            arr += value + "\n";
          }
          alert(arr);
        }
      });
    }

    // listドラッグ
    let dragid;
    function listDrag(elm){
      elm.ondragstart = function () {
        event.dataTransfer.setData('text/plain', event.target.id);
        dragid = event.target.id;
        event.dataTransfer.dropEffect = "move";
      };
      elm.ondragover = function () {
        if(dragid.match(/list/)){
          event.preventDefault();
          event.dataTransfer.dropEffect = "move";
          // console.log(this.tagName);
          let rect = this.getBoundingClientRect();
          if ((event.clientX - rect.left) > (this.clientWidth / 2)) {//マウスカーソルの位置が要素の半分より右
            this.style.borderLeft = '';
            this.style.borderRight = '100px solid #eee';
          } else {//マウスカーソルの位置が要素の半分より左
            this.style.borderLeft = '100px solid #eee';
            this.style.borderRight = '';
          }
        }
      };
      elm.ondragleave = function () {
        // console.log("aa");
        this.style.borderLeft = '';
        this.style.borderRight = '';
      };
      elm.ondrop = function () {
        event.preventDefault();
        if(dragid.match(/list/)){
          let elm_drag = document.getElementById(dragid);
          let rect = this.getBoundingClientRect();

          // list配列を並び替える
          let div,dropindex,dragindex;
          let draglist = elm_drag.id.substr(4)//dragしているlist番号を取得
          let droplist = this.id.substr(4)//dropしたlist番号を取得
          div = elm_drag.parentNode.querySelectorAll("div");
          dragindex = Array.prototype.indexOf.call(div,elm_drag);
          dropindex = Array.prototype.indexOf.call(div,this);

          if ((event.clientX - rect.left) > (this.clientWidth / 2)) {//マウスカーソルの位置が要素の半分より右
            this.parentNode.insertBefore(elm_drag, this.nextSibling);
            dropindex += 1;
          } else {//マウスカーソルの位置が要素の半分より左
            this.parentNode.insertBefore(elm_drag, this);
          }
          if(dropindex < dragindex){
            dragindex += 1;
          }

          arr_list.splice(dropindex,0,draglist);
          arr_list.splice(dragindex,1);

          this.style.borderLeft = '';
          this.style.borderRight = '';
          
          // DBに書き込み
          let obj;
          array = arr_list;
          obj = Object.assign({}, ...array.map((value,index) => ({
            ['listid' + (index + 1)]: value,
          })));
          obj.deskid = this.parentNode.id.substr(4);//desk番号を取得
          postFetch_desk_table(obj);
        }
      };
    };

    // itemドラッグ
    function itemDrag(elm){
      elm.ondragstart = function () {
        event.dataTransfer.setData('text/plain', event.target.id);
        dragid = event.target.id;
        event.dataTransfer.dropEffect = "move";
      };
      elm.ondragover = function () {
        event.preventDefault();
        event.dataTransfer.dropEffect = "move";
        if(dragid.match(/item/)){
          let rect = this.getBoundingClientRect();
          if ((event.clientY - rect.top) > (this.clientHeight / 2) || this.id.match(/plus/)) {//マウスカーソルの位置が要素の半分より下 or //+ボタンは下にのみ挿入可
            this.style.borderTop = '';
            this.style.borderBottom = '20px solid #eee';
          } else {//マウスカーソルの位置が要素の半分より上
            this.style.borderTop = '20px solid #eee';
            this.style.borderBottom = '';
          }
        }
      };
      elm.ondragleave = function () {
        this.style.borderTop = '';
        this.style.borderBottom = '';
      };
      elm.ondrop = function () {
        event.preventDefault();
        if(dragid.match(/item/)){
          let elm_drag = document.getElementById(dragid);
          let rect = this.getBoundingClientRect();
          // item配列を並び替える
          let ul,li,dropindex,dragindex;
          let dragitem = elm_drag.id.substr(4);//dragしているitem番号を取得
          let draglist = elm_drag.parentNode.parentNode.id.substr(4);//dragしているitemのlist番号を取得
          let dropitem = this.id.substr(4);//dropしたitem番号を取得
          let droplist = this.parentNode.parentNode.id.substr(4);//dropしたlist番号を取得
          ul = elm_drag.parentNode;
          li = ul.querySelectorAll("li");
          dragindex = Array.prototype.indexOf.call(li,elm_drag)-1;
          ul = this.parentNode;
          li = ul.querySelectorAll("li");
          dropindex = Array.prototype.indexOf.call(li,this)-1;

          if ((event.clientY - rect.top) > (this.clientHeight / 2) || this.id.match(/plus/)) {//マウスカーソルの位置が要素の半分より下 or //+ボタンは下にのみ挿入可
            this.parentNode.insertBefore(elm_drag, this.nextSibling);
            dropindex += 1;
          } else {//マウスカーソルの位置が要素の半分より上
            this.parentNode.insertBefore(elm_drag, this);
          }
          if(draglist === droplist && dropindex < dragindex){
            dragindex += 1;
          }

          eval("arr_item" + droplist + ".splice(dropindex,0,dragitem);");
          eval("arr_item" + draglist + ".splice(dragindex,1);");

          this.style.borderTop = '';
          this.style.borderBottom = '';

          // DBに書き込み
          const formdata = new FormData();
          if(draglist != droplist){
            eval("array = arr_item" + droplist + ";");
            obj = Object.assign({}, ...array.map((value,index) => ({
              ['itemid' + (index + 1)]: value,
            })));
            obj.listid = droplist;
            postFetch_list_table(obj);// list_table更新（dropしたlist）
            eval("arr_item" + draglist + ".push(null);");

            formdata.append('itemid', dragitem);
            formdata.append('listid', droplist);
            postFetch_changeitem(formdata);// item_table更新
          }
          eval("array = arr_item" + draglist + ";");
          obj = Object.assign({}, ...array.map((value,index) => ({
            ['itemid' + (index + 1)]: value,
          })));
          obj.listid = draglist;
          postFetch_list_table(obj);// list_table更新（drag開始したlist）
        }
      };
    };
    
    document.querySelectorAll('.list').forEach(listDrag);
    document.querySelectorAll('.item').forEach(itemDrag);
    
  </script>
</body>
</html>