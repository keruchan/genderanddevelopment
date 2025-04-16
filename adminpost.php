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

// Handle story deletion with confirmation
if (isset($_POST['delete_story'])) {
    $storyId = $_POST['story_id'];

    if (!empty($storyId)) {
        $stmt = $conn->prepare("DELETE FROM stories WHERE id = ?");
        $stmt->bind_param("i", $storyId);
        
        if ($stmt->execute()) {
            $_SESSION['delete_message'] = "Story deleted successfully.";
        } else {
            $_SESSION['delete_message'] = "Failed to delete the story.";
        }
        $stmt->close();
    }
    header("Location: adminpost.php");
    exit();
}

// Fetch stories from the database
$stmt = $conn->prepare("SELECT id, title, writer, date_published, picture, content FROM stories ORDER BY date_published DESC");
$stmt->execute();
$stmt->bind_result($id, $title, $writer, $date_published, $picture, $content);
$stories = [];
while ($stmt->fetch()) {
    $stories[] = [
        'id' => $id,
        'title' => $title,
        'writer' => $writer,
        'date_published' => $date_published,
        'picture' => $picture,
        'content' => $content
    ];
}
$stmt->close();
?>

<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigationold.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GAD Stories</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
        .create-button {
            display: block;
            width: 200px;
            background: #0779e4;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
            font-size: 18px;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            margin: 20px auto;
            transition: background 0.3s ease;
        }
        .create-button:hover {
            background: #055bb5;
        }
        .stories {
            margin-top: 20px;
        }
        .story {
            background: #fff;
            padding: 20px;
            margin: 10px 0;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .story img {
            width: 100%;
            border-radius: 10px;
        }
        .story h2 {
            color: #333;
            margin-top: 10px;
        }
        .story p {
            color: #666;
        }
        .delete-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            color: #e74c3c;
            font-size: 20px;
        }
        .delete-button:hover {
            color: #c0392b;
        }
    </style>
    <script>
        function confirmDelete(storyId) {
            if (confirm('Are you sure you want to delete this story?')) {
                document.getElementById('delete-form-' + storyId).submit();
            }
        }
    </script>
</head>
<body>
    <header id="main-header">
        <div class="container">
            <h1>GAD Stories</h1>
        </div>
    </header>

    <div class="container">
        <a href="create_story.php" class="create-button">Create New Story</a>

        <?php
        // Show delete success message if available
        if (isset($_SESSION['delete_message'])) {
            echo "<script>alert('" . $_SESSION['delete_message'] . "');</script>";
            unset($_SESSION['delete_message']); // Remove message after displaying
        }
        ?>

        <div class="stories">
            <?php foreach ($stories as $story): ?>
            <div class="story">
                <form id="delete-form-<?php echo $story['id']; ?>" action="adminpost.php" method="post" style="position: absolute; top: 10px; right: 10px;">
                    <input type="hidden" name="story_id" value="<?php echo $story['id']; ?>">
                    <input type="hidden" name="delete_story" value="1">
                    <button type="button" class="delete-button" onclick="confirmDelete(<?php echo $story['id']; ?>)"><i class="fas fa-trash"></i></button>
                </form>
                <img src="images/<?php echo htmlspecialchars($story['picture']); ?>" alt="">
                <h2><?php echo htmlspecialchars($story['title']); ?></h2>
                <p><strong><?php echo htmlspecialchars($story['writer']); ?></strong></p>
                <p><em><?php echo htmlspecialchars($story['date_published']); ?></em></p>
                <p><?php echo htmlspecialchars($story['content']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer id="main-footer">
        <p>Admin Post &copy; 2025, All Rights Reserved</p>
    </footer>
</body>
</html>
