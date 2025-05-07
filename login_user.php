<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: homepage.php");
    exit;
}

$email    = $_POST['email'];
$password = $_POST['password'];

if (empty($email) || empty($password)) {
    echo "Both email and password are required.";
    exit;
}

$conn = new mysqli("localhost:3307", "root", "", "hotel_booking");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT id, first_name, password FROM guest WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $first_name, $db_password);
    $stmt->fetch();

    if ($password === $db_password) {
        // Success
        $_SESSION['guest_id'] = $id;
        $_SESSION['guest_name'] = $first_name;
        header("Location: homepage.php");
        exit;
    } else {
        // Wrong password
        echo "<script>alert('Incorrect password.'); window.location.href='index.html';</script>";
        exit;
    }
} else {
    // Email not found
    echo "<script>alert('No user found with that email.'); window.location.href='index.html';</script>";
    exit;
}

$stmt->close();
$conn->close();
?>
