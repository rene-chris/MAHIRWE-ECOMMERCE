<?php
require_once 'config.php';

header('Content-Type: application/json');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
$query = "SELECT * FROM subscribers ORDER BY created_at DESC";
if ($limit) {
    $query .= " LIMIT $limit";
}

$subscribers = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
echo json_encode($subscribers);
?>