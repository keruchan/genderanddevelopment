<?php
require 'connecting/connect.php';

if (isset($_GET['id'])) {
    $story_id = (int)$_GET['id'];

    $stmt = $conn->prepare("SELECT id, title, writer, date_published, picture, content FROM stories WHERE id = ?");
    $stmt->bind_param("i", $story_id);
    $stmt->execute();
    $stmt->bind_result($id, $title, $writer, $datePublished, $picture, $content);
    $stmt->fetch();
    $stmt->close();

    // Ensure $conn is closed properly
    $conn->close();

    echo json_encode([
        'id' => $id,
        'title' => htmlspecialchars($title),
        'writer' => htmlspecialchars($writer),
        'datePublished' => date("F j, Y", strtotime($datePublished)),
        'picture' => htmlspecialchars($picture),
        'content' => nl2br(htmlspecialchars($content))
    ]);
} else {
    echo json_encode([
        'error' => 'Story ID not provided'
    ]);
}
?>