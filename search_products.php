<?php
require_once 'config.php';

$term = $conn->real_escape_string($_GET['term']);
$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.name LIKE '%$term%' OR p.description LIKE '%$term%' OR c.name LIKE '%$term%' OR p.features LIKE '%$term%'")
    ->fetch_all(MYSQLI_ASSOC);

echo json_encode($products);
?><?php
require_once 'config.php';

$term = $conn->real_escape_string($_GET['term']);
$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.name LIKE '%$term%' OR p.description LIKE '%$term%' OR c.name LIKE '%$term%' OR p.features LIKE '%$term%'")
    ->fetch_all(MYSQLI_ASSOC);

echo json_encode($products);
?>