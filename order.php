<?php
// order.php - Handles new orders from customers

session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_SESSION['user_id'];

    // Expecting form fields named as arrays: item_id[] and quantity[]
    $item_ids = $_POST['item_id'];
    $quantities = $_POST['quantity'];

    // Basic validation of form data
    if (!is_array($item_ids) || !is_array($quantities) || count($item_ids) != count($quantities)) {
        die('Invalid order data.');
    }

    try {
        // Database connection
        $pdo = new PDO('mysql:host=localhost;dbname=restaurant_system;charset=utf8mb4', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Begin transaction to ensure atomicity
        $pdo->beginTransaction();

        // Insert new order (assuming Orders table has id, customer_id, order_date)
        $stmtOrder = $pdo->prepare('INSERT INTO Orders (customer_id, order_date) VALUES (?, NOW())');
        $stmtOrder->execute([$customer_id]);
        $order_id = $pdo->lastInsertId();

        // Prepare statement for order details
        $stmtDetails = $pdo->prepare('INSERT INTO OrderDetails (order_id, menu_item_id, quantity) VALUES (?, ?, ?)');

        // Insert each ordered item into OrderDetails
        for ($i = 0; $i < count($item_ids); $i++) {
            $item_id = intval($item_ids[$i]);
            $quantity = intval($quantities[$i]);
            if ($quantity > 0) {
                $stmtDetails->execute([$order_id, $item_id, $quantity]);
            }
        }

        // Commit transaction
        $pdo->commit();

        // Redirect to home (or orders page)
        header('Location: home.php');
        exit;
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        die('Database error: ' . $e->getMessage());
    }
} else {
    // If not a POST, redirect to home or menu
    header('Location: home.php');
    exit;
}
?>
