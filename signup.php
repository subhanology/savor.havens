<?php
// Database credentials
$host = "localhost";
$user = "root";
$pass = "";
$db = "restaurant_system";

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];

    if (empty($username) || empty($name) || empty($contact) || empty($email) || empty($address) || empty($password)) {
        die('Please fill all required fields.');
    }

    try {
        $pdo = new PDO('mysql:host=localhost;dbname=restaurant_system;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if username already exists
        $stmt = $pdo->prepare('SELECT id FROM Customers WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            die('Username is already taken. Please choose another.');
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare('INSERT INTO Customers (username, name, contact, email, address, password) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$username, $name, $contact, $email, $address, $hashedPassword]);

        header('Location: login.html');
        exit;
    } catch (PDOException $e) {
        die('Database error: ' . $e->getMessage());
    }
} else {
    header('Location: signup.html');
    exit;
}
?>