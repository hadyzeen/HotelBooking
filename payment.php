<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['guest_id'])) {
    header("Location: login.php");
    exit;
}

$guest_id = $_SESSION['guest_id'];

// Case 1: Payment is being submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['card_number'])) {
    $booking_num = $_POST['booking_num'];
    $amount = $_POST['amount'];
    $card_number = $_POST['card_number'];
    $card_holder = $_POST['card_holder'];
    $expiry_month = $_POST['expiry_month'];
    $expiry_year = $_POST['expiry_year'];
    $cvv = $_POST['cvv'];

    // Format expiry date
    $expiry_month = (strlen($expiry_month) == 1) ? "0" . $expiry_month : $expiry_month;
    $expiry_date = "20$expiry_year-$expiry_month-01";

    // Insert into payment table
    $stmt = $conn->prepare("INSERT INTO payment (booking_num, guest_id, card_number, card_holder_name, expiry_date, cvv, amount) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssd", $booking_num, $guest_id, $card_number, $card_holder, $expiry_date, $cvv, $amount);

    if ($stmt->execute()) {
        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Payment Successful</title>
            <link rel="stylesheet" href="style.css">
        </head>
        <body class="success-page">
            <div class="success-card">
                <div class="check-icon">✅</div>
                <h2>Payment Successful!</h2>
                <p>Your booking has been confirmed.</p>
                <a href="homepage.php">Return to Homepage</a>
            </div>
        </body>
        </html>';
    } else {
        echo "<h2 style='color:red;'> Payment Failed: " . $stmt->error . "</h2>";
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Case 2: Arriving from hotel_details.php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['hotel_id'], $_POST['room_id'], $_POST['checkin'], $_POST['checkout'])) {
    $hotel_id = $_POST['hotel_id'];
    $room_id = $_POST['room_id'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];

    // Calculate number of days
    $days = (strtotime($checkout) - strtotime($checkin)) / (60 * 60 * 24);
    if ($days <= 0) {
        die("Invalid check-in/check-out dates.");
    }

    // Get room price
    $stmt = $conn->prepare("SELECT price FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
    $stmt->close();

    if (!$room) {
        die("Room not found.");
    }

    $total_price = $room['price'] * $days;

    // Insert booking
    $insert = $conn->prepare("INSERT INTO bookings (hotel_id, room_id, check_in_date, check_out_date, total_price, total_rooms_booked, guest_id) 
                              VALUES (?, ?, ?, ?, ?, 1, ?)");
    $insert->bind_param("iissdi", $hotel_id, $room_id, $checkin, $checkout, $total_price, $guest_id);
    $insert->execute();
    $booking_num = $insert->insert_id;
    $insert->close();

    // Set room unavailable
    $update = $conn->prepare("UPDATE rooms SET is_available = 0 WHERE id = ?");
    $update->bind_param("i", $room_id);
    $update->execute();
    $update->close();

} else {
    die("Invalid access.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="payment-page">

<div class="payment-box">
    <h2>Enter Payment Details</h2>
    <form method="POST">
        <input type="hidden" name="booking_num" value="<?= htmlspecialchars($booking_num) ?>">
        <input type="hidden" name="amount" value="<?= htmlspecialchars($total_price) ?>">

        <label>Card Number</label>
        <input type="text" name="card_number" placeholder="Card Number" required>

        <label>Cardholder Name</label>
        <input type="text" name="card_holder" placeholder="Card Holder Name" required>

        <label>Expiry Month</label>
        <input type="number" name="expiry_month" placeholder="MM" min="1" max="12" required>

        <label>Expiry Year</label>
        <input type="number" name="expiry_year" placeholder="YY" min="0" max="99" required>

        <label>CVV</label>
        <input type="text" name="cvv" placeholder="CVV" maxlength="4" required>

        <button type="submit">Confirm Payment</button>
    </form>
</div>

<script>
document.querySelector("form").addEventListener("submit", function (e) {
    const cardNumber = document.querySelector('input[name="card_number"]').value.trim();
    const cardHolder = document.querySelector('input[name="card_holder"]').value.trim();
    const month = document.querySelector('input[name="expiry_month"]').value.trim();
    const year = document.querySelector('input[name="expiry_year"]').value.trim();
    const cvv = document.querySelector('input[name="cvv"]').value.trim();

    if (!/^\d{12,19}$/.test(cardNumber)) {
        alert("❌ Card number must be 12 to 19 digits.");
        e.preventDefault();
        return;
    }

    if (!/^[a-zA-Z ]+$/.test(cardHolder)) {
        alert("❌ Cardholder name must only contain letters and spaces.");
        e.preventDefault();
        return;
    }

    if (!(+month >= 1 && +month <= 12)) {
        alert("❌ Month must be between 1 and 12.");
        e.preventDefault();
        return;
    }

    if (!(+year >= 25 && +year <= 99)) {
        alert("❌ Year must be between 25 and 99.");
        e.preventDefault();
        return;
    }

    if (!/^\d{3,4}$/.test(cvv)) {
        alert("❌ CVV must be 3 or 4 digits.");
        e.preventDefault();
        return;
    }
});
</script>

</body>
</html>
