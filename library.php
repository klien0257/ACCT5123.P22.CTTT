<?php
session_start();
include 'db_connect.php';

// 1. Process search query via semantic API
$searchTerm = $_GET['q'] ?? '';
$where = '';
$orderBy = '';
if ($searchTerm !== '') {
    $apiUrl = 'http://localhost:8000/search';
    $payload = json_encode(['q' => $searchTerm, 'k' => 10]);
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);

    if ($resp !== false) {
        $data = json_decode($resp, true);
        $names = $data['results'] ?? [];
        if ($names) {
            $quoted = array_map(function($n){ return "'" . str_replace("'","''",$n) . "'"; }, $names);
            $inList = implode(',', $quoted);
            $where  = "WHERE l.title IN ($inList)";
            $cases  = [];
            foreach ($names as $i => $t) {
                $esc = str_replace("'","''",$t);
                $cases[] = "WHEN l.title = '$esc' THEN $i";
            }
            $orderBy = "ORDER BY CASE " . implode(' ', $cases) . " END";
        }
    }
}

// 2. Fetch books from database
$sql = "SELECT
    l.i_index,
    l.title,
    l.image_url
  FROM lib l";
if ($where) {
    $sql .= " $where $orderBy";
} else {
    $sql .= " ORDER BY l.title";
}

$stid = oci_parse($conn, $sql);
oci_execute($stid);

$books = [];
while ($row = oci_fetch_assoc($stid)) {
    $books[] = [
        'I_INDEX'   => $row['I_INDEX'],
        'TITLE'     => $row['TITLE'],
        'IMAGE_URL' => $row['IMAGE_URL'],
    ];
}
oci_free_statement($stid);
oci_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Library</title>
    <style>
        body { background:#000; color:#fff; font-family:Arial,sans-serif; margin:20px; }
        h1 { text-align:center; margin-bottom:20px; }
        .search-form { text-align:center; margin-bottom:30px; }
        .search-form input { padding:10px; width:320px; border-radius:4px; border:none; }
        .search-form button { padding:10px 16px; margin-left:8px; border:none; border-radius:4px; cursor:pointer; font-weight:bold; }
        .book-list { display:flex; flex-wrap:wrap; justify-content:center; gap:24px; }
        .card { background:#111; width:180px; padding:12px; border-radius:8px; box-shadow:0 0 8px #fff; text-align:center; }
        .card img { width:150px; height:220px; object-fit:cover; border-radius:4px; }
        .card h2 { font-size:16px; margin:10px 0; color:#fff; }
        .logout { position:absolute; top:20px; right:20px; color:#fff; text-decoration:underline; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo link back to library -->
        <a class="back" href="library.php">
            <img src="images/Library_icon.jpg" alt="Library" style="height:100px;">
        </a>
    <h1>Library</h1>
    <div class="search-form">
        <form method="get">
            <input type="text" name="q" placeholder="Search…" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit">Search</button>
        </form>
    </div>
    <div class="book-list">
        <?php if (empty($books)): ?>
            <p>No books available.</p>
        <?php else: foreach ($books as $index => $b): ?>
            <div class="card">
                <a href="details.php?i_index=<?php echo urlencode($b['I_INDEX']); ?>">
                    <img src="<?php echo htmlspecialchars($b['IMAGE_URL']); ?>" alt="<?php echo htmlspecialchars($b['TITLE']); ?>"><br>
                    <h2><?php echo htmlspecialchars($b['TITLE']); ?></h2>
                </a>
            </div>
        <?php endforeach; endif; ?>
    </div>
    <div style="text-align:center; margin-top:20px;">
        <button id="showMoreBtn" style="padding:10px 20px; font-size:14px; cursor:pointer;">Show More</button>
    </div>
    <script>
        const cards = document.querySelectorAll('.card');
        const btn = document.getElementById('showMoreBtn');
        let visibleCount = 30; // show 30 initially
        function updateVisibility() {
            cards.forEach((card, idx) => {
                card.style.display = idx < visibleCount ? 'block' : 'none';
            });
            if (visibleCount >= cards.length) btn.style.display = 'none';
        }
        updateVisibility();
        btn.addEventListener('click', () => {
            visibleCount += 30; // load 30 more each click
            updateVisibility();
        });
    </script>
</body>
</html>