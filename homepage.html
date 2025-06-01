<?php
session_start();
if (!isset($_SESSION['guest_id'])) {
    header("Location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Hotels</title>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top-bar">
  <h1 class="hotel-heading">BOOK YOUR HOTEL NOW</h1>
  <a href="profile.php" class="btn-profile">Profile</a>
</div>


  <div class="hotel-grid">
    <?php
    $conn = new mysqli("localhost", "root", "", "hotel_booking");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $result = $conn->query("SELECT * FROM hotels");

    while ($row = $result->fetch_assoc()) {
        echo "<div class='hotel-card'>";
        echo "<a href='hotel_details.php?id=" . $row['id'] . "'>";
        echo "<img src='" . $row['image_url'] . "' alt='Hotel image'>";
        echo "<h2>" . htmlspecialchars($row['hotel_name']) . "</h2>";
        echo "<p>" . htmlspecialchars($row['location']) . "</p>";
        echo "</a>";
        echo "</div>";
    }

    $conn->close();
    ?>
  </div>
</body>
</html>
