<?php
session_start();
require_once 'config.php';

$cart = [];
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $product = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = {$item['id']}")->fetch_assoc();
        if ($product) {
            $cart[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $item['quantity'],
                'category' => $product['category_name'],
                'rating' => $product['rating'],
                'description' => $product['description'],
                'features' => explode(',', $product['features'])
            ];
        }
    }
}

echo json_encode($cart);
?>