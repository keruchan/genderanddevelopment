<?php
require 'connecting/connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $query = $conn->prepare("SELECT * FROM admin_archived_events WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();
    
    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        echo json_encode($event);
    } else {
        echo json_encode(['error' => 'Event not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>