<?php
session_start();

if (!isset($_SESSION['guest_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $booking_num = $_POST['booking_num'];
    $review_text = trim($_POST['review']);
    $rating = $_POST['rating'];
}
    if (empty($rating)) {
        echo "<script>alert('Rating cannot be empty!'); window.history.back();</script>";
        exit;
    }

    // Get hotel_id from the booking
    $stmt = $conn->prepare("SELECT hotel_id FROM bookings WHERE booking_num = ? AND guest_id = ?");
    $stmt->bind_param("ii", $booking_num, $_SESSION['guest_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $hotel_id = $row['hotel_id'];
        $guest_id = $_SESSION['guest_id'];
    
        // Get guest name
        $stmt_name = $conn->prepare("SELECT first_name FROM guest WHERE guest_id = ?");
        $stmt_name->bind_param("i", $guest_id);
        $stmt_name->execute();
        $res_name = $stmt_name->get_result();
        $guest_name = $res_name->fetch_assoc()['first_name'];
        $stmt_name->close();
    
        // Insert full review
        $insert = $conn->prepare("INSERT INTO reviews (hotel_id, booking_num, review, rating, guest_id, guest_name) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("iisdis", $hotel_id, $booking_num, $review_text, $rating, $guest_id, $guest_name);
        $insert->execute();
    
        $_SESSION['success_message'] = "Your review was submitted successfully!";
        header("Location: profile.php");
        exit;
    }
    
