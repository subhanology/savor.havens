<?php
// reservation.php - Handles reservation submissions by logged-in customers

session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form data
    $customer_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $email = trim($_POST['email']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $guests = intval($_POST['guests']);
    $special_requests = trim($_POST['special_requests']);

    // Basic validation of required fields
    if (empty($name) || empty($contact) || empty($email) || empty($date) || empty($time) || empty($guests)) {
        die('Please fill all required reservation fields.');
    }

    try {
        // Database connection
        $pdo = new PDO('mysql:host=localhost;dbname=restaurant_system;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insert reservation linked to this customer
        $stmt = $pdo->prepare(
            'INSERT INTO Reservations 
                (customer_id, name, contact, email, reservation_date, reservation_time, guests, special_requests) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$customer_id, $name, $contact, $email, $date, $time, $guests, $special_requests]);

        // Redirect to home (dashboard) or show success message
        header('Location: home.php');
        exit;
    } catch (PDOException $e) {
        // Handle insertion error
        die('Database error: ' . $e->getMessage());
    }
} else {
    // If not a POST request, redirect back to home or reservation form
    header('Location: home.php');
    exit;
}
?>
