<?php
session_start();
require_once 'config.php';

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'You must be logged in to delete notifications';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notification_id = (int)$_POST['notification_id'];
    $user_id = (int)$_SESSION['user_id'];

    // Ensure the notification belongs to the user
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['message'] = 'Failed to delete notification';
    }

    $stmt->close();
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
$conn->close();
?>