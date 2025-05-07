<?php 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $email      = $_POST['email'];
    $password   = $_POST['password'];

    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        echo "All fields are required.";
        exit;
    }

    // Database connection
    $conn = new mysqli("localhost:3307", "root", "", "hotel_booking");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO guest (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $first_name, $last_name, $email, $password);

    if ($stmt->execute()) {
        // Success message and redirect
        echo "<script>
                alert('Successfully registered! Now login.');
                window.location.href = 'index.html';
              </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
