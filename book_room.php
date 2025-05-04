<?php
session_start();
include 'db_connect.php';

$user_id  = $_SESSION['user_id'] ?? null;
$hotel_id = (int)$_POST['hotel_id'];
$room_id  = (int)$_POST['room_id'];
$check_in = $_POST['check_in'];
$check_out= $_POST['check_out'];
$guests   = (int)$_POST['num_guests'];

if (!$user_id) die("You must be logged in to book.");

$sql = "
  INSERT INTO bookings
    (user_id, hotel_id, room_id, check_in, check_out, num_guests)
  VALUES
    ($user_id, $hotel_id, $room_id, '$check_in', '$check_out', $guests)
";
if (mysqli_query($conn, $sql)) {
  // Optionally: mark room unavailable, or handle calendar logic here
  echo "Booking successful!";
} else {
  echo "Error: " . mysqli_error($conn);
}
