<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $result = $conn->query("SELECT o.*, GROUP_CONCAT(CONCAT(oi.product_name, ' x ', oi.quantity)) as items 
                            FROM orders o 
                            LEFT JOIN order_items oi ON o.id = oi.order_id 
                            WHERE o.status != 'cancelled' 
                            GROUP BY o.id 
                            ORDER BY o.created_at DESC");
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($orders);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch orders']);
}
?>