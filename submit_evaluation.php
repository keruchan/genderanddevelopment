<?php
session_start();
require 'connecting/connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['evaluation_event_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: event_list.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = $_SESSION['evaluation_event_id'];

$required_fields = [
    'organization_1', 'organization_2', 'organization_3',
    'materials_1', 'materials_2',
    'speaker_1', 'speaker_2', 'speaker_3', 'speaker_4', 'speaker_5',
    'overall_1', 'overall_2'
];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field])) {
        echo "<script>alert('Please complete all evaluation items.'); window.history.back();</script>";
        exit();
    }
}

$comments = trim($_POST['comments'] ?? '');

$stmt = $conn->prepare("INSERT INTO event_evaluations (
    user_id, event_id,
    organization_1, organization_2, organization_3,
    materials_1, materials_2,
    speaker_1, speaker_2, speaker_3, speaker_4, speaker_5,
    overall_1, overall_2,
    comments
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo "<script>alert('Database error.'); window.history.back();</script>";
    exit();
}

$stmt->bind_param(
    "iiiiiiiiiiiiiss",
    $user_id,
    $event_id,
    $_POST['organization_1'], $_POST['organization_2'], $_POST['organization_3'],
    $_POST['materials_1'], $_POST['materials_2'],
    $_POST['speaker_1'], $_POST['speaker_2'], $_POST['speaker_3'],
    $_POST['speaker_4'], $_POST['speaker_5'],
    $_POST['overall_1'], $_POST['overall_2'],
    $comments
);

if ($stmt->execute()) {
    // INSERT INTO certificates after successful evaluation
    $cert_path = "certificates/sample_{$user_id}_event{$event_id}.docx";
    $insertCert = $conn->prepare("INSERT INTO certificates (user_id, event_id, certificate_path, issued_at) VALUES (?, ?, ?, NOW())");
    $insertCert->bind_param("iis", $user_id, $event_id, $cert_path);
    $insertCert->execute();
    $insertCert->close();

    echo "<script>alert('Evaluation submitted successfully!'); window.location.href = 'event_list.php';</script>";
} else {
    echo "<script>alert('Submission failed. You may have already evaluated this event.'); window.location.href = 'event_list.php';</script>";
}

$stmt->close();
$conn->close();
?>
