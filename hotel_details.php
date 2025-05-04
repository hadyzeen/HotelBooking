<?php
include 'db_connect.php';

if (!isset($_GET['hotel_id'])) {
  die("No hotel specified.");
}

$id = (int)$_GET['hotel_id'];
$sql = "SELECT * FROM hotels WHERE id = $id";
$res = mysqli_query($conn, $sql);
$hotel = mysqli_fetch_assoc($res);
if (!$hotel) {
  die("Hotel not found.");
}

// Fetch rooms for this hotel
$rooms = mysqli_query($conn, "SELECT * FROM rooms WHERE hotel_id = $id AND availability = 1");
?>
<!DOCTYPE html>
<html lang="en">
<head>…</head>
<body>
  <main>
    <h2><?= htmlspecialchars($hotel['name']) ?></h2>
    <img src="<?= htmlspecialchars($hotel['image']) ?>" alt="">
    <p><?= htmlspecialchars($hotel['description']) ?></p>
    <p>Price / night: $<?= $hotel['price_per_night'] ?></p>

    <form action="book_room.php" method="POST">
      <input type="hidden" name="hotel_id" value="<?= $hotel['id'] ?>">
      <label>Room:</label>
      <select name="room_id" required>
        <?php while ($r = mysqli_fetch_assoc($rooms)): ?>
          <option value="<?= $r['id'] ?>">
            <?= htmlspecialchars($r['room_type']) ?> — $<?= $r['price'] ?>
          </option>
        <?php endwhile; ?>
      </select><br>

      <label>Check-in:</label>
      <input type="date" name="check_in" required><br>
      <label>Check-out:</label>
      <input type="date" name="check_out" required><br>
      <label>Guests:</label>
      <input type="number" name="num_guests" required><br>
      <button type="submit">Book Now</button>
    </form>
  </main>
</body>
</html>
