<?php
include 'db_connect.php';

$sql_books = "SELECT * FROM books ORDER BY bookno";
$stid_books = oci_parse($conn, $sql_books);
oci_execute($stid_books);

$sql_customers = "SELECT * FROM customer ORDER BY rollno";
$stid_customers = oci_parse($conn, $sql_customers);
oci_execute($stid_customers);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Books & Customers</title>
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 30px 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }
        h2 {
            margin-bottom: 15px;
        }
        table {
            border-collapse: collapse;
            width: 90vw;
            max-width: 900px;
            margin-bottom: 40px;
            box-shadow: 0 0 10px #fff;
            background-color: #111;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px 15px;
            text-align: left;
            color: #eee;
        }
        th {
            background-color: #222;
        }
        a {
            color: #fff;
            text-decoration: underline;
            margin: 0 10px;
            font-size: 16px;
        }
        a:hover {
            color: #ccc;
        }
        .nav {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<div class="nav">
    <a href="rent_book.php">Rent Book</a> | <a href="return_book.php">Return Book</a>
</div>

<h2>Books</h2>
<table>
    <thead>
        <tr>
            <th>Book No</th>
            <th>Book Name</th>
            <th>Available</th>
            <th>Subscribed To</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = oci_fetch_assoc($stid_books)) : ?>
        <tr>
            <td><?php echo htmlspecialchars($row['BOOKNO']); ?></td>
            <td><?php echo htmlspecialchars($row['BOOKNAME']); ?></td>
            <td><?php echo htmlspecialchars($row['AVAILABLE']); ?></td>
            <td><?php echo htmlspecialchars($row['SUBSCRIBED_TO']); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<h2>Customers</h2>
<table>
    <thead>
        <tr>
            <th>Roll No</th>
            <th>Name</th>
            <th>No Card</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = oci_fetch_assoc($stid_customers)) : ?>
        <tr>
            <td><?php echo htmlspecialchars($row['ROLLNO']); ?></td>
            <td><?php echo htmlspecialchars($row['NAME']); ?></td>
            <td><?php echo htmlspecialchars($row['NO_CARD']); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
