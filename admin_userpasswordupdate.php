<?php
include_once('temp/header.php');
include_once('temp/navigationold.php');
require 'connecting/connect.php';

// Get the user ID from the URL (passed when the "Edit" button is clicked)
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Fetch user data for the given ID
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Check if the user exists
    if (!$user) {
        echo "User not found.";
        exit;
    }
} else {
    echo "No user ID provided.";
    exit;
}

// Handle password update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the admin password, new password, and confirm password
    $adminPassword = $_POST['adminPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate the admin password
    $adminId = 1; // assuming the admin ID is 1, change as necessary
    $adminQuery = $conn->prepare("SELECT password FROM admins WHERE id = ?");
    $adminQuery->bind_param("i", $adminId);
    $adminQuery->execute();
    $adminResult = $adminQuery->get_result()->fetch_assoc();
    $adminQuery->close();

    // Verify the admin password using md5
    if (md5($adminPassword) == $adminResult['password']) {
        // Check if passwords match
        if ($newPassword === $confirmPassword) {
            // Hash the new password for user (using password_hash)
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the password in the database
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->bind_param("si", $hashedPassword, $userId);
            if ($updateStmt->execute()) {
                $message = "Password updated successfully.";
                $messageType = 'success'; // Add this to identify success message
            } else {
                $message = "Error updating password.";
                $messageType = 'error'; // Add this to identify error message
            }
            $updateStmt->close();
        } else {
            $message = "New passwords do not match.";
            $messageType = 'error'; // Add this to identify error message
        }
    } else {
        $message = "Admin password is incorrect.";
        $messageType = 'error'; // Add this to identify error message
    }
}

?>

<div class="user-management-container">
    <h1>Update User Password</h1>

    <?php if (isset($message)) : ?>
        <div class="alert <?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Form to update the user's password -->
    <form action="admin_userpasswordupdate.php?id=<?= $userId ?>" method="POST">
        <div>
            <label for="username">Username</label>
            <!-- Username is read-only so it's not modifiable -->
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" readonly>
        </div>

        <div>
            <label for="adminPassword">Admin Password</label>
            <input type="password" id="adminPassword" name="adminPassword" required>
        </div>

        <div>
            <label for="newPassword">New Password</label>
            <input type="password" id="newPassword" name="newPassword" required>
        </div>

        <div>
            <label for="confirmPassword">Confirm New Password</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required>
        </div>

        <div>
            <button type="submit">Update Password</button>
        </div>
    </form>

</div>

<script>
    // Optional: Add JavaScript to improve user experience (e.g., password visibility toggle)
</script>

<style>
    .user-management-container {
        padding: 20px;
        text-align: center;
    }

    form {
        width: 50%;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 8px;
        background-color: #f9f9f9;
    }

    form div {
        margin-bottom: 15px;
    }

    form label {
        display: block;
        margin-bottom: 5px;
    }

    form input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 1.2em; /* Increase font size for text inside the textboxes */
    }

    form button {
        background-color: #007bff;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    form button:hover {
        background-color: #0056b3;
    }

    .alert {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .alert.success {
        background-color: #d4edda; /* Light green background */
        color: #155724; /* Dark green text for better readability */
    }

    .alert.error {
        background-color: #f8d7da; /* Light red background for errors */
        color: #721c24; /* Dark red text for errors */
    }

    .alert.success, .alert.error {
        font-size: 1.2em;
    }
</style>

<?php include_once('temp/footeradmin.php'); ?>
