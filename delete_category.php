<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        // Check if category has associated products
        $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $product_count = $stmt->get_result()->fetch_row()[0];
        $stmt->close();

        if ($product_count > 0) {
            echo "<script>alert('Cannot delete category with associated products.'); window.location='admin.php';</script>";
            exit;
        }

        // Delete category
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header('Location: admin.php');
        } else {
            echo "<script>alert('Error deleting category.'); window.location='admin.php';</script>";
        }
        $stmt->close();
    } catch (Exception $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location='admin.php';</script>";
    }
} else {
    header('Location: admin.php');
}
?>