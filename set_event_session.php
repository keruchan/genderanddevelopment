<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $_SESSION['evaluation_event_id'] = (int) $_POST['event_id'];
    header("Location: event.php");
    exit();
}

echo "<script>
        alert('Invalid request.');
        window.location.href = 'event_list.php';
      </script>";
exit();
