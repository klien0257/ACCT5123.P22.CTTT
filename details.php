<?php
// details.php
session_start();
include 'db_connect.php';

// Validate and get i_index from query
$iIndex = $_GET['i_index'] ?? null;
if (!$iIndex) {
    echo "<p>Invalid book identifier.</p>";
    exit;
}

// Fetch book details
$sql = "SELECT
    l.i_index,
    l.r_index,
    l.title,
    l.isbn_10,
    l.isbn_13,
    l.publish_date,
    l.key_col,
    l.subjects,
    l.languages,
    l.description,
    l.genres,
    l.image_url,
    NVL((
        SELECT COUNT(*) FROM subscription s
        WHERE s.bookno = l.i_index AND s.status = 'ntreturned'
    ), 0) AS rented_count
  FROM lib l
 WHERE l.i_index = :idx";

$stid = oci_parse($conn, $sql);
oci_bind_by_name($stid, ':idx', $iIndex);
oci_execute($stid);

$book = oci_fetch_assoc($stid);
oci_free_statement($stid);
oci_close($conn);

if (!$book) {
    echo "<p>Book not found.</p>";
    exit;
}

// Determine availability
$totalCopies = 1; // if you imported noofcopies, adjust accordingly
$rented = intval($book['RENTED_COUNT']);
$available = max(0, $totalCopies - $rented);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Details: <?php echo htmlspecialchars($book['TITLE']); ?></title>
    <style>
        body { background:#000; color:#fff; font-family:Arial,sans-serif; padding:10px; position: relative; }
        .back { position: absolute; top: 20px; left: 20px; }
        .container { max-width:800px; margin:60px auto 0; }
        .details { display:flex; gap:28px; }
        .details img { width:300px; height:500px; object-fit:cover; border-radius:8px; }
        .info { flex:1; }
        .info h1 { margin-top:0; }
        .info p { margin:12px 0; font-size:16px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo link back to library -->
        <a class="back" href="library.php">
            <img src="images/Library_icon.jpg" alt="Library" style="height:100px;">
        </a>
        <div class="details">
            <img src="<?php echo htmlspecialchars($book['IMAGE_URL']); ?>" alt="<?php echo htmlspecialchars($book['TITLE']); ?>">
            <div class="info">
                <?php
                // Convert CLOB fields to strings
                $subjects   = (is_object($book['SUBJECTS']) ? $book['SUBJECTS']->load() : $book['SUBJECTS']);
                $languages  = (is_object($book['LANGUAGES']) ? $book['LANGUAGES']->load() : $book['LANGUAGES']);
                $description= (is_object($book['DESCRIPTION']) ? $book['DESCRIPTION']->load() : $book['DESCRIPTION']);
                $genres     = (is_object($book['GENRES']) ? $book['GENRES']->load() : $book['GENRES']);
                ?>
                <h1><?php echo htmlspecialchars($book['TITLE']); ?></h1>
                <p><strong>ISBN-10:</strong> <?php echo htmlspecialchars($book['ISBN_10']); ?></p>
                <p><strong>ISBN-13:</strong> <?php echo htmlspecialchars($book['ISBN_13']); ?></p>
                <p><strong>Published:</strong> <?php echo htmlspecialchars($book['PUBLISH_DATE']); ?></p>
                <p><strong>Genres:</strong> <?php echo htmlspecialchars($genres); ?></p>
                <p><strong>Subjects:</strong> <?php echo htmlspecialchars($subjects); ?></p>
                <p><strong>Languages:</strong> <?php echo htmlspecialchars($languages); ?></p>
                <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($description)); ?></p>
                <p><strong>Available Copies:</strong> <?php echo $available; ?></p>
            </div>
        </div>
    </div>
</body>
</html>
