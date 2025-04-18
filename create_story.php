<?php 
session_start();
require 'connecting/connect.php'; // Ensure correct path

$displayName = '<i class="fa fa-user"></i> LOGIN'; // Default login text
$dropdownMenu = ''; // Empty by default

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Fetch first name from the database
    $stmt = $conn->prepare("SELECT firstname FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($firstName);
    $stmt->fetch();
    $stmt->close();

    if (!empty($firstName)) {
        $displayName = '<i class="fa fa-user"></i> ' . htmlspecialchars($firstName);
        $dropdownMenu = '
            <ul class="dropdown">
                <li><a href="user_update.php">Update</a></li>
                <li><a href="connecting/logout.php">Logout</a></li>
            </ul>';
    }
}

// Display logout notification
if (isset($_SESSION['logout_message'])) {
    echo "<script>alert('" . $_SESSION['logout_message'] . "');</script>";
    unset($_SESSION['logout_message']); // Remove message after displaying
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $picture = $_FILES['picture']['name'];
    $content = $_POST['content'];

    // Handle file upload
    $target_dir = "images/";
    $target_file = $target_dir . basename($picture);
    move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file);

    // Get current date as date_published
    $date_published = date('Y-m-d');

    // Insert story into the database
    $stmt = $conn->prepare("INSERT INTO stories (title, date_published, picture, content) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $date_published, $picture, $content);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Uploaded successfully!');</script>";
}
?>

<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigationold.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Story</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            overflow: hidden;
        }
        #main-header {
            background-color: #333;
            color: #fff;
            padding-top: 30px;
            min-height: 70px;
            border-bottom: #0779e4 3px solid;
        }
        #main-header h1 {
            text-align: center;
            text-transform: uppercase;
            margin: 0;
            font-size: 24px;
        }
        #main-footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 10px;
            margin-top: 30px;
        }
        .form-wrap {
            background: #fff;
            padding: 20px;
            margin-top: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-wrap h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .form-wrap label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        .form-wrap input, .form-wrap textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-wrap input[type="file"] {
            padding: 0;
        }
        .form-wrap button {
            display: block;
            width: 100%;
            background: #0779e4;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
            font-size: 18px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }
        .form-wrap button:hover {
            background: #055bb5;
        }
    </style>
</head>
<body>
    <header id="main-header">
        <div class="container">
            <h1>Create New Post</h1>
        </div>
    </header>

    <div class="container">
        <div class="form-wrap">
            <h1>Upload New Post</h1>
            <form action="create_story.php" method="post" enctype="multipart/form-data">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required>

                <label for="picture">Picture</label>
                <input type="file" id="picture" name="picture" accept="image/*" required>

                <label for="content">Content</label>
                <textarea id="content" name="content" rows="10" required></textarea>

                <button type="submit">Upload Post</button>
            </form>
        </div>
    </div>

    <footer id="main-footer">
        <p></p>
    </footer>
</body>
</html>
