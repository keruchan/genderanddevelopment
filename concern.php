<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'connecting/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: userin.php");
    exit();
}

$successMessage = "";
$errorMessage = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION['user_id'];
    $concernType = trim($_POST['options']) === "Others" && !empty($_POST['customOption'])
        ? trim($_POST['customOption'])
        : trim($_POST['options']);

    $description = trim($_POST['message']);
    $fileName = "";

    if (!empty($_FILES['image']['name'])) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $uploadDir = "concernupload/";
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $fileName = $destPath;
        } else {
            $errorMessage = "File upload failed.";
        }
    }

    if (!empty($concernType) && !empty($description)) {
        $stmt = $conn->prepare("INSERT INTO concerns (user_id, concern_type, description, attachment, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isss", $userId, $concernType, $description, $fileName);

        if ($stmt->execute()) {
            $successMessage = "Your concern has been submitted successfully.";

            $notifTitle = "New GAD Concern";
            $notifMessage = "A new gender-related concern has been submitted.";
            $notifType = "concerns";
            $notifLink = "adminconcerns.php";

            $notifStmt = $conn->prepare("INSERT INTO admin_notification (title, message, type, link, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
            $notifStmt->bind_param("ssss", $notifTitle, $notifMessage, $notifType, $notifLink);
            $notifStmt->execute();
            $notifStmt->close();
        } else {
            $errorMessage = "There was an error submitting your concern. Please try again.";
        }

        $stmt->close();
    } else {
        $errorMessage = "Please fill in all required fields.";
    }
}
?>

<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigation.php'); ?>

<style>
    .auth-content {
        max-width: 600px;
        margin: 2rem auto;
        background: #ffffff;
        padding: 2rem 2.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-top: 6px solid #2b6cb0;
    }
    .form-title {
        text-align: center;
        font-size: 2.2rem;
        color: #2b6cb0;
        margin-bottom: 1.5rem;
    }
    label {
        display: block;
        margin-bottom: 0.4rem;
        font-weight: 600;
        font-size: 1.1rem;
        color: #2a4365;
    }
    .text-input,
    textarea {
        width: 100%;
        padding: 0.75rem;
        margin-bottom: 1.2rem;
        border-radius: 6px;
        border: 1px solid #cbd5e0;
        font-size: 1.05rem;
    }
    textarea {
        min-height: 120px;
        resize: vertical;
    }
    .btn-big {
        width: 100%;
        padding: 0.9rem;
        font-size: 1.15rem;
        border: none;
        border-radius: 6px;
        background-color: #2b6cb0;
        color: white;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .btn-big:hover {
        background-color: #2c5282;
    }
    .success {
        color: green;
        text-align: center;
        margin-bottom: 1rem;
    }
    .error {
        color: red;
        text-align: center;
        margin-bottom: 1rem;
    }
</style>

<div class="auth-content">
    <form action="concern.php" method="post" enctype="multipart/form-data">
        <h2 class="form-title">GAD Concern Form</h2>

        <?php if (!empty($successMessage)): ?>
            <div class="success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <div>
            <label for="options">Type of Concern</label>
            <select id="options" name="options" class="text-input" required onchange="toggleCustomInput()">
                <option value="" disabled selected>Select a concern type</option>
                <option value="Gender-based discrimination">Gender-based discrimination</option>
                <option value="Lack of gender sensitivity">Lack of gender sensitivity</option>
                <option value="Sexual harassment and abuse">Sexual harassment and abuse</option>
                <option value="Lack of safe spaces">Lack of safe spaces</option>
                <option value="Limited representation">Limited representation</option>
                <option value="Teenage pregnancy stigma">Teenage pregnancy stigma</option>
                <option value="Others">Others</option>
            </select>
        </div>

        <div id="customInputDiv" style="display: none;">
            <label for="customOption">Please specify your concern</label>
            <input type="text" id="customOption" name="customOption" class="text-input" placeholder="Enter your custom concern">
        </div>

        <div>
            <label for="image">Attachment (Optional)</label>
            <input type="file" name="image" id="image" class="text-input">
        </div>

        <div>
            <label for="message">Description</label>
            <textarea name="message" id="message" class="text-input contact-input" placeholder="Describe your concern in detail..." required></textarea>
        </div>

        <div>
            <button type="submit" class="btn-big">Submit Concern</button>
        </div>
    </form>
</div>

<script>
function toggleCustomInput() {
    const select = document.getElementById('options');
    const customDiv = document.getElementById('customInputDiv');
    const customInput = document.getElementById('customOption');

    if (select.value === 'Others') {
        customDiv.style.display = 'block';
        customInput.setAttribute('required', 'required');
    } else {
        customDiv.style.display = 'none';
        customInput.removeAttribute('required');
    }
}
</script>

<?php include_once('temp/footer.php'); ?>