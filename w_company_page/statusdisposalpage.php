<?php
// データベースの情報
$servername = "localhost";
$username = "dbuser";
$password = "ecc";
$dbname = "food";

$conn = new mysqli($servername, $username, $password, $dbname);

//接続のチェック 
if ($conn->connect_error) {
    die("アクセス失敗: " . $conn->connect_error);
}

$stmt1 = $conn->prepare("SELECT * FROM store");
$stmt1->execute();
$store_info = $stmt1->get_result();

// Storeデータを配列に格納
$store_rows = array();
while ($store_row = $store_info->fetch_assoc()) {
    $store_rows[$store_row['STORE_ID']] = $store_row['STORE_NAME'];
}

$stmt2 = $conn->prepare("SELECT * FROM disposal");
$stmt2->execute();
$disposal_info = $stmt2->get_result();

// Storeごとのデータを配列に格納
$store_data = array();
while ($disposal_row = $disposal_info->fetch_assoc()) {
    $store_id = $disposal_row['STORE_ID'];
    if (!isset($store_data[$store_id])) {
        $store_data[$store_id] = array();
    }
    $store_data[$store_id][] = $disposal_row;
}

// ステータスの更新処理
if (isset($_POST['statusChange'])) {
    $disposalId = $_POST['disposalId'];
    $status = $_POST['status'];

    $stmt3 = $conn->prepare("UPDATE disposal SET STATUS = ? WHERE DISPOSAL_ID = ?");
    $stmt3->bind_param("si", $status, $disposalId);
    $stmt3->execute();
    $stmt3->close();
}

$stmt2->close();
$stmt1->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>OpenSeaS管理システム</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/footer.css" />
    <link rel="stylesheet" href="../css/navbar.css" />
    <link rel="stylesheet" href="../css/storeInvnt.css" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container-fluid">
        <nav class="navbar navbar-inverse fixed-top">
            <div class="navbar-header">
                <a class="navbar-brand" href="./w_aboutUs/about.html">OpenSeaS</a>
            </div>
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">ホーム</a></li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">ストア用<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="#">ストアフロント</a></li>
                        <li><a href="./w_disposal_page/registerDisposal.html">廃棄登録</a></li>
                        <li><a href="./w_store_page/storeInfo.html">ストア情報</a></li>
                    </ul>
                </li>
                <li><a href="./w_disposal_page/deliveryDisposal">廃棄情報</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="../w_Account_Register/Register.html"><span class="glyphicon glyphicon-user"></span>
                        新規登録</a>
                </li>
                <li id="user">
                    <a href="../login.php"><span class="glyphicon glyphicon-log-in"></span> ログイン</a>
                </li>
            </ul>
        </nav>
        <div class="text-center">
            <h1 class="mx-auto">会社画面表示</h1>
            <h2>test</h2>
        </div>
        <div class="row">
            <div class="col-sm-2">
                <div id="dashboard">
                    <h3>ダッシュボード</h3>
                    <div class="btn-group-vertical">
                        <button type="button" class="btn btn-lg w-100" id="dash-btn">アイテム登録</button>
                        <button type="button" class="btn btn-lg w-100" id="dash-btn">機能</button>
                        <button type="button" class="btn btn-lg w-100" id="dash-btn">発送問い合わせ</button>
                    </div>
                </div>
            </div>
            <div class="col-sm-10">
                <div id="addItem">

                </div>
                <!-- Inventory management section -->
                <?php foreach ($store_data as $store_id => $disposal_rows): ?>
                    <h3>Store ID:
                        <?php echo $store_id; ?>&nbsp;
                        <?php echo $store_rows[$store_id]; ?>
                    </h3>
                    <table class="table-bordered table-hover" id="inventory">
                        <thead>
                            <tr>
                                <th onclick="sortTable(0)">
                                    廃棄情報 <span class="glyphicon glyphicon-sort"></span>
                                </th>
                                <th onclick="sortTable(1)">
                                    アイテム <span class="glyphicon glyphicon-sort"></span>
                                </th>
                                <th onclick="sortTable(2)">
                                    個数 <span class="glyphicon glyphicon-sort"></span>
                                </th>
                                <th onclick="sortTable(3)">
                                    日付 <span class="glyphicon glyphicon-sort"></span>
                                </th>
                                <th onclick="sortTable(4)">
                                    ステータス <span class="glyphicon glyphicon-sort"></span>
                                </th>
                                <th id="actionColumn"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($disposal_rows as $row): ?>
                                <tr>
                                    <td>
                                        <?php echo $row['DISPOSAL_ID']; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['ITEM']; ?>
                                    </td>



                                    <td>
                                        <?php if ($row['QTY'] == 0): ?>
                                            <span class="zero-qty">なし</span>
                                        <?php else: ?>
                                            <?php echo $row['QTY']; ?>
                                        <?php endif; ?>
                                    </td>


                                    <td>
                                        <?php echo $row['DATE']; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['QTY'] == 0): ?>
                                            <span class="zero-qty">在庫切れ</span>
                                        <?php else: ?>
                                            <?php echo $row['STATUS']; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="deleteButton" data-disposal-id="<?= $row['DISPOSAL_ID']; ?>">削除</button>
                                        <button class="statusChangeButton" data-disposal-id="<?= $row['DISPOSAL_ID']; ?>"
                                            data-toggle="modal" data-target="#statusChangeModal"
                                            onclick="setDisposalId(<?= $row['DISPOSAL_ID']; ?>)">ステータス変更</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Status Change Modal -->
    <div id="statusChangeModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">ステータス変更</h4>
                </div>
                <div class="modal-body">
                    <form action="update_status.php" method="POST">
                        <input type="hidden" id="disposalId" name="disposalId" value="">
                        <div class="form-group">
                            <label for="status">ステータス:</label>
                            <select class="form-control" id="status" name="status">
                                <option value="受取待ち">受取待ち</option>
                                <option value="受取完了">受取完了</option>
                                <option value="在庫切れ">在庫切れ</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" name="statusChange">変更</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        function setDisposalId(disposalId) {
            document.getElementById("disposalId").value = disposalId;
            document.getElementById("statusChangeForm").action = "update_status.php?disposalId=" + disposalId;
        }
    </script>

    <script src="../js/inventory.js"></script>
    <script src="../js/deleteItemFromDisposal.js"></script>
</body>

</html>