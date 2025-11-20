<?php
require_once 'config.php';
header('Content-Type: application/json');

$where = [];
if (isset($_GET['category']) && $_GET['category'] != 'all') $where[] = "c.name = '" . $conn->real_escape_string($_GET['category']) . "'";
if (isset($_GET['search'])) $where[] = "(p.name LIKE '%" . $conn->real_escape_string($_GET['search']) . "%' OR p.description LIKE '%" . $conn->real_escape_string($_GET['search']) . "%')";
$whereSql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Your productsPerPage
$offset = ($page - 1) * $limit;

$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereSql LIMIT $limit OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
echo json_encode($products);
?>