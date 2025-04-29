<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'connect.php';

if (isset($_POST["register"])) {
    // Retrieve & Trim Form Data
    $lastname = trim($_SESSION["lastname"]);
    $firstname = trim($_SESSION["firstname"]);
    $age = intval($_SESSION["age"]);
    $email = trim($_SESSION["email"]);
    $contact = trim($_SESSION["contact"]);
    $address = trim($_SESSION["address"]);
    $department = trim($_SESSION["department"]);
    $course = trim($_SESSION["course"]);
    $yearr = trim($_SESSION["year"]);
    $section = trim($_SESSION["section"]);
    $groupp = trim($_SESSION["groupp"]);
    $username = trim($_POST["username"]);
    $password = password_hash(trim($_POST["password"]), PASSWORD_BCRYPT);
    $gender = $_SESSION["gender"];
    $impair = trim($_SESSION["impairment"]);
    $profilePicPath = $_SESSION["profilepic"];

    // Basic Validation
    if (empty($lastname) || empty($firstname) || empty($email) || empty($username) || empty($_POST["password"])) {
        echo "<script>
                alert('All fields marked with * are required!');
                window.location.href = '../userup.php';
              </script>";
        exit();
    }

    // Check if Email Exists
    $checkEmailStmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();
    if ($checkEmailStmt->num_rows > 0) {
        echo "<script>
                alert('Email already exists! Use another email.');
                window.location.href = '../userup.php';
              </script>";
        exit();
    }
    $checkEmailStmt->close();

    // Check if Username Exists
    $checkUsernameStmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $checkUsernameStmt->bind_param("s", $username);
    $checkUsernameStmt->execute();
    $checkUsernameStmt->store_result();
    if ($checkUsernameStmt->num_rows > 0) {
        echo "<script>
                alert('Username already taken! Choose another.');
                window.location.href = '../userupcredentials.php';
              </script>";
        exit();
    }
    $checkUsernameStmt->close();

    // Profile Picture Upload
    if (!empty($_FILES["profilepic"]["name"])) {
        $allowedTypes = ["image/jpeg", "image/png"];
        $uploadDir = "uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileType = mime_content_type($_FILES["profilepic"]["tmp_name"]);
        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>
                    alert('Invalid profile picture format. Only JPG and PNG allowed.');
                    window.location.href = '../userup.php';
                  </script>";
            exit();
        }

        if ($_FILES["profilepic"]["size"] > 2 * 1024 * 1024) {
            echo "<script>
                    alert('Profile picture size should not exceed 2MB.');
                    window.location.href = '../userup.php';
                  </script>";
            exit();
        }

        $profilePicPath = $uploadDir . uniqid() . '_' . basename($_FILES["profilepic"]["name"]);
        if (!move_uploaded_file($_FILES["profilepic"]["tmp_name"], $profilePicPath)) {
            echo "<script>
                    alert('Failed to upload profile picture.');
                    window.location.href = '../userup.php';
                  </script>";
            exit();
        }
    }

    // Insert User Data
    $stmt = $conn->prepare("INSERT INTO users (lastname, firstname, age, email, contact, address, department, course, yearr, section, groupp, username, password, gender, impairment, profilepic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo "<script>
                alert('Database error! Please try again.');
                window.location.href = '../userup.php';
              </script>";
        exit();
    }

    $stmt->bind_param("ssisssssssssssss", $lastname, $firstname, $age, $email, $contact, $address, $department, $course, $yearr, $section, $groupp, $username, $password, $gender, $impair, $profilePicPath);

    if ($stmt->execute()) {
        // Insert notification to admin_notification table
        $notifTitle = "New User Registered";
        $notifMessage = "A new user has signed up.";
        $notifType = "new-user";
        $notifLink = "usersadmin.php";

        $notifStmt = $conn->prepare("INSERT INTO admin_notification (title, message, type, link, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
        $notifStmt->bind_param("ssss", $notifTitle, $notifMessage, $notifType, $notifLink);
        $notifStmt->execute();
        $notifStmt->close();

        echo "<script>
                alert('Registration successful!');
                window.location.href = '../userin.php?success=1';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Registration failed. Try again!');
                window.location.href = '../userup.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../userup.php");
    exit();
}
?>
