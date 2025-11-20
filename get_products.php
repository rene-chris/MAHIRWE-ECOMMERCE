<?php
header('Content-Type: application/json');
require_once 'config.php';

$productsPerPage = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$where_clauses = [];
$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id";

if (isset($_GET['category']) && $_GET['category'] != 'all') {
    $category = $conn->real_escape_string($_GET['category']);
    $where_clauses[] = "c.name = '$category'";
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where_clauses[] = "(p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}
$query .= " LIMIT $productsPerPage OFFSET $offset";

$result = $conn->query($query);
$products = $result->fetch_all(MYSQLI_ASSOC);
$total = $conn->query("SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id" . (!empty($where_clauses) ? " WHERE " . implode(' AND ', $where_clauses) : ""))->fetch_row()[0];

echo json_encode(['products' => $products, 'total' => $total]);
$conn->close();
?>