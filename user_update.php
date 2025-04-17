<?php
// Start a session and include necessary files
session_start();
ob_start(); // Start output buffering
require 'connecting/connect.php'; // Include database connection
include_once('temp/header.php');
include_once('temp/navigation.php');

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('Please log in to access this page.');
            window.location.href = 'login.php';
          </script>";
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "<script>
            alert('User not found.');
            window.location.href = 'index.php';
          </script>";
    exit();
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f3f4f6;
        color: #333;
    }

    .container {
        max-width: 700px;
        margin: 40px auto;
        padding: 30px;
        background-color: #ffffff;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        border: 1px solid #e0e0e0;
    }

    .container h2 {
        text-align: center;
        font-size: 24px;
        color: #007bff;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 14px;
    }

    .form-group input, .form-group select, .form-group button {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .form-group input:focus, .form-group select:focus {
        border-color: #007bff;
        outline: none;
    }

    .form-group button {
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    .form-group button:hover {
        background-color: #0056b3;
    }

    .form-group input[type="file"] {
        border: none;
        padding: 10px 12px;
    }

    .form-group input[type="number"], .form-group input[type="text"], .form-group input[type="email"], .form-group select {
        background-color: #f8f9fa;
    }

    .form-group input[type="number"]:focus, .form-group input[type="text"]:focus, .form-group input[type="email"]:focus, .form-group select:focus {
        background-color: #ffffff;
    }

    .form-group #impairment-group {
        display: none;
        margin-top: 10px;
    }

    .form-group button:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }

    .form-footer {
        text-align: center;
        margin-top: 20px;
    }
    
    .form-footer a {
        color: #007bff;
        text-decoration: none;
        font-weight: 600;
    }

    .form-footer a:hover {
        text-decoration: underline;
    }
</style>

<div class="container">
    <h2>Update User Information</h2>
    <form action="user_update.php" method="POST" enctype="multipart/form-data" 
          oninput="document.getElementById('impairment-group').style.display = (document.getElementById('group').value === 'PWD') ? 'block' : 'none'">

        <div class="form-group">
            <label for="lastname">Last Name</label>
            <input type="text" name="lastname" id="lastname" class="form-control" 
                   value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="firstname">First Name</label>
            <input type="text" name="firstname" id="firstname" class="form-control" 
                   value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="age">Age</label>
            <input type="number" name="age" id="age" class="form-control" 
                   value="<?php echo htmlspecialchars($user['age']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" 
                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="contact">Contact</label>
            <input type="text" name="contact" id="contact" class="form-control" 
                   value="<?php echo htmlspecialchars($user['contact']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" name="address" id="address" class="form-control" 
                   value="<?php echo htmlspecialchars($user['address']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" name="department" id="department" class="form-control" 
                   value="<?php echo htmlspecialchars($user['department']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="course">Course</label>
            <input type="text" name="course" id="course" class="form-control" 
                   value="<?php echo htmlspecialchars($user['course']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="year">Year</label>
            <input type="text" name="year" id="year" class="form-control" 
                   value="<?php echo htmlspecialchars($user['yearr']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="section">Section</label>
            <input type="text" name="section" id="section" class="form-control" 
                   value="<?php echo htmlspecialchars($user['section']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="group">Group</label>
            <input type="text" name="group" id="group" class="form-control" 
                   value="<?php echo htmlspecialchars($user['groupp']); ?>" required>
        </div>
        
        <div class="form-group" id="impairment-group" style="display: <?php echo (strtoupper($user['groupp']) === 'PWD') ? 'block' : 'none'; ?>;">
    <label for="impairment">Impairment</label>
    <input type="text" name="impairment" id="impairment" class="form-control" 
           value="<?php echo htmlspecialchars($user['impairment']); ?>">
</div>

        
        <div class="form-group">
            <label for="gender">Gender</label>
            <select name="gender" id="gender" class="form-control">
                <option value="male" <?php echo ($user['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo ($user['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                <option value="other" <?php echo ($user['gender'] === 'other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="profilepic">Profile Picture</label>
            <input type="file" name="profilepic" id="profilepic" class="form-control">
        </div>
        
        <button type="submit" name="update" class="btn btn-primary">Update</button>
    </form>

    <div class="form-footer">
        <a href="index.php">Back</a>
    </div>
</div>

<?php include_once('temp/footer.php'); ?>

<?php
// Start output buffering at the very beginning of the script
ob_start(); 

// Your error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection file
require 'connecting/connect.php';

// Form processing logic
if (isset($_POST["update"])) {
    // Retrieve & Trim Form Data
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
    $groupp = trim($_POST["group"]);
    $gender = $_POST["gender"];
    $impair = trim($_POST["impairment"]);
    $profilePicPath = NULL;

    // Basic Validation
    if (empty($lastname) || empty($firstname) || empty($email)) {
        echo "<script>
                alert('All fields marked with * are required!');
                window.location.href = 'user_update.php';
              </script>";
        exit();
    }

    // Check if Email Exists for another user
    $checkEmailStmt = $conn->prepare("SELECT email FROM users WHERE email = ? AND id != ?");
    $checkEmailStmt->bind_param("si", $email, $user_id);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();
    if ($checkEmailStmt->num_rows > 0) {
        echo "<script>
                alert('Email already exists! Use another email.');
                window.location.href = 'user_update.php';
              </script>";
        exit();
    }
    $checkEmailStmt->close();

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
                    window.location.href = 'user_update.php';
                  </script>";
            exit();
        }

        if ($_FILES["profilepic"]["size"] > 2 * 1024 * 1024) { // Max 2MB
            echo "<script>
                    alert('Profile picture size should not exceed 2MB.');
                    window.location.href = 'user_update.php';
                  </script>";
            exit();
        }

        $profilePicPath = $uploadDir . uniqid() . '_' . basename($_FILES["profilepic"]["name"]);
        if (!move_uploaded_file($_FILES["profilepic"]["tmp_name"], $profilePicPath)) {
            echo "<script>
                    alert('Failed to upload profile picture.');
                    window.location.href = 'user_update.php';
                  </script>";
            exit();
        }
    }

    // Update User Data
    $stmt = $conn->prepare("UPDATE users SET lastname=?, firstname=?, age=?, email=?, contact=?, address=?, department=?, course=?, yearr=?, section=?, groupp=?, gender=?, impairment=?, profilepic=? WHERE id=?");

    if (!$stmt) {
        echo "<script>
                alert('Database error! Please try again.');
                window.location.href = 'user_update.php';
              </script>";
        exit();
    }

    $stmt->bind_param("ssisssssssssssi", $lastname, $firstname, $age, $email, $contact, $address, $department, $course, $yearr, $section, $groupp, $gender, $impair, $profilePicPath, $user_id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Update successful!');
                window.location.href = 'index.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Update failed. Try again!');
                window.location.href = 'user_update.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
} else {
    exit();
}

// End output buffering and flush the output buffer at the end of the script
ob_end_flush();
?>
