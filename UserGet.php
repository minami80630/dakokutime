<?php
    require_once(dirname(__FILE__) . '/function.php');

    $userno = $_POST['user_no'];
    $response['result'] ="";
    if(isset($userno))
    {
        $dbh = connect_db();
        // 社員テーブルから 名前、時給を取得 予定
        $sql = "select user_no,name,paying from user where id = $userno";
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        if($stmt->rowCount() != 0)
        {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $response['result'] = $result['name'];
        }
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
