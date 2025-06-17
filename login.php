<?php
session_start();
include 'db_connect.php';

$message = '';

$redirect = $_GET['redirect'] ?? 'my_rented_books.php';
$bookname = $_GET['bookname'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rollno = $_POST['rollno'] ?? '';

    if (empty($rollno) || !is_numeric($rollno)) {
        $message = "Please enter a valid roll number.";
    } else {
        $sql = "SELECT name FROM customer WHERE rollno = :rollno";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ':rollno', $rollno);
        oci_execute($stid);

        if (($row = oci_fetch_assoc($stid)) != false) {
            $_SESSION['user'] = $row['NAME'];
            $_SESSION['rollno'] = $rollno;

            // Chuyển hướng sau login thành công, kèm bookname nếu có
            if (!empty($bookname)) {
                header('Location: ' . $redirect . '?bookname=' . urlencode($bookname));
            } else {
                header('Location: ' . $redirect);
            }
            exit;
        } else {
            $message = 'Invalid roll number';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
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
        .login-container {
            background-color: #111;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px #fff;
            width: 320px;
            text-align: center;
        }
        input[type="number"] {
            width: 100%;
            padding: 8px 10px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #222;
            color: #fff;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #fff;
            color: #000;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            width: 100%;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #ccc;
        }
        p.message {
            margin-top: 20px;
            font-weight: bold;
            color: #f55;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Login</h2>
    <form method="post" action="?<?php
        // Giữ nguyên các tham số GET redirect, bookname khi submit form
        echo http_build_query(['redirect' => $redirect, 'bookname' => $bookname]);
    ?>">
        <input type="number" name="rollno" placeholder="Enter your Roll No" required>
        <input type="submit" value="Login">
    </form>
    <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <p><a href="library.php" style="color:#fff;text-decoration:underline;">Back to Library</a></p>
</div>
</body>
</html>
