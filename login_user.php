<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "Invalid request method.";
    exit;
}

$email    = $_POST['email'];
$password = $_POST['password'];

if (empty($email) || empty($password)) {
    echo "Both email and password are required.";
    exit;
}

$conn = new mysqli("localhost", "root", "", "hotel_booking");
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
    exit;
}

$stmt = $conn->prepare("SELECT guest_id, first_name, password FROM guest WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($guest_id, $first_name, $db_password);
    $stmt->fetch();

    if (password_verify($password, $db_password)) {
        $_SESSION['guest_id'] = $guest_id;
        $_SESSION['guest_name'] = $first_name;
        echo "success";
    } else {
        echo "Incorrect password.";
    }
} else {
    echo "No user found with that email.";
}

$stmt->close();
$conn->close();
?>
