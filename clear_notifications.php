<?php
session_start();
require_once 'config.php';

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'You must be logged in to clear notifications';
    echo json_encode($response);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['message'] = 'Failed to clear notifications';
}

$stmt->close();
echo json_encode($response);
$conn->close();
?>