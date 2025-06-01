<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['guest_id'])) {
    header("Location: login.php");
    exit;
}

$guestId = $_SESSION['guest_id'];
$first_name = $_POST['first_name'] ?? '';
$last_name  = $_POST['last_name'] ?? '';
$email      = $_POST['email'] ?? '';

if (empty($first_name) || empty($last_name) || empty($email)) {
    echo "All fields are required.";
    exit;
}

$stmt = $conn->prepare("UPDATE guest SET first_name = ?, last_name = ?, email = ? WHERE guest_id = ?");
$stmt->bind_param("sssi", $first_name, $last_name, $email, $guestId);

if ($stmt->execute()) {
    $_SESSION['guest_name'] = $first_name;
    $_SESSION['success_message'] = "Profile updated successfully!";
    header("Location: profile.php");
    exit;

} else {
    echo "Error updating profile: " . $stmt->error;
}
?>
