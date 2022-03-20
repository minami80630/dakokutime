<!doctype html>
<html lang="ja">


<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

  <!-- fontawesome -->
  <script src="https://kit.fontawesome.com/68ddd43c82.js" crossorigin="anonymous"></script>

  <!-- Original CSS -->
  <link href="css/style.css" rel="stylesheet">

  <title>勤怠画面1 | TIME</title>
</head>

<body class="text-center bg-light">
<script>
  // 
  function submitChk(formID)
  {
    const textbox = document.getElementById("user_no");

    // 社員Noの入力有無チェック
    if(textbox.value == "")
    {
      alert("社員番号が入力されていません");
      return false;
    }
    
    // 入力された 社員の存在チェックと 本人確認
    var result = GetEmployeeName(formID);
    if(result == "")
    {
      alert("その社員番号は登録されていません");
      return false;
    }

    /* 確認ダイアログ表示 */
    var flag = confirm(result + "さんですか？");
    return flag;
   
  }
  function GetEmployeeName(formID)
  {
    var result = "";
    var formElement = document.getElementById(formID);
    var formData = new FormData(formElement);
    xhr = new XMLHttpRequest();
    xhr.open("POST", "./UserGet.php", false);
    xhr.onload = function () 
    {
      var response = JSON.parse(this.responseText);
      result = response.result;
	  }

    xhr.send(formData);

    return result;
  }

  function ExecTimeRegistration(buttonKind)
  {
    if(!submitChk("dakokuForm"))
    {
      return;
    }

    const ActionKey = document.getElementById("ActionKey");
    ActionKey.value = buttonKind;

    var formElement = document.getElementById('dakokuForm');
    var formData = new FormData(formElement);
    xhr = new XMLHttpRequest();
    xhr.open("POST", "./CommandProxy.php", false);
    xhr.onload = function () 
    {
      var response = JSON.parse(this.responseText);

      switch(buttonKind)
      {
        case "jobstart":
          alert("出勤登録しました！今日も1日頑張りましょう♪");
          break;
        case "jobend":
          alert("退勤登録しました！お仕事お疲れ様でした!");
          //alert(response["dailypay"]);
          break;
        case "breakstart":
          alert("休憩開始登録しました！ゆっくりお休みください。");
          break;
        case "breakend":
          alert("休憩終了登録しました！後半も頑張りましょう！");
          break;
      }
	  }

    xhr.send(formData);
    return;
  }


  function SetUserNo()
  {
    const textbox = document.getElementById("user_no");
    const ListTextbox = document.getElementById("ListFrom_user_no");

    ListTextbox.value = textbox.value;
  }
</script>

  <header >
    <div class="bg-dark text-secondary text-center ">
      <div class="py-0 pt-4">
        <div>
          <img class="mb-0" src="img/time.png" alt="TIME" width="100" height="100">
        </div>
        <span class="display-6 text-white" id="viewTime">今日の日付を表示します</span>
        <h1 class="display-2 fw-bold text-white mb-0" id="RealtimeClockArea2"><br></h1>
      </div>
    </div>
  </header>

  <form class="form" name="dakokuForm"  id="dakokuForm">
    <div class="bg-dark text-secondary  text-center mt-0 py-1">
      <hr>
      <p class="fs-5 mb-1 text-white">↓ 社員番号を入力してください ↓</p>

      <div class="col form-login ">
        <input type="text" class="form-control mb-3 " name="user_no" id="user_no" placeholder="社員番号" aria-label="First name">
      </div>
    </div>
    <br>

    <div class="d-flex flex-column flex-md-row gap-2 col-8 mx-auto row-eq-height mt-0">
      <button class="w-50 btn btn-lg btn-primary px-5 enter"
        type="submit" name="job_start_btn" 
        onclick="ExecTimeRegistration('jobstart')">
        <i class="fa-solid fa-right-to-bracket"></i> <br> 出勤</button>
      <button class="w-50 btn btn-lg btn-primary"
        type="submit" name="job_end_btn"
        onclick="ExecTimeRegistration('jobend')">
        <i class="fa-solid fa-right-from-bracket"></i><br>退勤</button>
    </div>
    <div class="d-flex flex-column flex-md-row gap-2 col-8 mx-auto">
      <button class="w-50 btn btn-lg btn-primary my-3" 
        type="submit" name="rest_star_btn" 
        onclick="ExecTimeRegistration('breakstart')">休憩開始</button>
      <button class="w-50 btn btn-lg btn-primary my-3" 
        type="submit" name="rest_end_btn"
        onclick="ExecTimeRegistration('breakend')">休憩終了</button>
    </div>
    <input name="ActionKey" id="ActionKey" type="hidden" value="" />
  </form>
  <form class="form" name="ListForm" action="worklist.php" id="ListForm" method="post" onsubmit="return submitChk('ListForm')">
    <input type="hidden"  name="user_no" id="ListFrom_user_no" value="">
    <button class="w-50 btn btn-lg btn-primary my-3" type="submit" name="rest_end_btn" onclick="SetUserNo()">勤怠一覧</button>
  </form>


  <!-- 日付表示JavaScriptソース -->
  <script>
    document.getElementById("viewTime").innerHTML = getNowTime();

    function getNowTime() {
      var now = new Date();
      var year = now.getFullYear(); //年
      var mon = now.getMonth() + 1; //月 １を足す
      var day = now.getDate(); //日
      var you = now.getDay(); //曜日

      //曜日の配列（日～土）
      var youbi = new Array("日", "月", "火", "水", "木", "金", "土");
      //出力
      var s = year + "年" + mon + "月" + day + "日 (" + youbi[you] + ")";
      return s;
    }
  </script>
  <!--ここまで-->

  <!-- リアルタイム時計表示JavaScriptソース -->
  <script>
    function set2fig(num) {
      // 桁数が1桁だったら先頭に0を加えて2桁に調整する
      var ret;
      if (num < 10) {
        ret = "0" + num;
      } else {
        ret = num;
      }
      return ret;
    }

    function showClock2() {
      var nowTime = new Date();
      var nowHour = set2fig(nowTime.getHours());
      var nowMin = set2fig(nowTime.getMinutes());
      var nowSec = set2fig(nowTime.getSeconds());
      var msg = nowHour + ":" + nowMin + ":" + nowSec;
      document.getElementById("RealtimeClockArea2").innerHTML = msg;
    }
    setInterval('showClock2()', 500);
  </script>
  <!--ここまで-->


  <script src="../assets/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Optional JavaScript; choose one of the two! -->

  <!-- Option 1: Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

  <!-- Option 2: Separate Popper and Bootstrap JS -->
  <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
    -->
</body>

</html>