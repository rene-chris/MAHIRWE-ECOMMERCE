<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

try {
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $limit = 10; // Number of categories to fetch per request

    $stmt = $conn->prepare("SELECT * FROM categories ORDER BY name LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($categories);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>