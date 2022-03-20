<?php

// ====================================================
//  Localhost MySQL 接続
// ====================================================
function connect_db()
{
// $dsn = 'mysql:dbname=dakoku_app;port=8889;host=localhost';
    $dsn = 'mysql:dbname=dakoku_app;host=localhost;charset=utf8;';
    $user = 'dakoku_user';
    $password = '80630';
    $dbh = new PDO($dsn, $user, $password);
    return $dbh;
}

// ====================================================
//  Heroku MySQL 接続
// ====================================================
/*
function connect_db()
{
    //mysql://b47e03f8b4929d:5d0f3671@us-cdbr-east-05.cleardb.net/heroku_5238230432e57ac?reconnect=true
    $dsn = 'mysql:dbname=heroku_5238230432e57ac;host=us-cdbr-east-05.cleardb.net;charset=utf8;';
    $user = 'b47e03f8b4929d';
    $password = '5d0f3671';
    $dbh = new PDO($dsn, $user, $password);
    return $dbh;
}
*/


// 日付を日(曜日)の形式に変換する
function time_format_dw($date)
{
    $format_date = NULL;
    $week = array('日', '月', '火', '水', '木', '金', '土');

    if ($date) {
        $format_date = date('j(' . $week[date('w', strtotime($date))] . ')', strtotime($date));
    }

    return $format_date;
}

// 時間のデータ形式を調整する
function format_time($time_str)
{
    if (!$time_str || $time_str == '00:00:00') {
        return NULL;
    } else {
        return date('H:i', strtotime($time_str));
    }
}

// 出勤ボタン処理
// work table に 当日の行を追加後 出勤時間（現在時）を記入
function JobStartButtonAction($userNo)
{
    try {
        // DB接続
        $dbh = connect_db();
        $now = new DateTime('now', new DateTimeZone('Asia/Tokyo'));
        $today = $now->format('Y-m-d');
        $nowTime = $now->format('H:i:s');
        //var_dump($today)  ;
        //var_dump($nowTime)  ;
        //return
        $sql = "INSERT INTO `work` (`id`, `user_id`, `date`, `start_time`, `end_time`, `breakstart_time`, `breakend_time`) 
        VALUES (NULL,$userNo, '$today', '$nowTime', NULL, NULL, NULL)";
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
    } catch (PDOException $error) {
        echo "接続失敗:" . $error->getMessage();
        die();
    } finally {
        $dbh = null;
    }
}

// 退勤ボタン処理
// work table に当日分の行が存在していること 出勤時間が登録されていること
// 上記条件を満たせば 退勤時間（現在時）を記入
function JobEndButtonAction($userNo)
{
    try {
        // DB接続
        $dbh = connect_db();
        $now = new DateTime('now', new DateTimeZone('Asia/Tokyo'));
        $today = $now->format('Y-m-d');
        $nowTime = $now->format('H:i:s');

        $sql = "UPDATE `work` SET `end_time` = '$nowTime' WHERE `work`.`date` = '$today' and `work`.`user_id` = $userNo ";
        //$sql ="UPDATE `work` SET `end_time` = '00:05:31' WHERE `work`.`id` = 41"; 
        $stmt = $dbh->prepare($sql);
        $res = $stmt->execute();
    } catch (PDOException $error) {
        echo "接続失敗:" . $error->getMessage();
        die();
    } finally {
        $dbh = null;
    }
}

// 休憩開始ボタン処理
// work table に当日分の行が存在していること 出勤時間が登録されていること　退勤が登録されていないこと
// 上記条件を満たせば 休憩開始時間（現在時）を記入
function RestStartButtonAction($userNo)
{
    try {
        // DB接続
        $dbh = connect_db();
        $now = new DateTime('now', new DateTimeZone('Asia/Tokyo'));
        $today = $now->format('Y-m-d');
        $nowTime = $now->format('H:i:s');

        $sql = "UPDATE `work` SET `breakstart_time` = '$nowTime' 
                WHERE `work`.`date` = '$today' and `work`.`user_id` = $userNo and `work`.`end_time` IS NULL";
        //$sql = "SELECT `work`.`end_time` FROM `work` WHERE 'end_time' IS NULL ";
        //UPDATE `work` SET `breakstart_time` = '01:14:43' WHERE `work`.`id` = 41; 
        $stmt = $dbh->prepare($sql);
        $res = $stmt->execute();
    } catch (PDOException $error) {
        echo "接続失敗:" . $error->getMessage();
        die();
    } finally {
        $dbh = null;
    }
}

// 休憩終了ボタン処理
// work table に当日分の行が存在していること 出勤時間が登録されていること　退勤時間が登録されていないこと
// 休憩開始時間が登録されていること
// 上記条件を満たせば 休憩終了時間（現在時）を記入
function RestEndButtonAction($userNo)
{
    try {
    // DB接続
    $dbh = connect_db();
    $now = new DateTime('now', new DateTimeZone('Asia/Tokyo'));
    $today = $now->format('Y-m-d');
    $nowTime = $now->format('H:i:s');

    $sql = "UPDATE `work` SET `breakend_time` = '$nowTime' 
    WHERE `work`.`date` = '$today' and `work`.`user_id` = $userNo and `work`.`end_time` IS NULL and `work`.`breakstart_time` IS NOT NULL ";
    //$sql = "SELECT `work`.`end_time` FROM `work` WHERE 'end_time' IS NULL ";
    //UPDATE `work` SET `breakstart_time` = NULL, `breakend_time` = '02:49:48' WHERE `work`.`id` = 41; 
    $stmt = $dbh->prepare($sql);
    $res = $stmt->execute();
    } catch (PDOException $error) {
        echo "接続失敗:" . $error->getMessage();
        die();
    } finally {
        $dbh = null;
    }
}

function GetDailypay($userNo)
{
    $dbh = connect_db();

    // 社員テーブルから 情報取得
    $sql = "select id,user_no,name,paying from user where id = $userno";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $resultUser = $stmt->fetch(PDO::FETCH_ASSOC);
    $paying = $resultUser['paying'];

    return $paying;
    $yyyymm = date('Y-m');
     
    $sql = "SELECT date , work.* FROM work WHERE user_id = :user_id AND DATE_FORMAT(date, '%Y-%m') = :date" ;
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':user_id', $userno, PDO::PARAM_INT);
    $stmt->bindValue(':date', $yyyymm);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $oneRecOutput = GetOneRecordOutPutValues($result, $paying);
    return $oneRecOuput['dailypay'];
}

function GetOneRecordOutPutValues($oneRecord, $paying)
{
    $result['start_time'] = "";
    $result['end_time'] = "";
    $result['break_time'] = "";
    $result['dailypay'] = "";
    $result['workall'] = "";
    $result['worktimeSum'] = 0;
    $result['dailypaySum'] = 0;
    
    if ($oneRecord['start_time']) {
        $result['start_time'] = date('H:i', strtotime($oneRecord['start_time']));
    }

    if ($oneRecord['end_time']) {
        $result['end_time'] = date('H:i', strtotime($oneRecord['end_time']));
    }

    $alltime = -1;
    $breakalltime = 0;
    
    // 勤務時間
    if($oneRecord['start_time'] &&  $oneRecord['end_time'])
    {
        //終了時間-開始時間
        $timestamp = strtotime($oneRecord['start_time']);
        $timestamp2 = strtotime($oneRecord['end_time']);
        $alltime = ($timestamp2 - $timestamp);
    }

    // 休憩時間
    if($oneRecord['breakstart_time'] &&  $oneRecord['breakend_time'])
    {
        //休憩終了-休憩開始
        $timestamp3 = strtotime($oneRecord['breakstart_time']);
        $timestamp4 = strtotime($oneRecord['breakend_time']);

        $breakalltime = ($timestamp4 - $timestamp3);
        $result['break_time'] = sprintf("%02d:%02d", $breakalltime / 3600, ($breakalltime % 3600) / 60 );
    }

    if($alltime != -1)
    {
        $workTimeall = ($alltime - $breakalltime);
        $result['worktimeSum'] = $workTimeall;
        $result['worktimeDay'] = sprintf("%02d:%02d", $workTimeall / 3600, ($workTimeall % 3600) / 60 );
        $result['dailypaySum']  = ($workTimeall / 3600) * $paying;
        $result['dailypay'] = number_format($result['dailypaySum']);
    }

    return $result;
}

?>

