<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: userin.php");
    exit();
}
require 'connecting/connect.php';
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Certificates</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; padding: 40px; background-color: #f8f9fa; }
        .container { margin-top: 30px !important; max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #007bff; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background-color: #007bff; color: white; }
        .btn { background-color: #007BFF; color: white; padding: 10px; border: none; cursor: pointer; border-radius: 4px; text-decoration: none; }
        .btn i { margin-right: 5px; }
    </style>
</head>
<body>
<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigation.php'); ?>
<div class="container">
    <h2>Your Certificates</h2>
    <table>
        <tr>
            <th>#</th>
            <th>Event</th>
            <th>Issued At</th>
            <th>Certificate</th>
        </tr>
        <?php
        $stmt = $conn->prepare("SELECT c.certificate_path, c.issued_at, e.title FROM certificates c JOIN events e ON c.event_id = e.id WHERE c.user_id = ? ORDER BY c.issued_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $i = 1;
        while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= date("F j, Y", strtotime($row['issued_at'])) ?></td>
            <td><a class="btn" href="<?= htmlspecialchars($row['certificate_path']) ?>" target="_blank"><i class="fa fa-download"></i>Download</a></td>
        </tr>
        <?php endwhile; $stmt->close(); $conn->close(); ?>
    </table>
</div>
<?php include_once('temp/footer.php'); ?>
</body>
</html>
