<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'connect.php';

if (isset($_POST["register"])) {
    // Retrieve Form Data
    $lastname = trim($_POST["lastname"]);
    $firstname = trim($_POST["firstname"]);
    $age = intval($_POST["age"]);
    $email = trim($_POST["email"]);
    $contact = trim($_POST["contact"]);
    $address = trim($_POST["address"]);
    $department = trim($_POST["department"]);
    $course = trim($_POST["course"]);
    $yearr = trim($_POST["year"]);
    $section = trim($_POST["section"]);
    $groupp = trim($_POST["groupp"]);
    $username = trim($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $gender = $_POST["gender"];
    $profilePicPath = NULL;

    // ✅ Step 1: Check if email already exists
    $checkEmailStmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();

    if ($checkEmailStmt->num_rows > 0) {
        echo "<script>
                alert('Error: Email already exists! Please use a different email.');
                window.history.back(); // Redirect back to the registration page
              </script>";
        exit();
    }
    $checkEmailStmt->close();

    // ✅ Step 2: Check if username already exists
    $checkUsernameStmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $checkUsernameStmt->bind_param("s", $username);
    $checkUsernameStmt->execute();
    $checkUsernameStmt->store_result();

    if ($checkUsernameStmt->num_rows > 0) {
        echo "<script>
                alert('Error: Username already taken! Please choose another username.');
                window.history.back();
              </script>";
        exit();
    }
    $checkUsernameStmt->close();

    // ✅ Step 3: Handle Profile Picture Upload
    if (!empty($_FILES["profilepic"]["name"])) {
        $uploadDir = "uploads/"; // Ensure the 'uploads' directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $profilePicPath = $uploadDir . basename($_FILES["profilepic"]["name"]);

        if (!move_uploaded_file($_FILES["profilepic"]["tmp_name"], $profilePicPath)) {
            echo "<script>
                    alert('Error uploading profile picture. Please try again.');
                    window.location.href = '../index.php'; // Redirect to home page
                  </script>";
            exit();
        }
    }

    // ✅ Step 4: Insert Data into Database
    $stmt = $conn->prepare("INSERT INTO users (lastname, firstname, age, email, contact, address, department, course, yearr, section, groupp, username, password, gender, profilepic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo "<script>
                alert('Database error! Please try again later.');
                window.location.href = '../index.php';
              </script>";
        exit();
    }

    $stmt->bind_param("ssissssssssssss", $lastname, $firstname, $age, $email, $contact, $address, $department, $course, $yearr, $section, $groupp, $username, $password, $gender, $profilePicPath);

    if ($stmt->execute()) {
        // ✅ Successful Registration Alert & Redirect
        echo "<script>
                alert('Registration successful! Redirecting to login page.');
                window.location.href = '../userin.php';
              </script>";
        exit();
    } else {
        // ❌ Unsuccessful Registration Alert & Redirect
        echo "<script>
                alert('Error: Registration failed! Please try again.');
                window.location.href = '../index.php';
              </script>";
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
