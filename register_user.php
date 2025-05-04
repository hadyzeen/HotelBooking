<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = $_POST['first_name'];
    $last  = $_POST['last_name'];
    $email = $_POST['email'];
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

   
    $sql = "
      INSERT INTO guest 
        (first_name, last_name, email, password)
      VALUES
        ('$first', '$last', '$email', '$pass')
    ";

    if ($conn->query($sql) === TRUE) {
        echo "<p>Registration successful! <a href='userlogin.html'>Login here</a></p>";
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}
?>
