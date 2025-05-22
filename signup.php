<?php
// Step 1: Database credentials
$host = "localhost";
$user = "root";
$pass = ""; // use your password if not empty
$db = "restaurant_system"; // change this to your actual database name

// Step 2: Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);

// Step 3: Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// signup.php - Handles new user registrations

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize form data
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $password = $_POST['password']; // Do not trim password in case spaces are significant

    // Basic validation
    if (empty($username) || empty($name) || empty($contact) || empty($email) || empty($address) || empty($password)) {
        die('Please fill all required fields.');
    }

    try {
        // Set up database connection using PDO
        $pdo = new PDO('mysql:host=localhost;dbname=restaurant_system;charset=utf8mb4', 'root', '');
        // Set error mode to exceptions
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if username already exists to avoid duplicates
        $stmt = $pdo->prepare('SELECT id FROM Customers WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            die('Username is already taken. Please choose another.');
        }

        // Hash the password securely (PASSWORD_DEFAULT uses bcrypt or better)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into Customers table
        $stmt = $pdo->prepare('INSERT INTO Customers (username, name, contact, email, address, password) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$username, $name, $contact, $email, $address, $hashedPassword]);

        // Redirect to login page on successful signup
        header('Location: login.html');
        exit;
    } catch (PDOException $e) {
        // Handle database errors
        die('Database error: ' . $e->getMessage());
    }
} else {
    // If not a POST request, redirect back to signup form
    header('Location: signup.html');
    exit;
}
?>
<?php