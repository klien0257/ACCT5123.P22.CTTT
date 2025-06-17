<?php
include 'db_connect.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bookNo = (int) $_POST['bookno'];

    $sql = "BEGIN ret(:bno); END;";

    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':bno', $bookNo);

    $r = oci_execute($stid);

    if ($r) {
        $message = "Returned successfully";
    } else {
        $e = oci_error($stid);
        $message = "Error: " . $e['message'];
    }
} else {
    // Nếu không submit form mà truy cập trực tiếp thì redirect về trang sách đã thuê
    header('Location: my_rented_books.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Return Book</title>
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: Arial, sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .message-box {
            background-color: #111;
            padding: 40px 60px;
            border-radius: 8px;
            box-shadow: 0 0 10px #fff;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
        }
        a {
            display: block;
            margin-top: 20px;
            color: #fff;
            text-decoration: underline;
            font-size: 16px;
            text-align: center;
        }
        a:hover {
            color: #ccc;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <?php echo htmlspecialchars($message); ?>
        <a href="my_rented_books.php">Back to My Rented Books</a>
    </div>
</body>
</html>
