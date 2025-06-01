<?php
session_start();
if (!isset($_GET['id'])) {
    die("Hotel ID not provided.");
}

$conn = new mysqli("localhost", "root", "", "hotel_booking");

$hotel_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();
$hotel = $result->fetch_assoc();

if (!$hotel) {
    die("Hotel not found.");
}

$room_stmt = $conn->prepare("SELECT id, room_type, price FROM rooms WHERE hotel_id = ? AND is_available = 1");
$room_stmt->bind_param("i", $hotel_id);
$room_stmt->execute();
$room_result = $room_stmt->get_result();

$review_stmt = $conn->prepare("SELECT review, rating, guest_name, review_time FROM reviews WHERE hotel_id = ?");
$review_stmt->bind_param("i", $hotel_id);
$review_stmt->execute();
$review_result = $review_stmt->get_result();

$avg_stmt = $conn->prepare("SELECT AVG(rating) as average_rating FROM reviews WHERE hotel_id = ?");
$avg_stmt->bind_param("i", $hotel_id);
$avg_stmt->execute();
$avg_result = $avg_stmt->get_result();
$avg_rating_row = $avg_result->fetch_assoc();
$average_rating = round($avg_rating_row['average_rating'], 1);
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($hotel['hotel_name']) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500&family=Poppins&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="booklet-title">Bookly</div>

<div class="hotel-container">
  <h1><?= htmlspecialchars($hotel['hotel_name']) ?></h1>
  <p><strong>Location:</strong> <?= htmlspecialchars($hotel['location']) ?></p>
  <p><strong>Description:</strong> <?= htmlspecialchars($hotel['description']) ?></p>

  <div class="image-slider">
    <button class="slider-btn" onclick="changeImage(-1)">❮</button>
    <img id="sliderImage" src="<?= $hotel['image_url'] ?>" alt="Hotel Image" style="width: 600px;">
    <button class="slider-btn" onclick="changeImage(1)">❯</button>
  </div>

  <div class="form-section">
    <h2>Book Now</h2>
    <form action="payment.php" method="POST">
      <input type="hidden" name="hotel_id" value="<?= $hotel['id'] ?>">
      <label>Room Type:</label>
      <select name="room_id" id="roomSelect" onchange="showPrice()" required>
        <option value="">---</option>
        <?php while($r = $room_result->fetch_assoc()): ?>
          <option value="<?= $r['id'] ?>" data-price="<?= $r['price'] ?>"><?= $r['room_type'] ?></option>
        <?php endwhile; ?>
      </select>

      
      <p class="price-box">Price: <span id="roomPrice">$0.00</span></p>

      <label>Check-In:</label>
      <input type="date" name="checkin" id="checkin" required>

      <label>Check-Out:</label>
      <input type="date" name="checkout" id="checkout" required>

      <p class="total-box">Total Price: <span id="totalPrice">$0.00</span></p>

      <button type="submit">Proceed</button>
    </form>
  </div>

  <p class="average-rating"><strong>Average Rating:</strong> <?= $average_rating ?> out of 5 ⭐</p>

  <div class="reviews">
    <h2>Reviews</h2>
    <?php if ($review_result->num_rows > 0): ?>
      <?php while ($row = $review_result->fetch_assoc()): ?>
        <div class="review-item">
          <strong>Rating:</strong> <?= str_repeat("⭐", (int)$row['rating']) ?><br>
          <strong>Review:</strong> <?= htmlspecialchars($row['review']) ?><br>
          <small>By <?= htmlspecialchars($row['guest_name']) ?> on <?= date("F j, Y, g:i A", strtotime($row['review_time'])) ?></small>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No reviews yet.</p>
    <?php endif; ?>
  </div>
</div>

<script>
let images = [
  "<?= $hotel['image_url'] ?>",
  "<?= $hotel['image2_url'] ?>"
];

let index = 0;

function changeImage(direction) {
  index = (index + direction + images.length) % images.length;
  document.getElementById("sliderImage").src = images[index];
}

function showPrice() {
  let select = document.getElementById("roomSelect");
  let price = select.options[select.selectedIndex].getAttribute("data-price");
  document.getElementById("roomPrice").innerText = "$" + (price || "0.00");
}

function calculateTotal() {
  let price = document.getElementById("roomSelect").selectedOptions[0]?.getAttribute("data-price");
  let checkin = new Date(document.getElementById("checkin").value);
  let checkout = new Date(document.getElementById("checkout").value);
  let days = (checkout - checkin) / 86400000;
  if (days > 0 && price) {
    document.getElementById("totalPrice").innerText = "$" + (days * price).toFixed(2);
  } else {
    document.getElementById("totalPrice").innerText = "$0.00";
  }
}

window.onload = function() {
  document.getElementById("roomSelect").addEventListener("change", calculateTotal);
  document.getElementById("checkin").addEventListener("change", calculateTotal);
  document.getElementById("checkout").addEventListener("change", calculateTotal);
};
</script>
</body>
</html>
