<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'connect.php';

if (isset($_POST["login-btn"])) {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // First, check if the user is an Admin
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();   
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($admin_id, $admin_username, $admin_password);
        $stmt->fetch();

        // Verify admin password (use md5 if already stored that way)
        if (md5($password) == $admin_password) {
            // Set session for admin
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['admin_username'] = $admin_username;

            // Redirect to Admin Panel
            echo "<script>
                    alert('Admin login successful! Redirecting to Admin Dashboard...');
                    window.location.href = '../admin.php';
                  </script>";
            exit();
        }
    }

    // If not an admin, check the Users table
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();   
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $user_username, $user_password);
        $stmt->fetch();

        // Verify hashed password for users
        if (password_verify($password, $user_password)) {
            // Set session for user
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $user_username;

            // Redirect to User Dashboard
            echo "<script>
                    alert('Login successful! Redirecting to Home...');
                    window.location.href = '../index.php';
                  </script>";
            exit();
        } else {
            // Incorrect password
            echo "<script>
                    alert('Error: Incorrect password! Try again.');
                    window.location.href = '../userin.php';
                  </script>";
            exit();
        }
    } else {
        // Username not found
        echo "<script>
                alert('Error: Username not found! Please check and try again.');
                window.location.href = '../userin.php';
              </script>";
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
