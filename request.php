<?php
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'connecting/connect.php'; // Ensure correct path

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userin.php");
    exit();
}

// Initialize messages
$successMessage = "";
$errorMessage = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_SESSION['user_id'];
    $concernType = trim($_POST['options']);
    $description = trim($_POST['message']);
    
    // Handle file upload
    $fileName = "";
    if (!empty($_FILES['image']['name'])) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $uploadDir = "requestupload/"; // Make sure this folder exists in your project
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $fileName = $destPath; // Store the path in the database
        } else {
            $errorMessage = "File upload failed.";
        }
    }

    if (!empty($concernType) && !empty($description)) {
        // Insert request into database
        $stmt = $conn->prepare("INSERT INTO requests (user_id, concern_type, description, attachment, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isss", $userId, $concernType, $description, $fileName);

        if ($stmt->execute()) {
            $successMessage = "Request submitted successfully!";
        } else {
            $errorMessage = "Error submitting request. Please try again.";
        }

        $stmt->close();
    } else {
        $errorMessage = "All fields are required.";
    }
}
?>

<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigation.php'); ?>    

<style>
    label {
        font-size: 24px;
    }
    .success {
        color: green;
        text-align: center;
        margin-bottom: 10px;
    }
    .error {
        color: red;
        text-align: center;
        margin-bottom: 10px;
    }
</style>

<div class="auth-content">
    <form action="request.php" method="post" enctype="multipart/form-data">
        <h2 class="form-title" style="font-size:40px;">Request Form</h2>

        <?php if (!empty($successMessage)): ?>
            <div class="success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <div>
            <label for="options">Type of Concern</label>
            <select id="options" name="options" class="text-input" required>
                <option value="" disabled selected>Select an option</option>
                <option value="Bullying">Bullying</option>
                <option value="LSPU Access">LSPU Access</option>
                <option value="Empowerment">Empowerment</option>
                <option value="Inclusion">Inclusion</option>
                <option value="Discrimination">Discrimination</option>
                <option value="Discrimination">Others</option>

            </select>
        </div>

        <div>
            <label>Attachments</label>
            <input type="file" name="image" class="text-input">
        </div>

        <div>
            <label>Request Description</label>
            <textarea name="message" class="text-input contact-input" placeholder="Discuss your concern here" required></textarea>
        </div>

        <div>
            <button type="submit" class="btn btn-big">Send</button>
        </div>
    </form>
</div>

<?php include_once('temp/footer.php'); ?>
