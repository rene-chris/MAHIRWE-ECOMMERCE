<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $price_before = isset($_POST['price_before']) ? floatval($_POST['price_before']) : null;
    $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : 0;
    $features = $conn->real_escape_string($_POST['features']);
    
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_path = $target_dir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }
    
    $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, price_before, image, rating, features) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issddssd", $category_id, $name, $description, $price, $price_before, $image_path, $rating, $features);
    
    if ($stmt->execute()) {
        header('Location: admin.php');
    } else {
        echo "Error adding product";
    }
}
?>