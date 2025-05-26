<?php
// Start a session and include necessary files
session_start();
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $new_username = $_POST['new_username'] ?? $user['username']; // Username is optional

    // Check if the old password is correct
    if (!password_verify($old_password, $user['password'])) {
        echo "<script>
                alert('Old password is incorrect.');
                window.location.href = 'update_password.php';
              </script>";
        exit();
    }

    // Check if new passwords match
    if ($new_password !== $confirm_password) {
        echo "<script>
                alert('New password and confirm password do not match.');
                window.location.href = 'update_password.php';
              </script>";
        exit();
    }

    // Check password strength (example: at least 8 characters)
    if (strlen($new_password) < 8) {
        echo "<script>
                alert('New password must be at least 8 characters long.');
                window.location.href = 'update_password.php';
              </script>";
        exit();
    }

    // Hash the new password
    $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Prepare the SQL statement for updating the password and username (if provided)
    if ($new_username !== $user['username']) {
        // Update both username and password if the username is changed
        $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_username, $hashed_new_password, $user_id);
    } else {
        // Update only the password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_new_password, $user_id);
    }

    // Execute the query
    if ($stmt->execute()) {
        echo "<script>
                alert('Password (and/or username) updated successfully!');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>
                alert('Update failed. Please try again.');
                window.location.href = 'update_password.php';
              </script>";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
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

    .form-community {
        margin-bottom: 20px;
    }

    .form-community label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 14px;
    }

    .form-community input, .form-community select, .form-community button {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .form-community input:focus, .form-community select:focus {
        border-color: #007bff;
        outline: none;
    }

    .form-community button {
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    .form-community button:hover {
        background-color: #0056b3;
    }

    .form-community input[type="file"] {
        border: none;
        padding: 10px 12px;
    }

    .form-community input[type="number"], .form-community input[type="text"], .form-community input[type="email"], .form-community select {
        background-color: #f8f9fa;
    }

    .form-community input[type="number"]:focus, .form-community input[type="text"]:focus, .form-community input[type="email"]:focus, .form-community select:focus {
        background-color: #ffffff;
    }

    .form-community #impairment-community {
        display: none;
        margin-top: 10px;
    }

    .form-community button:disabled {
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
    <h2>Update Username and Password</h2>
    <form action="update_password.php" method="POST">

    <div class="form-community">
            <label for="new_username">New Username (Optional)</label>
            <input type="text" name="new_username" id="new_username" class="form-control" 
                   value="<?php echo htmlspecialchars($user['username']); ?>">
        </div>

        <div class="form-community">
            <label for="old_password">Old Password *</label>
            <input type="password" name="old_password" id="old_password" class="form-control" required>
        </div>

        <div class="form-community">
            <label for="new_password">New Password *</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required>
        </div>

        <div class="form-community">
            <label for="confirm_password">Confirm New Password *</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>

    <div class="form-footer">
        <a href="index.php">Back</a>
    </div>
</div>

<?php include_once('temp/footer.php'); ?>
