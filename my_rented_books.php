<?php
session_start();
echo "Logged in as rollno: " . (isset($_SESSION['rollno']) ? htmlspecialchars($_SESSION['rollno']) : 'Not logged in');
include 'db_connect.php';

if (!isset($_SESSION['rollno'])) {
    header('Location: login.php');
    exit;
}

$rollNo = $_SESSION['rollno'];

function getBookImage($bookname) {
    $file = 'images/' . strtolower(str_replace(' ', '_', $bookname)) . '.jpg';
    if (file_exists($file)) {
        return $file;
    }
    return 'images/default_book.jpg';
}

// Lấy sách đang thuê (chưa trả)
$sql_renting = "SELECT b.bookno, b.bookname, s.do_sub, s.do_return, s.status
                FROM books b
                JOIN subscription s ON b.bookno = s.bookno
                WHERE s.rollno = :rollno AND s.status = 'ntreturned'
                ORDER BY s.do_sub DESC";
$stid_renting = oci_parse($conn, $sql_renting);
oci_bind_by_name($stid_renting, ':rollno', $rollNo);
oci_execute($stid_renting);

$rentingBooks = [];
while ($row = oci_fetch_assoc($stid_renting)) {
    $rentingBooks[] = $row;
}

// Lấy sách đã trả
$sql_returned = "SELECT b.bookno, b.bookname, s.do_sub, s.do_return, s.status
                FROM books b
                JOIN subscription s ON b.bookno = s.bookno
                WHERE s.rollno = :rollno AND s.status = 'returned'
                ORDER BY s.do_return DESC";
$stid_returned = oci_parse($conn, $sql_returned);
oci_bind_by_name($stid_returned, ':rollno', $rollNo);
oci_execute($stid_returned);

$returnedBooks = [];
while ($row = oci_fetch_assoc($stid_returned)) {
    $returnedBooks[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Rented Books</title>
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-top: 40px;
        }
        table {
            width: 90%;
            margin: 10px auto 40px auto;
            border-collapse: collapse;
            background-color: #111;
            box-shadow: 0 0 10px #fff;
        }
        th, td {
            border: 1px solid #444;
            padding: 10px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #222;
        }
        img.book-img {
            width: 80px;
            height: 110px;
            object-fit: cover;
            border-radius: 4px;
        }
        input.return-btn {
            background-color: #fff;
            color: #000;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        input.return-btn:hover {
            background-color: #ccc;
        }
        a {
            color: #fff;
            text-decoration: underline;
        }
        .logout {
            display: block;
            width: 80px;
            margin: 10px auto;
            padding: 8px;
            text-align: center;
            background: #fff;
            color: #000;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
        }
        .logout:hover {
            background: #ccc;
        }
    </style>
</head>
<body>

<div style="text-align: leftleft; margin-bottom: 20px;">
    <a href="library.php">
        <img src="images/library_icon.jpg" alt="Library Logo" style="max-height: 80px; cursor: pointer;">
    </a>
</div>

<h2>Currently Renting Books</h2>
<?php if (count($rentingBooks) > 0): ?>
<table>
    <tr>
        <th>Image</th>
        <th>Book No</th>
        <th>Book Name</th>
        <th>Borrow Date</th>
        <th>Return Date</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php foreach ($rentingBooks as $book): ?>
    <tr>
        <td><img class="book-img" src="<?php echo htmlspecialchars(getBookImage($book['BOOKNAME'])); ?>" alt="<?php echo htmlspecialchars($book['BOOKNAME']); ?>"></td>
        <td><?php echo htmlspecialchars($book['BOOKNO']); ?></td>
        <td><?php echo htmlspecialchars($book['BOOKNAME']); ?></td>
        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($book['DO_SUB']))); ?></td>
        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($book['DO_RETURN']))); ?></td>
        <td><?php echo htmlspecialchars($book['STATUS']); ?></td>
        <td>
            <form method="post" action="return_book.php" onsubmit="return confirm('Are you sure you want to return this book?');">
                <input type="hidden" name="bookno" value="<?php echo htmlspecialchars($book['BOOKNO']); ?>">
                <input type="submit" class="return-btn" value="Return Book">
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p style="text-align:center;">You have no books currently renting.</p>
<?php endif; ?>


<h2>Returned Books</h2>
<?php if (count($returnedBooks) > 0): ?>
<table>
    <tr>
        <th>Image</th>
        <th>Book No</th>
        <th>Book Name</th>
        <th>Borrow Date</th>
        <th>Return Date</th>
        <th>Status</th>
    </tr>
    <?php foreach ($returnedBooks as $book): ?>
    <tr>
        <td><img class="book-img" src="<?php echo htmlspecialchars(getBookImage($book['BOOKNAME'])); ?>" alt="<?php echo htmlspecialchars($book['BOOKNAME']); ?>"></td>
        <td><?php echo htmlspecialchars($book['BOOKNO']); ?></td>
        <td><?php echo htmlspecialchars($book['BOOKNAME']); ?></td>
        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($book['DO_SUB']))); ?></td>
        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($book['DO_RETURN']))); ?></td>
        <td><?php echo htmlspecialchars($book['STATUS']); ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p style="text-align:center;">You have no returned books.</p>
<?php endif; ?>


<a href="logout.php" class="logout">Logout</a>

</body>
</html>
