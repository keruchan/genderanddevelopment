<?php
session_start();
require 'connecting/connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('Please log in to access this page.'); window.location.href = 'admin_login.php';</script>";
    exit();
}

// Get the user ID from the URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($user_id) {
    // Check if user exists
    $checkQuery = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $checkQuery->bind_param("i", $user_id);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if ($result->num_rows > 0) {
        // Update 'archived' column to 1
        $updateQuery = $conn->prepare("UPDATE users SET archived = 1 WHERE id = ?");
        $updateQuery->bind_param("i", $user_id);

        if ($updateQuery->execute()) {
            echo "<script>
                alert('User successfully archived.');
                window.location.href = 'usersadmin.php';
            </script>";
        } else {
            echo "<script>
                alert('Failed to archive user. Please try again.');
                window.location.href = 'usersadmin.php';
            </script>";
        }

        $updateQuery->close();
    } else {
        echo "<script>
            alert('User not found.');
            window.location.href = 'usersadmin.php';
        </script>";
    }

    $checkQuery->close();
} else {
    echo "<script>
        alert('No user selected.');
        window.location.href = 'usersadmin.php';
    </script>";
}

$conn->close();
?>
