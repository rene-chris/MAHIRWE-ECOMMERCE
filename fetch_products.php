<?php
require_once 'config.php';
header('Content-Type: application/json');

$productsPerPage = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $productsPerPage;
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : 'all';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$where_clauses = [];
if ($category !== 'all') {
    $where_clauses[] = "c.name = '$category'";
}
if ($search) {
    $where_clauses[] = "(p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}
$where = !empty($where_clauses) ? " WHERE " . implode(' AND ', $where_clauses) : '';

$products_query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $where LIMIT $productsPerPage OFFSET $offset";
$total_query = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id $where";

$products = $conn->query($products_query)->fetch_all(MYSQLI_ASSOC);
$total_products = $conn->query($total_query)->fetch_row()[0];
$total_pages = ceil($total_products / $productsPerPage);

echo json_encode([
    'products' => $products,
    'total_products' => $total_products,
    'total_pages' => $total_pages
]);
?>