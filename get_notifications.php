<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['unread' => 0, 'notifications' => []]);
    exit;
}
$user_id = $_SESSION['user_id'];
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$unread = $conn->query("SELECT COUNT(*) FROM notifications WHERE user_id = $user_id AND is_read = 0")->fetch_row()[0];
echo json_encode(['unread' => $unread, 'notifications' => $notifications]);
?>
