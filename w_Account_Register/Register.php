<?php
//PHP部分完成

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

$sql;
//フォームから送信されたデータを取得
$name = $_POST['name'];
$telephone = $_POST['telephone'];
$email = $_POST['email'];
$password = $_POST['password'];
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$address = $_POST['address'];
$selectedOption = $_POST['selectedOption'];

if ($selectedOption == 'store') {
    $stmt = $conn->prepare("INSERT INTO Store (STORE_NAME, STORE_TEL, STORE_EMAIL, STORE_PASSWORD, STORE_ADDRESS) 
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $telephone, $email, $hashedPassword, $address);
}else{
    $stmt = $conn->prepare("INSERT INTO User (USER_NAME, USER_TEL, USER_EMAIL, USER_PASSWORD, USER_ADDRESS) 
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $telephone, $email, $hashedPassword, $address);
}

$isRegistrationOk = false;
$message;
try {
    if ($stmt->execute()) {
        $conn->commit();
        $isRegistrationOk = true;
        $message = "アカウント作成できました！";
    } else {
        $isRegistrationOk = false;
        $message = "登録に失敗しました！<br>もう一度お試しください！" . $stmt->error;
    }
} catch (Exception $e) {
    // Handle the exception
    $message = "エラーが発生しました: " . $e->getMessage();
}

$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php
     if($isRegistrationOk == false){
        echo "Oops!";
     }else{
        echo "ありがとうございます！";
     }
    ?></title>
    <link rel="stylesheet" type="text/css" href="" />
</head>

<style>
    body {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }

    #image-container {
        margin-bottom: 20px;
    }

    #button-container {
        display: flex;
        justify-content: center;
    }
</style>


<body>
    <h1><?php echo $message; ?></h1>
    <?php
    
    if ($isRegistrationOk) {
        echo '
        <div id="image-container">
            <img src="../img/thankyou.png">
        </div>
        ';
    }
    ?>
    
  <div id="button-container">
    <a href="../login.php" target="_blank">
      <button type="button">ログインへ</button>
    </a>
  </div>
</body>

</html>