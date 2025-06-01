<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['guest_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db_connect.php';
$guestId = $_SESSION['guest_id'];

// Get guest info
$stmt = $conn->prepare("SELECT first_name, last_name, email FROM guest WHERE guest_id = ?");
$stmt->bind_param("i", $guestId);
$stmt->execute();
$result = $stmt->get_result();
$guest = $result->fetch_assoc();
$stmt->close();

if (!$guest) {
    echo "Guest not found.";
    exit;
}

// Get user's bookings
$stmt = $conn->prepare("SELECT booking_num, hotel_id, check_in_date, check_out_date FROM bookings WHERE guest_id = ?");
$stmt->bind_param("i", $guestId);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    // Check if a review already exists for this booking
    $check = $conn->prepare("SELECT 1 FROM reviews WHERE booking_num = ?");
    $check->bind_param("i", $row['booking_num']);
    $check->execute();
    $check->store_result();
    if ($check->num_rows == 0) {
        $bookings[] = $row;
    }
    $check->close();
}
$stmt->close();

$hasBookings = count($bookings) > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guest Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500&family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script>
        function enableEdit() {
            document.getElementById("editBtn").style.display = "none";
            let fields = document.querySelectorAll(".editable");
            fields.forEach(f => f.removeAttribute("readonly"));
            document.getElementById("saveBtn").style.display = "inline";
        }

        function validateProfile() {
            const firstName = document.getElementById("first_name").value.trim();
            const lastName = document.getElementById("last_name").value.trim();
            const email = document.getElementById("email").value.trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (firstName === "" || lastName === "" || email === "") {
                alert("All fields are required.");
                return false;
            }

            if (!emailPattern.test(email)) {
                alert("Please enter a valid email address.");
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
<div class="profile-wrapper">
    <div class="top-buttons">
        <a href="homepage.php" class="back-btn">⬅ Return to Homepage</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- ✅ Success Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
            <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <div class="box">
        <h2>Personal Profile</h2>
        <form method="post" action="update_profile.php" onsubmit="return validateProfile()">
            <label>First Name:
                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($guest['first_name']) ?>" class="editable" readonly>
            </label>
            <label>Last Name:
                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($guest['last_name']) ?>" class="editable" readonly>
            </label>
            <label>Email:
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($guest['email']) ?>" class="editable" readonly>
            </label>
            <button type="button" id="editBtn" onclick="enableEdit()">Edit</button>
            <button type="submit" id="saveBtn" style="display:none;">Save Changes</button>
        </form>
    </div>

    <?php if ($hasBookings): ?>
        <div class="box">
            <h2>Your Bookings & Add Review</h2>
            <?php foreach ($bookings as $booking): ?>
                <?php
                $stmt = $conn->prepare("SELECT hotel_name FROM hotels WHERE id = ?");
                $stmt->bind_param("i", $booking['hotel_id']);
                $stmt->execute();
                $hotelResult = $stmt->get_result();
                $hotelRow = $hotelResult->fetch_assoc();
                $stmt->close();
                ?>
                <form method="post" action="submit_review.php">
                    <input type="hidden" name="booking_num" value="<?= $booking['booking_num'] ?>">
                    <p>
                        <strong><?= htmlspecialchars($hotelRow['hotel_name']) ?></strong><br>
                        Check-in: <?= $booking['check_in_date'] ?> |
                        Check-out: <?= $booking['check_out_date'] ?>
                    </p>
                    <label>Your Review:
                        <textarea name="review" placeholder="Write your review for this booking..."></textarea>
                    </label>
                    <label>Your Rating:</label>
                    <div class="star-rating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" id="star<?= $i ?>-<?= $booking['booking_num'] ?>" value="<?= $i ?>">
                            <label for="star<?= $i ?>-<?= $booking['booking_num'] ?>">★</label>
                        <?php endfor; ?>
                    </div>
                    <button type="submit">Submit Review</button>
                    <hr>
                </form>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-bookings">You have no bookings left to review. ✅</p>
    <?php endif; ?>
</div>
</body>
</html>
