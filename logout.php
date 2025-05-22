<?php
// logout.php - Destroys the user session and redirects to login

session_start();
session_unset();
session_destroy();

// Redirect to login page after logout
header('Location: login.html');
exit;
?>
