<?php
$conn = new mysqli("localhost", "root", "", "hotel_booking");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hotel_id = $_POST['hotel_id'];
    $room_id = $_POST['room_id'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];

    $days = (strtotime($checkout) - strtotime($checkin)) / (60 * 60 * 24);

    if ($days <= 0) {
        die("Invalid dates selected.");
    }

    $stmt = $conn->prepare("SELECT price FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $room = $result->fetch_assoc();
    $price_per_night = $room['price'];


    $total_price = $price_per_night * $days;

    $insert = $conn->prepare("INSERT INTO bookings (hotel_id, room_id, check_in_date, check_out_date, total_price, total_rooms_booked) VALUES (?, ?, ?, ?, ?, 1)");
    $insert->bind_param("iissd", $hotel_id, $room_id, $checkin, $checkout, $total_price);
    $insert->execute();

    $update = $conn->prepare("UPDATE rooms SET is_available = 0 WHERE id = ?");
    $update->bind_param("i", $room_id);
    $update->execute();

    echo "Booking saved!<br>";
    echo "Check-in: $checkin<br>";
    echo "Check-out: $checkout<br>";
    echo "Total Price: $total_price<br>";
    echo "<a href='homepage.php'>Back to homepage</a>";
}
?>
