<?php
session_start();
session_destroy(); // Destroy session

// Store logout message in a session variable
session_start();
$_SESSION['logout_message'] = "You have successfully logged out.";

header("Location: ../index.php"); // Redirect to index.php
exit();
?>
