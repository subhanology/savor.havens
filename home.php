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


// Start session and verify user login
session_start();
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to login page
    header("Location: login.html");
    exit();
}

// Database connection settings (adjust database name and credentials as needed)
$dsn = "mysql:host=localhost;dbname=restaurant_db;charset=utf8";
$dbUser = "root";
$dbPassword = "";
try {
    $pdo = new PDO($dsn, $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch user information from Customers table
// Assuming 'customer_id' is the primary key and matches Orders.customer_id
try {
    $stmtUser = $pdo->prepare("SELECT * FROM Customers WHERE username = ?");
    $stmtUser->execute([$_SESSION['username']]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}

// If user data not found, clear session and redirect to login
if (!$user) {
    session_destroy();
    header("Location: login.html");
    exit();
}

// Fetch order history for this user
$orders = [];
try {
    $stmtOrders = $pdo->prepare("SELECT * FROM Orders WHERE customer_id = ?");
    $stmtOrders->execute([$user['customer_id']]);
    $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage());
}

// Fetch reservations for this user (assuming a Reservations table exists)
$reservations = [];
try {
    $stmtRes = $pdo->prepare("SELECT * FROM Reservations WHERE customer_id = ?");
    $stmtRes->execute([$user['customer_id']]);
    $reservations = $stmtRes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If table doesn't exist or query fails, leave reservations empty
    $reservations = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Savor Havens - Dashboard</title>
  <style>
    /* Reset and base styles (same as home.html) */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #fff;
      color: #333;
      line-height: 1.6;
    }
    /* Navigation styles */
    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 40px;
      background: white;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    nav .logo {
      font-weight: 700;
      font-size: 24px;
      color: #00796b;
      cursor: default;
      user-select: none;
    }
    nav ul {
      list-style: none;
      display: flex;
      gap: 25px;
      font-weight: 600;
      color: #00796b;
      align-items: center;
    }
    nav ul li a {
      text-decoration: none;
      color: #00796b;
      transition: color 0.3s ease;
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 16px;
    }
    nav ul li a:hover {
      color: #004d40;
    }
    /* Icons */
    .icon {
      width: 20px;
      height: 20px;
      fill: currentColor;
    }
    /* Hero section styles */
    .hero {
      position: relative;
      width: 100%;
      height: 80vh;
      overflow: hidden;
    }
    .hero img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .hero-text {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      font-size: 3rem;
      font-weight: 700;
      text-shadow: 0 0 15px rgba(0,0,0,0.7);
      text-align: center;
      max-width: 90%;
      padding: 0 20px;
    }
    /* Main content container */
    .container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }
    h2 {
      text-align: center;
      color: #00796b;
      margin-bottom: 30px;
      font-weight: 700;
      font-size: 2.2rem;
    }
    /* Gallery section */
    #gallery {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 25px;
      margin-bottom: 60px;
    }
    #gallery img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
      cursor: pointer;
    }
    #gallery img:hover {
      transform: scale(1.05);
    }
    /* Testimonials section */
    #testimonials {
      background: #f1f8f7;
      padding: 60px 20px;
      border-radius: 12px;
      max-width: 900px;
      margin: 0 auto 60px auto;
    }
    #testimonials .testimonial-box {
      font-style: italic;
      font-size: 1.2rem;
      color: #555;
      line-height: 1.5;
      position: relative;
      padding-left: 40px;
      margin-bottom: 40px;
    }
    #testimonials .testimonial-box:last-child {
      margin-bottom: 0;
    }
    #testimonials .testimonial-box::before {
      content: '“';
      font-size: 3rem;
      color: #00796b;
      position: absolute;
      left: 10px;
      top: -10px;
      font-weight: 700;
      line-height: 1;
    }
    /* Team section */
    #team {
      padding: 80px 20px;
    }
    #team .team-members {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      justify-content: center;
    }
    #team .team-member {
      background: #e0f2f1;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      padding: 20px;
      max-width: 250px;
      text-align: center;
    }
    #team .team-member img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    #team .team-member h3 {
      color: #00796b;
      margin-bottom: 6px;
      font-size: 1.2rem;
    }
    #team .team-member p {
      font-size: 0.95rem;
      color: #444;
    }
    /* Footer */
    footer {
      background: #004d40;
      color: #e0f2f1;
      text-align: center;
      padding: 20px 10px;
      font-size: 0.9rem;
    }
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .hero-text {
        font-size: 2rem;
      }
      #team .team-members {
        flex-direction: column;
        align-items: center;
      }
    }
    /* Account panel styles */
    #accountPanel {
      position: fixed;
      top: 0;
      right: -300px; /* Hidden by default */
      width: 300px;
      height: 100%;
      background: white;
      box-shadow: -2px 0 5px rgba(0,0,0,0.2);
      transition: right 0.3s ease;
      padding: 20px;
      z-index: 2000;
    }
    #accountPanel.open {
      right: 0; /* Slide into view */
    }
    #accountPanel .panel-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    #accountPanel .panel-header span {
      font-size: 1.2rem;
      font-weight: 700;
      color: #00796b;
    }
    #closeAccount {
      font-size: 1.5rem;
      cursor: pointer;
      color: #00796b;
      border: none;
      background: none;
    }
    #accountPanel .panel-content {
      font-size: 0.95rem;
      color: #444;
    }
    #accountPanel .panel-content h4 {
      margin-bottom: 10px;
      color: #00796b;
    }
    #accountPanel .panel-content p {
      margin-bottom: 10px;
    }
    #accountPanel .panel-content ul {
      list-style: none;
      padding-left: 0;
      margin: 0 0 10px 0;
    }
    #accountPanel .panel-content li {
      margin-bottom: 5px;
    }
    #accountPanel .panel-content .logout-btn {
      display: block;
      margin-top: 15px;
      padding: 10px;
      background: #00796b;
      color: white;
      text-align: center;
      text-decoration: none;
      border-radius: 6px;
      transition: background 0.3s ease;
    }
    #accountPanel .panel-content .logout-btn:hover {
      background: #004d40;
    }
    /* Overlay to dim background when panel is open */
    #accountOverlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: none;
      background: rgba(0,0,0,0.3);
      z-index: 1500;
    }
    #accountOverlay.show {
      display: block;
    }
  </style>
</head>
<body>
  <!-- Navigation bar -->
  <nav>
    <div class="logo">Savor Havens</div>
    <ul>
      <li><a href="#home">Home</a></li>
      <li><a href="#about">About</a></li>
      <li><a href="#menu">Menu</a></li>
      <li><a href="#contact">Contact</a></li>
      <li>
        <!-- Cart icon (non-functional placeholder) -->
        <a href="#cart" aria-label="Cart">
          <svg class="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm0 
            2m10-2c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2zm-9.83-3h11.17c.55 
            0 1.04-.35 1.21-.87l2.58-7.49a.996.996 0 0 0-.96-1.34H6.21L5.27 4H2v2h2l3.6 
            7.59-1.35 2.44c-.18.32-.27.69-.27 1.07 0 1.1.9 2 2 2z"/>
          </svg>
        </a>
      </li>
      <li>
        <!-- Account icon that triggers the account panel -->
        <a href="#account" aria-label="My Account" id="accountLink">
          <svg class="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 
            4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 
            4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
          </svg>
        </a>
      </li>
    </ul>
  </nav>
  <!-- Right-side account panel for profile and history -->
  <div id="accountPanel">
    <div class="panel-header">
      <span>My Account</span>
      <button id="closeAccount">&times;</button>
    </div>
    <div class="panel-content">
      <h4>My Profile</h4>
      <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
      <p><strong>Contact:</strong> <?php echo htmlspecialchars($user['contact']); ?></p>
      <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
      <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>

      <h4>Order History</h4>
      <?php if (count($orders) > 0): ?>
        <?php foreach ($orders as $order): ?>
          <p>Order #<?php echo $order['order_id']; ?> - <?php echo $order['order_date']; ?></p>
          <ul>
            <?php
              // Fetch items for this order (assuming OrderDetails and MenuItems tables)
              $stmtItems = $pdo->prepare(
                "SELECT MenuItems.name, OrderDetails.quantity 
                 FROM OrderDetails 
                 JOIN MenuItems ON OrderDetails.item_id = MenuItems.item_id 
                 WHERE OrderDetails.order_id = ?"
              );
              $stmtItems->execute([$order['order_id']]);
              $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
              foreach ($items as $item) {
                  echo "<li>" . htmlspecialchars($item['name']) . " x" . $item['quantity'] . "</li>";
              }
            ?>
          </ul>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No orders found.</p>
      <?php endif; ?>

      <h4>Reservations</h4>
      <?php if (count($reservations) > 0): ?>
        <?php foreach ($reservations as $res): ?>
          <p><?php echo $res['reservation_date']; ?> - Party of <?php echo $res['party_size']; ?></p>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No reservations made.</p>
      <?php endif; ?>

      <!-- Logout option -->
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </div>
  <!-- Overlay for closing panel when clicking outside -->
  <div id="accountOverlay"></div>
  <!-- Hero section with background image -->
  <section class="hero" id="home">
    <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1470&q=80" alt="Delicious Food" />
    <div class="hero-text">
      Welcome back, <?php echo htmlspecialchars($user['name']); ?>! Taste the magic of Savor Havens.
    </div>
  </section>
  <!-- Main content container -->
  <div class="container">
    <!-- (Optional additional dashboard content could go here) -->
  </div>
  <!-- Footer -->
  <footer>
    &copy; 2025 Savor Havens. All Rights Reserved.
  </footer>
  <!-- JavaScript to handle account panel toggling -->
  <script>
    const accountLink = document.getElementById('accountLink');
    const accountPanel = document.getElementById('accountPanel');
    const closeBtn = document.getElementById('closeAccount');
    const overlay = document.getElementById('accountOverlay');
    // Open panel when account icon is clicked
    accountLink.addEventListener('click', function(e) {
      e.preventDefault();
      accountPanel.classList.add('open');
      overlay.classList.add('show');
    });
    // Close panel when close button is clicked
    closeBtn.addEventListener('click', function() {
      accountPanel.classList.remove('open');
      overlay.classList.remove('show');
    });
    // Close panel when clicking outside of it
    overlay.addEventListener('click', function() {
      accountPanel.classList.remove('open');
      overlay.classList.remove('show');
    });
  </script>
</body>
</html>
