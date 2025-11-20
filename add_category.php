<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $icon_class = $conn->real_escape_string($_POST['icon_class']);
    
    $stmt = $conn->prepare("INSERT INTO categories (name, icon_class) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $icon_class);
    
    if ($stmt->execute()) {
        header('Location: admin.php');
    } else {
        echo "Error adding category";
    }
}
?>