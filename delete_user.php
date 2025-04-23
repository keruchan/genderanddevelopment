<?php
session_start();
require 'connecting/connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('Please log in to access this page.'); window.location.href = 'admin_login.php';</script>";
    exit();
}

// Get the user ID from the URL
$user_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($user_id) {
    // Step 1: Fetch the user data
    $userQuery = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows > 0) {
        // Step 2: Move user data to admin_archived_users table
        $userData = $userResult->fetch_assoc();

        // Insert into the admin_archived_users table
        $insertQuery = $conn->prepare("INSERT INTO admin_archived_users 
            (id, lastname, firstname, age, email, contact, address, department, course, yearr, section, groupp, username, password, gender, profilepic, registration_date, impairment) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind the parameters for insertion
        $insertQuery->bind_param("ississssssssssssss", 
            $userData['id'], 
            $userData['lastname'], 
            $userData['firstname'], 
            $userData['age'], 
            $userData['email'], 
            $userData['contact'], 
            $userData['address'], 
            $userData['department'], 
            $userData['course'], 
            $userData['yearr'], 
            $userData['section'], 
            $userData['groupp'], 
            $userData['username'], 
            $userData['password'], 
            $userData['gender'], 
            $userData['profilepic'], 
            $userData['registration_date'], 
            $userData['impairment']
        );

        // Execute the insertion into the archive table
        if ($insertQuery->execute()) {
            // Step 3: Delete the user from the 'users' table
            $deleteQuery = $conn->prepare("DELETE FROM users WHERE id = ?");
            $deleteQuery->bind_param("i", $user_id);
            $deleteQuery->execute();

            echo "<script>
                alert('User has been archived and deleted successfully.');
                window.location.href = 'usersadmin.php'; // Redirect to user management page
            </script>";
        } else {
            echo "<script>
                alert('Failed to archive user data. Please try again.');
                window.location.href = 'usersadmin.php'; // Redirect to user management page
            </script>";
        }
    } else {
        echo "<script>
            alert('User not found.');
            window.location.href = 'usersadmin.php'; // Redirect to user management page
        </script>";
    }
} else {
    echo "<script>
        alert('No user selected.');
        window.location.href = 'usersadmin.php'; // Redirect to user management page
    </script>";
}

$conn->close();
?>