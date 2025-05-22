<?php
// contact.php - Handles feedback submissions

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $rating = intval($_POST['rating']);
    $comments = trim($_POST['comments']);

    // Basic validation
    if (empty($name) || empty($email) || empty($rating)) {
        die('Please provide your name, email, and rating.');
    }
    if ($rating < 1 || $rating > 5) {
        die('Rating must be between 1 and 5.');
    }

    try {
        // Database connection
        $pdo = new PDO('mysql:host=localhost;dbname=restaurant_system;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insert feedback into Feedback table
        $stmt = $pdo->prepare('INSERT INTO Feedback (name, email, rating, comments) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $rating, $comments]);

        // Redirect or thank the user
        header('Location: home.php');
        exit;
    } catch (PDOException $e) {
        die('Database error: ' . $e->getMessage());
    }
} else {
    // If not a POST, redirect to home or contact form
    header('Location: home.php');
    exit;
}
?>
