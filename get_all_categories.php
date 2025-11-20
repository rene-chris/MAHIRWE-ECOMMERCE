<?php
require_once 'config.php';

header('Content-Type: application/json');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
$query = "SELECT * FROM categories ORDER BY name";
if ($limit) {
    $query .= " LIMIT $limit";
}

$categories = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
echo json_encode($categories);
?>