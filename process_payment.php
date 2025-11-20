<?php
session_start();
require_once 'config.php';

$response = ['success' => false, 'message' => ''];

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to process payment.';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$total_amount = isset($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
$transaction_id = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : '';
$cart = isset($_POST['cart']) ? json_decode($_POST['cart'], true) : [];

// Validate inputs
if (empty($transaction_id) || $total_amount <= 0 || empty($payment_method) || empty($cart)) {
    $response['message'] = 'Invalid payment data provided.';
    echo json_encode($response);
    exit;
}

// Prepare products JSON for the orders table
$products_json = json_encode($cart);

try {
    // Start transaction
    $conn->begin_transaction();

    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, products, total_amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("isdss", $user_id, $products_json, $total_amount, $payment_method, $transaction_id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to save order: ' . $conn->error);
    }
    $order_id = $conn->insert_id;

    // Insert order items
    $stmt_items = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
        $product_name = $item['name'];
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        $stmt_items->bind_param("isid", $order_id, $product_name, $quantity, $price);
        if (!$stmt_items->execute()) {
            throw new Exception('Failed to save order items: ' . $conn->error);
        }
    }

    // Handle screenshot upload (if provided)
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $screenshot_path = $upload_dir . $order_id . '_' . basename($_FILES['screenshot']['name']);
        if (!move_uploaded_file($_FILES['screenshot']['tmp_name'], $screenshot_path)) {
            throw new Exception('Failed to upload screenshot.');
        }
        // Update order with screenshot path
        $stmt_update = $conn->prepare("UPDATE orders SET screenshot_path = ? WHERE id = ?");
        $stmt_update->bind_param("si", $screenshot_path, $order_id);
        if (!$stmt_update->execute()) {
            throw new Exception('Failed to update screenshot path: ' . $conn->error);
        }
    }

    // Clear cart
    unset($_SESSION['cart']);

    // Commit transaction
    $conn->commit();
    $response['success'] = true;

} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("Payment processing error: " . $e->getMessage());
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($stmt_items)) $stmt_items->close();
    if (isset($stmt_update)) $stmt_update->close();
}

echo json_encode($response);
?>