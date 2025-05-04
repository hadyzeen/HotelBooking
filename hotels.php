<?php
include 'db_connect.php';

$location   = isset($_GET['location'])   ? mysqli_real_escape_string($conn, $_GET['location'])   : '';
$min_price  = isset($_GET['min_price'])  ? (float)$_GET['min_price']   : 0;
$max_price  = isset($_GET['max_price'])  ? (float)$_GET['max_price']   : 999999;
$min_rating = isset($_GET['min_rating']) ? (float)$_GET['min_rating']  : 0;

// Build query
$sql = "
  SELECT * 
    FROM hotels 
   WHERE location LIKE '%$location%'
     AND price_per_night BETWEEN $min_price AND $max_price
     AND rating >= $min_rating
";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Available Hotels</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header>…same nav as index…</header>
  <main>
    <h2>Search Results</h2>
    <?php while ($h = mysqli_fetch_assoc($result)): ?>
      <div class="hotel">
        <h3><?= htmlspecialchars($h['name']) ?></h3>
        <p>Location: <?= htmlspecialchars($h['location']) ?></p>
        <p>Price: $<?= number_format($h['price_per_night'],2) ?> / night</p>
        <p>Rating: <?= $h['rating'] ?>/5</p>
        <a href="hotel_details.php?hotel_id=<?= $h['id'] ?>">View Details</a>
      </div>
    <?php endwhile; ?>
  </main>
</body>
</html>

