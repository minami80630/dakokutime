<?php
// ===============================================
// メイン画面からの 機能ボタン別機能実行振り分け処理
// このPHPへの必須情報は社員NO 
// 入力された 社員Noが登録されていることは 
// 呼び出し元でチェック済みのこと
// ===============================================

// 利用する外部PHP
require_once(dirname(__FILE__) . '/function.php');

if (!isset($_POST['user_no']) || !isset($_POST['ActionKey'])) {
    //// 社員NOがPostされてこない場合には処理不可能
    $response['result'] = "NG";
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

// HTML 機能ボタンに応じた分岐
$actionKey = $_POST['ActionKey'];
switch($actionKey)
{
  case "jobstart":
    JobStartButtonAction($_POST['user_no']);
    break;
  case "jobend":
    JobEndButtonAction($_POST['user_no']);
    //$response['dailypay'] = GetDailypay($_POST['user_no']);
    break;
  case "breakstart":
    RestStartButtonAction($_POST['user_no']);
    break;
  case "breakend":
    RestEndButtonAction($_POST['user_no']);
    break;
}

$response['result'] = $actionKey;
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>