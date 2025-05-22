<?php
// Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'restaurant_system');

// Connect
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    exit('Database connection failed: ' . $e->getMessage());
}

// login.php - Authenticates user and starts a session

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize input
    $username = trim($_POST['username']);
    $password = $_POST['password']; // Raw password

    // Basic validation
    if (empty($username) || empty($password)) {
        die('Please enter both username and password.');
    }

    try {
        // Connect to database
        $pdo = new PDO('mysql:host=localhost;dbname=restaurant_system;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Retrieve user record by username
        $stmt = $pdo->prepare('SELECT id, password, name FROM Customers WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // No matching user
            die('Invalid username or password.');
        }

        // Verify the password against the stored hash
        if (password_verify($password, $user['password'])) {
            // Password is correct, store user info in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['name'] = $user['name'];

            // Redirect to the home page (dashboard)
            header('Location: home.php');
            exit;
        } else {
            // Wrong password
            die('Invalid username or password.');
        }
    } catch (PDOException $e) {
        // Database error
        die('Database error: ' . $e->getMessage());
    }
} else {
    // If not a POST request, redirect to login form
    header('Location: login.html');
    exit;
}
?>
<?php