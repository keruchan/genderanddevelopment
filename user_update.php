<?php include_once('temp/header.php') ?>
<?php include_once('temp/navigation.php') ?>
<style>
    .container {
        max-width: 600px;
        margin: auto;
        padding: 20px;
        background-color: #f9f9f9;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    .container h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
    }

    .form-group input, .form-group select, .form-group button {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .form-group button {
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
    }

    .form-group button:hover {
        background-color: #0056b3;
    }
</style>
<div class="container">
    <h2>Update User Information</h2>
    <form action="user_update.php" method="POST" enctype="multipart/form-data"
          oninput="document.getElementById('impairment-group').style.display = (document.getElementById('group').value === 'PWD') ? 'block' : 'none'">
        <div class="form-group">
            <label for="lastname">Last Name *</label>
            <input type="text" name="lastname" id="lastname" class="form-control" value="<?php echo $_SESSION['lastname']; ?>" required>
        </div>
        <div class="form-group">
            <label for="firstname">First Name *</label>
            <input type="text" name="firstname" id="firstname" class="form-control" value="<?php echo $_SESSION['firstname']; ?>" required>
        </div>
        <div class="form-group">
            <label for="age">Age</label>
            <input type="number" name="age" id="age" class="form-control" value="<?php echo $_SESSION['age']; ?>">
        </div>
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo $_SESSION['email']; ?>" required>
        </div>
        <div class="form-group">
            <label for="contact">Contact</label>
            <input type="text" name="contact" id="contact" class="form-control" value="<?php echo $_SESSION['contact']; ?>">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" name="address" id="address" class="form-control" value="<?php echo $_SESSION['address']; ?>">
        </div>
        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" name="department" id="department" class="form-control" value="<?php echo $_SESSION['department']; ?>">
        </div>
        <div class="form-group">
            <label for="course">Course</label>
            <input type="text" name="course" id="course" class="form-control" value="<?php echo $_SESSION['course']; ?>">
        </div>
        <div class="form-group">
            <label for="year">Year</label>
            <input type="text" name="year" id="year" class="form-control" value="<?php echo $_SESSION['year']; ?>">
        </div>
        <div class="form-group">
            <label for="section">Section</label>
            <input type="text" name="section" id="section" class="form-control" value="<?php echo $_SESSION['section']; ?>">
        </div>
        <div class="form-group">
            <label for="group">Group</label>
            <input type="text" name="group" id="group" class="form-control" value="<?php echo $_SESSION['groupp']; ?>">
        </div>
        <div class="form-group" id="impairment-group" style="display: <?php echo ($_SESSION['groupp'] === 'PWD') ? 'block' : 'none'; ?>;">
            <label for="impairment">Impairment</label>
            <?php 
            $impairment = $_SESSION['impairment'];
            if (empty($impairment)) {
                $impairment = 'NA';
                echo "<input type='text' name='impairment' id='impairment' class='form-control' value='$impairment' readonly>";
            } else {
                echo "<input type='text' name='impairment' id='impairment' class='form-control' value='$impairment'>";
            }
            ?>
        </div>
        <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" name="username" id="username" class="form-control" value="<?php echo $_SESSION['username']; ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="gender">Gender</label>
            <select name="gender" id="gender" class="form-control">
                <option value="male" <?php echo ($_SESSION['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo ($_SESSION['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                <option value="other" <?php echo ($_SESSION['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div class="form-group">
            <label for="profilepic">Profile Picture</label>
            <input type="file" name="profilepic" id="profilepic" class="form-control">
        </div>
        <button type="submit" name="update" class="btn btn-primary">Update</button>
    </form>
</div>
<?php include_once('temp/footer.php') ?>

<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'connecting/connect.php';

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
    $username = trim($_POST["username"]);
    $password = password_hash(trim($_POST["password"]), PASSWORD_BCRYPT);
    $gender = $_POST["gender"];
    $impair = trim($_POST["impairment"]);
    $profilePicPath = NULL;

    // Basic Validation
    if (empty($lastname) || empty($firstname) || empty($email) || empty($username) || empty($_POST["password"])) {
        echo "<script>
                alert('All fields marked with * are required!');
                window.location.href = 'user_update.php';
              </script>";
        exit();
    }

    // Check if Email Exists for another user
    $checkEmailStmt = $conn->prepare("SELECT email FROM users WHERE email = ? AND username != ?");
    $checkEmailStmt->bind_param("ss", $email, $username);
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
    $stmt = $conn->prepare("UPDATE users SET lastname=?, firstname=?, age=?, email=?, contact=?, address=?, department=?, course=?, yearr=?, section=?, groupp=?, password=?, gender=?, impairment=?, profilepic=? WHERE username=?");

    if (!$stmt) {
        echo "<script>
                alert('Database error! Please try again.');
                window.location.href = 'user_update.php';
              </script>";
        exit();
    }

    $stmt->bind_param("ssisssssssssssss", $lastname, $firstname, $age, $email, $contact, $address, $department, $course, $yearr, $section, $groupp, $password, $gender, $impair, $profilePicPath, $username);

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
    header("Location: user_update.php");
    exit();
}
?>