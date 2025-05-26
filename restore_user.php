<?php
require 'connecting/connect.php';

// Get the user ID from the URL
$user_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($user_id) {
    // Fetch archived user data
    $userQuery = $conn->prepare("SELECT * FROM admin_archived_users WHERE id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows > 0) {
        // Fetch user data
        $userData = $userResult->fetch_assoc();

        // Insert the user data back to the 'users' table
        $insertQuery = $conn->prepare("INSERT INTO users 
            (id, lastname, firstname, age, email, contact, address, department, course, yearr, section, community, username, password, gender, profilepic, registration_date, impairment) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

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
            $userData['community'], 
            $userData['username'], 
            $userData['password'], 
            $userData['gender'], 
            $userData['profilepic'], 
            $userData['registration_date'], 
            $userData['impairment']);

        // Execute the insertion
        if ($insertQuery->execute()) {
            // After restoring, delete the user from the archived table
            $deleteQuery = $conn->prepare("DELETE FROM admin_archived_users WHERE id = ?");
            $deleteQuery->bind_param("i", $user_id);
            $deleteQuery->execute();

            // Return success
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to restore user."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "User not found in the archive."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No user selected."]);
}

$conn->close();
?>
