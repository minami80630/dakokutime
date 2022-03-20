<?php
require_once(dirname(__FILE__) . '/function.php');


$userno = $_POST['user_no'];

try {
    // DB接続
    $dbh = connect_db();

    // 社員テーブルから 情報取得
    $sql = "select id,user_no,name,paying from user where id = $userno";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $paying = $result['paying'];

    // 業務日報データを取得 条件をセット
    if (isset($_POST['m'])) {
        $yyyymm = $_POST['m'];
        $day_count = date('t', strtotime($yyyymm));
    }else{
        $yyyymm =date('Y-m');
        $day_count=date('t');
    }

    // 勤怠データから 該当社員の該当月のデータを取得
    $sql = "SELECT date , work.* FROM work WHERE user_id = :user_id AND DATE_FORMAT(date, '%Y-%m') = :date" ;
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':user_id', $userno, PDO::PARAM_INT);
    $stmt->bindValue(':date', $yyyymm);
    $stmt->execute();
    $work_list = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);
    
} catch (PDOException $error) {
    echo "接続失敗:" . $error->getMessage();
    die();
}
?>


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



    <title>日報登録 | TIME</title>
</head>

<body class="text-center bg-light">

    <div>
        <img class="mt-3 mb-4" src="img/time.png" alt="TIME" width="100" height="100">
    </div>

    <form class="border rounded bg-white form-time-table" method="post" action="worklist.php">
        <h1 class="h3 mt-2">月別リスト</h1><br>
        <a href="index.php">ホームに戻る</a>

        <input type="hidden" name="user_no" value=<?=$userno?>>
        <select class="form-select rounded-pill mb-3" name="m" onchange="submit(this.form)">
            <option value="<?= date('Y-m') ?>"><?= date('Y/m') ?></option>
            <?php for ($i = 1; $i < 12; $i++) : ?>
                <?php $target_yyyymm = strtotime("- {$i}months"); ?>
                <option value="<?= date('Y-m', $target_yyyymm) ?>" 
                <?php if ($yyyymm == date('Y-m', $target_yyyymm)) echo 'selected' ?>><?= date('Y/m', $target_yyyymm) ?></option>
            <?php endfor; ?>
        </select>

        <table class="table table-bordered">
            <thead>
                <tr class="bg-light">
                    <th scope="col">日</th>
                    <th scope="col">出勤</th>
                    <th scope="col">退勤</th>
                    <th scope="col">休憩時間</th>
                    <th scope="col">勤務時間</th>
                    <th scope="col">日給</th>
                    
                </tr>
            </thead>
            <tbody>
                <?PHP 
                    // Set Zero to Sum.
                    $worktimeSum = 0;
                    $dailypaySum = 0;
                ?>
                <?php for ($i = 1; $i <= $day_count; $i++) : ?>
                    <?php
                    $start_time = '';
                    $end_time = '';
                    $break_time ='';
                    $dailypay = '';
                    $worktimeDay = '';

                    $dayofmonth = sprintf("%s-%02d", $yyyymm, $i);
                    $targetDate = date('Y-m-d', strtotime($dayofmonth));

                    if (isset($work_list[$targetDate])) {
                        $work = $work_list[$targetDate];
                        $oneRecOutput = GetOneRecordOutPutValues($work, $paying);
                        $start_time = $oneRecOutput['start_time'];
                        $end_time = $oneRecOutput['end_time'];
                        $break_time = $oneRecOutput['break_time'];
                        $dailypay = $oneRecOutput['dailypay'];
                        $worktimeDay = $oneRecOutput['worktimeDay'];
                        $worktimeSum += $oneRecOutput['worktimeSum'];;
                        $dailypaySum += $oneRecOutput['dailypaySum'];;
                    }
                    ?>
                    <tr>
                        <th scope="row"><?= time_format_dw($yyyymm . '-' . $i) ?></th>
                        <td><?= $start_time ?></td>
                        <td><?= $end_time ?></td>
                        <td><?= $break_time ?></td>
                        <td><?= $worktimeDay ?></td>
                        <td><?= $dailypay ?></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
            <thead>
                <tr class="bg-light">
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col"><?= sprintf("%02d:%02d", $worktimeSum / 3600, ($worktimeSum % 3600) / 60 ) ?></th>
                    <th scope="col"><?= number_format($dailypaySum) ?></th>
                </tr>
            </thead>
        </table>
    </form>


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