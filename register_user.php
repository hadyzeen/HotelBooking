<?php 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $email      = $_POST['email'];
    $password   = $_POST['password'];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        echo "All fields are required.";
        exit;
    }

    // Encrypt password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $conn = new mysqli("localhost:3307", "root", "", "hotel_booking");

    if ($conn->connect_error) {
        echo "Connection failed: " . $conn->connect_error;
        exit;
    }

    // Optional: check if email already exists
    $check = $conn->prepare("SELECT email FROM guest WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo "Email already registered.";
        $check->close();
        $conn->close();
        exit;
    }
    $check->close();

    // Insert new guest
    $stmt = $conn->prepare("INSERT INTO guest (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
