<?php
session_start();
require 'connecting/connect.php'; // Ensure correct path

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $attachment = $_FILES['attachment']['name'];

    // Validate the event date to be greater than today
    $current_date = date('Y-m-d');
    if ($event_date <= $current_date) {
        echo "<script>alert('The event date must be greater than the current date.');</script>";
    } else {
        // Handle file upload
        $target_dir = "attachments/";
        $target_file = $target_dir . basename($attachment);
        if (!empty($attachment)) {
            move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file);
        }

        // Insert event into the database
        $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, start_time, end_time, attachment_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $description, $event_date, $start_time, $end_time, $attachment);
        if ($stmt->execute()) {
            echo "<script>alert('Event added successfully!');</script>";
        } else {
            echo "<script>alert('Failed to add event.');</script>";
        }
        $stmt->close();
    }
}
?>

<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigationold.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event</title>
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
        .container {
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
            font-size: 18px;

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

    <div class="container">
        <div class="form-wrap">
            <h1>Upload New Event</h1>
            <form action="admin_addeventlist.php" method="post" enctype="multipart/form-data" onsubmit="return validateEventDate()">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required>

                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" required></textarea>

                <label for="event_date">Event Date</label>
                <input type="date" id="event_date" name="event_date" required>

                <label for="start_time">Start Time</label>
                <input type="time" id="start_time" name="start_time" required>

                <label for="end_time">End Time</label>
                <input type="time" id="end_time" name="end_time" required>

                <label for="attachment">Attachment</label>
                <input type="file" id="attachment" name="attachment">

                <button type="submit">Post Event</button>
            </form>
        </div>
    </div>

    <script>
        function validateEventDate() {
            const eventDate = document.getElementById('event_date').value;
            const currentDate = new Date().toISOString().split('T')[0]; // Get current date in YYYY-MM-DD format

            if (eventDate <= currentDate) {
                alert('The event date must be greater than the current date.');
                return false;
            }
            return true;
        }
    </script>

</body>
</html>
