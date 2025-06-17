<?php
session_start();
include 'db_connect.php';

$bookName = $_GET['bookname'] ?? '';

if (!isset($_SESSION['rollno'])) {
    header('Location: login.php?redirect=rent_book.php&bookname=' . urlencode($bookName));
    exit;
}

$message = "";
$redirectAfterSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookName = $_POST['bookname'] ?? '';
    $rollNo = $_SESSION['rollno'];

    $sql = "BEGIN sub(:bname, :roll_no); END;";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':bname', $bookName);
    oci_bind_by_name($stid, ':roll_no', $rollNo);

    $r = oci_execute($stid);

    if ($r) {
        oci_commit($conn);
        $message = "Book rented successfully. Redirecting to your rented books...";
        $redirectAfterSuccess = true;
    } else {
        $e = oci_error($stid);
        if (isset($e['code']) && $e['code'] >= 20000) {
            $message = "Error: " . htmlspecialchars($e['message']);
        } else {
            $message = "An unexpected error occurred.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rent Book</title>
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
        .container {
            background-color: #111;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px #fff;
            width: 320px;
            text-align: center;
        }
        input[type="text"] {
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
            color: #0f0;
        }
        p.message.error {
            color: #f55;
        }
        a {
            color: #fff;
            text-decoration: underline;
            margin-top: 10px;
            display: inline-block;
        }
        a:hover {
            color: #ccc;
        }
    </style>
    <?php if ($redirectAfterSuccess): ?>
    <script>
        setTimeout(function(){
            window.location.href = 'my_rented_books.php';
        }, 2000); // 2 giây tự chuyển
    </script>
    <?php endif; ?>
</head>
<body>
<div class="container">
    <h2>Rent a Book</h2>
    <form method="post" action="">
        <input type="text" name="bookname" value="<?php echo htmlspecialchars($bookName); ?>" readonly>
        <input type="submit" value="Rent Book" <?php echo $redirectAfterSuccess ? 'disabled' : ''; ?>>
    </form>

    <?php if ($message): ?>
        <p class="message <?php echo (strpos($message, 'Error:') === 0) ? 'error' : ''; ?>">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>

    <p><a href="library.php">Back to Library</a></p>
</div>
</body>
</html>
