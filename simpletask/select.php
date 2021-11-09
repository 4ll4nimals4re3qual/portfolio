<?php
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
    // exit('データベースに接続できません：'.$e -> getmessage());
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

  // desk一覧描画
  function viewDesk($userid,$userdata_table){
    echo '<form action="my_top.php" method="post" name="form_selectdesk" id="selectdesk">',PHP_EOL;
    echo '<div id="user'.$userdata_table['userid'].'" class="user">',PHP_EOL;
    echo '<ul class="drag-desk">',PHP_EOL;
    echo '<li id="plus'.$userdata_table['userid'].'" class="desk plus" onclick="addDesk(event)">'.'<span>+</span>新しいデスクを作成'.'</li>',PHP_EOL;
    $arr_desk = array();
    foreach($userdata_table as $key => $value):
      if($value === null):
        break;
      elseif(preg_match('/deskid/',$key)):
        // desk描画
        $arr_desk[] = $value;// jsにdeskの配列を作成 
        $str = "";
        $desk_table = getListId($userid,$value);
        $str .= '<div onclick="openDesk(event)"><span></span><span></span><span></span></div><input type="radio" name="deskid" value="'.$desk_table['deskid'].'" id="deskname'.$desk_table['deskid'].'" onchange="selectDesk(event)"><label for="deskname'.$desk_table['deskid'].'" class="deskname">'.nl2br(htmlspecialchars($desk_table['deskname'],ENT_QUOTES,'UTF-8')).'</label>';
        echo '<li id="desk'.$value.'" class="desk" draggable="true">'.$str.'</li>',PHP_EOL;
      endif;
    endforeach;
    $arr_desk = json_encode($arr_desk);
    echo '</ul>',PHP_EOL;
    echo '<script>',PHP_EOL;
    echo "let arr_desk = $arr_desk;",PHP_EOL;
    echo '</script>',PHP_EOL;
    echo '</div>',PHP_EOL;
    echo '<input type="submit" value="選択したデスクを開く" class="submitbtn" disabled>',PHP_EOL;
    echo '</form>',PHP_EOL;
  }

  $userdata_table = getDeskId($userid);
  // $desk_name = getDeskName($userid);
  
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="select.css">
  <link rel="icon" href="favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Coiny&family=Fredoka+One&family=Palette+Mosaic&display=swap" rel="stylesheet">
  <title>select</title>
</head>
<body>
  <div id="wrapper">
    <header>
      <h1><span>S</span>impletask</h1>
      <div id="nav_menu">
        <input type="checkbox" id="nav_check">
        <label for="nav_check"><span></span><span></span><span></span></label>
        <div id="hidden_show">
          <nav>
            <ul id="nav_list">
              <li><a href="index.html">top</a></li>
              <li><a href="logout.php">log out</a></li>
              <li><a href="deleteuser.php">退会</a></li>
            </ul>
          </nav>
        </div>
      </div>
    </header>
    <main>
      <div class="open" id="js-opendesk">
        <div class="open-inner">
          <div class="close-btn" id="js-close-btn" onclick="closeDesk(event)">×</div>
          <form action="" method="post" name="form_desk">
            <table>
              <tbody>
                <tr>
                  <th wihth="150">デスク</th>
                </tr>
                <tr>
                  <td><input type="text" name="deskname" size="68"></td>
                </tr>
              </tbody>
            </table>
          </form>
          <button id="deskchange_btn" onclick="changeDesk(event)">更新</button>
          <button id="deskdelete_btn" onclick="deleteDesk(event)">削除</button>
        </div>
        <div class="black-background" onclick="closeDesk(event)"></div>
      </div>
<?php 
  echo '<section id="user'.$userid.'">',PHP_EOL;
  $arr_desk = array();
  // desk一覧描画
  viewDesk($userid,$userdata_table);
  echo '</section>',PHP_EOL;

  $pdo = null;
?>
    </main>
  </div>
  <script>
<?php $arr_desk = json_encode($arr_desk); ?>
<?php echo "let userid = ".$userid.";",PHP_EOL; ?>

    let obj_opendesk = {};//deskの中身（変更前）

    // （Fetch）userdataテーブル更新
    const postFetch_userdata_table = (obj) => {
      fetch('fetch_userdata_table.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify(obj)
      });
      // .then(response => response.text())
      // .then(data =>  console.log(data));
    }
    
    // （Fetch）deskテーブル取得
    const postFetch_getdesk = (obj) => {
      return fetch('fetch_getdesk.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify(obj)
      })
      .then(response => response.json());
      // .then(data =>  console.log(data));
    }

    // （Fetch）deskテーブルにdesk追加
    const postFetch_adddesk = (obj) => {
      return fetch('fetch_adddesk.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify(obj)
      })
      .then(response => response.text());
      // .then(data => console.log(data));
    }

    // （Fetch）deskテーブル（リスト名）更新
    const postFetch_changedesk = (obj) => {
      return fetch('fetch_changedesk.php', {
        method: 'POST',
        body: obj
      })
      .then(response => response.json());
      // .then(data => console.log(data));
    }

    // （Fetch）deskテーブル、userdataテーブルからdesk削除
    const postFetch_deletedesk = (obj) => {
      fetch('fetch_deletedesk.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify(obj)
      });
      // .then(response => response.text())
      // .then(data => console.log(data));
    }

    // desk追加
    function addDesk(e){
      if(arr_desk.length < 10){// desk上限10件まで
        // DBに書き込み
        let userid = e.target.id.substr(4);
        let obj = {
          userid: userid,
          deskname: "",
        };
        let promise = postFetch_adddesk(obj);// desk_tableにdesk追加
        promise.then(result => {
          let obj2;
          eval("arr_desk.push('" + result + "');");// arr_deskにdesk追加
          eval("array = arr_desk;");
          obj2 = Object.assign({}, ...array.map((value,index) => ({
            ['deskid' + (index + 1)]: value,
          })));
          obj2.userid = userid;
          postFetch_userdata_table(obj2);// userdata_table更新

          // 要素追加
          let newElement = document.createElement("li"); // li要素作成
          newElement.setAttribute("id","desk" + result); // 要素に属性を設定
          newElement.setAttribute("class","desk");
          newElement.setAttribute("draggable","true");
          deskDrag(newElement);
          e.target.parentNode.appendChild(newElement); // 要素追加

          // liの中に要素追加
          // div
          newElement = document.createElement("div"); // div要素作成
          newElement.setAttribute("onclick","openDesk(event)"); // 要素に属性を設定
          let elm_li = e.target.parentNode.querySelector('#desk' + result);
          elm_li.appendChild(newElement); // 要素追加
          
          // input
          newElement = document.createElement("input"); // input要素作成
          newElement.setAttribute("type","radio"); // 要素に属性を設定
          newElement.setAttribute("name","deskid");
          newElement.setAttribute("value",result);
          newElement.setAttribute("id","deskname" + result);
          newElement.setAttribute("onchange","selectDesk(event)");
          elm_li.appendChild(newElement); // 要素追加
          
          // label
          newElement = document.createElement("label"); // label要素作成
          newElement.setAttribute("for","deskname" + result); // 要素に属性を設定
          newElement.setAttribute("class","deskname");
          newElement.innerHTML = "untitled";
          elm_li.appendChild(newElement); // 要素追加

          // span
          let elm_div = elm_li.querySelector('div');
          newElement = document.createElement("span"); // span要素作成
          elm_div.appendChild(newElement); // 要素追加
          newElement = document.createElement("span"); // span要素作成
          elm_div.appendChild(newElement); // 要素追加
          newElement = document.createElement("span"); // span要素作成
          elm_div.appendChild(newElement); // 要素追加
        });
      };
    }

    // desk編集画面開く
    function openDesk(e) {
      let clickelm;
      if(e.target.tagName.match(/SPAN/)){
        clickelm = e.target.parentNode.parentNode;
      }else{
        clickelm = e.target.parentNode;
      }
      // deskテーブル取得
      let deskid = clickelm.id.substr(4);
      let obj = {
        userid: userid,
        deskid: deskid,
      };
      let promise = postFetch_getdesk(obj);// deskテーブル取得
      promise.then(result => {
        // データの中身だけ抽出
        for (const [key, value] of Object.entries(result)) {
          if(key == 0){
            obj_opendesk = value;
          }
        }
        // フォームに転記
        for (const [key, value] of Object.entries(obj_opendesk)) {
          if(key.match(/deskname/)){
            eval("document.form_desk." + key + ".value = value;");
          }
        }
        // ダイアログ表示
        let opendesk = document.getElementById('js-opendesk');
        opendesk.classList.add('is-show');
      });
    }

    // desk編集画面閉じる
    function closeDesk(e){
      // ダイアログ閉じる
      let opendesk = document.getElementById('js-opendesk');
      opendesk.classList.remove('is-show');
    }

    // desk更新
    function changeDesk(e){
      // obj_opendeskとformdataを比較して変更されたものだけformdataにいれる
      let change_flg = false;
      let change_deskname_flg = false;
      const formdata = new FormData();
      formdata.append('deskid', obj_opendesk['deskid']);
      let opendesk,changedesk,changedeskname;
      for (const elm of document.form_desk) {
        eval("opendesk = obj_opendesk." + elm.name);
        eval("changedesk = document.form_desk." + elm.name);
        if(elm.name.match(/deskname/)){
          if(opendesk != changedesk.value){
            formdata.append(elm.name, changedesk.value);
            change_flg = true;
            change_deskname_flg = true;
            changedeskname = changedesk.value;
          }
        }
      }
      if(change_flg){
        // formdataをfetchで更新
        let promise = postFetch_changedesk(formdata);// desk更新
        promise.then(result => {
          if(Object.keys(result).length){//返値（エラー）があるときアラート表示
            let arr = "";
            for (const [key, value] of Object.entries(result)) {
              arr += value + "\n";
            }
            alert(arr);
          }else{
            closeDesk();//編集画面を閉じる
            // userのdesk要素変更
            let deskelm,desknameelm;
            eval("deskelm = document.getElementById('desk" + obj_opendesk['deskid'] + "');");
            if(change_deskname_flg){
              desknameelm = deskelm.querySelector('label');
              desknameelm.innerHTML = changedeskname;
            }

          }
        });
      }
    }

    // desk削除
    function deleteDesk(e){
      // DBから削除
      let elm;
      eval("elm = document.getElementById('desk" + obj_opendesk['deskid'] + "');");
      let li = elm.parentNode.querySelectorAll("li");
      let index = Array.prototype.indexOf.call(li,elm);
      arr_desk.splice(index - 1,1);// arr_desk配列から削除
      arr_desk.push(null);// nullを追加
      array = arr_desk;
      let obj = Object.assign({}, ...array.map((value,index) => ({
        ['deskid' + (index + 1)]: value,
      })));
      obj.userid = obj_opendesk['userid'];
      obj.deskid = obj_opendesk['deskid'];
      postFetch_deletedesk(obj);// desk_table、userdata_tableからdesk削除
      arr_desk.pop();// nullを削除
      closeDesk();//編集画面を閉じる
      elm.remove();//desk要素削除
    }

    // desk選択
    function selectDesk(e){
      let elm_li,elm_input,elm_submit;
      for(let deskid of arr_desk){
        // 選択したら色変更
        eval('elm_li = document.getElementById("desk' + deskid + '");');
        eval('elm_input = document.getElementById("deskname' + deskid + '");');
        if(elm_input.checked){
          elm_li.style.background = '#069';
        }else{
          elm_li.style.background = '#09f';
        }
      }
      elm_submit = document.querySelector('.submitbtn');
      elm_submit.disabled = false;
      elm_submit.style.background = '#09f';
      elm_submit.style.border = '1px solid #069';
    }

    // deskドラッグ
    let dragid;
    let leaveelm;
    function deskDrag(elm){
      elm.ondragstart = function () {
        event.dataTransfer.setData('text/plain', event.target.id);
        dragid = event.target.id;
        event.dataTransfer.dropEffect = "move";
      };
      elm.ondragover = function () {
        event.preventDefault();
        event.dataTransfer.dropEffect = "move";
        if(dragid.match(/desk/)){
          let rect = this.getBoundingClientRect();
          if(leaveelm){// 広げた隙間を戻す
            leaveelm.style.borderTop = '';
            leaveelm.style.borderBottom = '';
          }
          if ((event.clientY - rect.top) > (this.clientHeight / 2) || this.id.match(/plus/)) {//マウスカーソルの位置が要素の半分より下 or +ボタンは下にのみ挿入可
            this.style.borderTop = '';
            this.style.borderBottom = '50px solid #eee';
          } else {//マウスカーソルの位置が要素の半分より上
            this.style.borderTop = '50px solid #eee';
            this.style.borderBottom = '';
          }
          leaveelm = this;
        }
      };
      elm.ondrop = function () {
        event.preventDefault();
        if(dragid.match(/desk/)){
          let elm_drag = document.getElementById(dragid);
          let rect = this.getBoundingClientRect();
          // desk配列を並び替える
          let ul,li,dropindex,dragindex;
          let dragdesk = elm_drag.id.substr(4);//dragしているdesk番号を取得
          let draguser = elm_drag.parentNode.parentNode.id.substr(4);//dragしているdeskのuser番号を取得
          let dropdesk = this.id.substr(4);//dropしたdesk番号を取得
          ul = elm_drag.parentNode;
          li = ul.querySelectorAll("li");
          dragindex = Array.prototype.indexOf.call(li,elm_drag)-1;
          ul = this.parentNode;
          li = ul.querySelectorAll("li");
          dropindex = Array.prototype.indexOf.call(li,this)-1;

          if ((event.clientY - rect.top) > (this.clientHeight / 2) || this.id.match(/plus/)) {//マウスカーソルの位置が要素の半分より下 or +ボタンは下にのみ挿入可
            this.parentNode.insertBefore(elm_drag, this.nextSibling);
            dropindex += 1;
          } else {//マウスカーソルの位置が要素の半分より上
            this.parentNode.insertBefore(elm_drag, this);
          }
          if(dropindex < dragindex){
            dragindex += 1;
          }
          eval("arr_desk.splice(dropindex,0,dragdesk);");
          eval("arr_desk.splice(dragindex,1);");
          if(leaveelm){// 広げた隙間を戻す
            leaveelm.style.borderTop = '';
            leaveelm.style.borderBottom = '';
          }

          // DBに書き込み
          eval("array = arr_desk;");
          obj = Object.assign({}, ...array.map((value,index) => ({
            ['deskid' + (index + 1)]: value,
          })));
          obj.userid = draguser;
          postFetch_userdata_table(obj);// userdata_table更新
        }
      };
      elm.ondragend = function () {
        if(leaveelm){// 広げた隙間を戻す
          leaveelm.style.borderTop = '';
          leaveelm.style.borderBottom = '';
        }
      };
    };

    document.querySelectorAll('.desk').forEach(deskDrag);

  </script>
</body>
</html>