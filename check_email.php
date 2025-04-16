<?php
require 'connecting/connect.php'; // Make sure your path is correct

header('Content-Type: application/json'); // Set the header to JSON

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["email"])) {
    $email = trim($_POST["email"]);

    if (!empty($email)) {
        // Prepare the SQL query to check if email exists
        $checkEmailStmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $checkEmailStmt->bind_param("s", $email);
        $checkEmailStmt->execute();
        $checkEmailStmt->store_result();

        if ($checkEmailStmt->num_rows > 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Error: Email already exists! Please use a different email."
            ]);
        } else {
            echo json_encode(["status" => "success"]);
        }

        $checkEmailStmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Email is required."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request"]);
}

$conn->close();
?>
