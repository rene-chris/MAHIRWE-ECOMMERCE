<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$notification_message = '';
$notification_type = 'success';
$errors = [];

// Database interaction functions
function validateInput($data, $fieldName) {
    $data = trim($data);
    if (empty($data)) {
        return "$fieldName is required";
    }
    return '';
}

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    try {
        if ($_POST['action'] === 'add_category') {
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $icon_class = filter_input(INPUT_POST, 'icon_class', FILTER_SANITIZE_STRING);
            
            if ($error = validateInput($name, 'Category Name')) {
                throw new Exception($error);
            }
            if ($error = validateInput($icon_class, 'Icon Class')) {
                throw new Exception($error);
            }

            $stmt = $conn->prepare("INSERT INTO categories (name, icon_class) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $icon_class);
            $stmt->execute();
            $notification_message = 'Category added successfully!';
        } elseif ($_POST['action'] === 'edit_category') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $icon_class = filter_input(INPUT_POST, 'icon_class', FILTER_SANITIZE_STRING);

            if (!$id) throw new Exception('Invalid category ID');
            if ($error = validateInput($name, 'Category Name')) throw new Exception($error);
            if ($error = validateInput($icon_class, 'Icon Class')) throw new Exception($error);

            $stmt = $conn->prepare("UPDATE categories SET name = ?, icon_class = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $icon_class, $id);
            $stmt->execute();
            $notification_message = 'Category updated successfully!';
        } elseif ($_POST['action'] === 'delete_category') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('Invalid category ID');

            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $notification_message = 'Category deleted successfully!';
        } elseif ($_POST['action'] === 'add_product') {
            $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
            $price_before = filter_input(INPUT_POST, 'price_before', FILTER_VALIDATE_FLOAT, ['options' => ['default' => null]]);
            $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT, ['options' => ['default' => null]]);
            $features = filter_input(INPUT_POST, 'features', FILTER_SANITIZE_STRING);
            $is_trending = isset($_POST['is_trending']) ? 1 : 0;
            $image_path = '';

            if (!$category_id) throw new Exception('Invalid category ID');
            if ($error = validateInput($name, 'Product Name')) throw new Exception($error);
            if ($error = validateInput($description, 'Description')) throw new Exception($error);
            if (!$price) throw new Exception('Invalid price');
            if ($rating && ($rating < 0 || $rating > 5)) throw new Exception('Rating must be between 0 and 5');

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'Uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $image_path = $upload_dir . basename($_FILES['image']['name']);
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    throw new Exception('Failed to upload image');
                }
            }

            $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, price_before, image, rating, features, is_trending) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssdssdi", $category_id, $name, $description, $price, $price_before, $image_path, $rating, $features, $is_trending);
            $stmt->execute();
            $notification_message = 'Product added successfully!';
        } elseif ($_POST['action'] === 'edit_product') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
            $price_before = filter_input(INPUT_POST, 'price_before', FILTER_VALIDATE_FLOAT, ['options' => ['default' => null]]);
            $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT, ['options' => ['default' => null]]);
            $features = filter_input(INPUT_POST, 'features', FILTER_SANITIZE_STRING);
            $is_trending = isset($_POST['is_trending']) ? 1 : 0;
            $image_path = filter_input(INPUT_POST, 'existing_image', FILTER_SANITIZE_STRING);

            if (!$id) throw new Exception('Invalid product ID');
            if (!$category_id) throw new Exception('Invalid category ID');
            if ($error = validateInput($name, 'Product Name')) throw new Exception($error);
            if ($error = validateInput($description, 'Description')) throw new Exception($error);
            if (!$price) throw new Exception('Invalid price');
            if ($rating && ($rating < 0 || $rating > 5)) throw new Exception('Rating must be between 0 and 5');

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'Uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $image_path = $upload_dir . basename($_FILES['image']['name']);
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    throw new Exception('Failed to upload image');
                }
            }

            $stmt = $conn->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, price_before = ?, image = ?, rating = ?, features = ?, is_trending = ? WHERE id = ?");
            $stmt->bind_param("isssdssdi", $category_id, $name, $description, $price, $price_before, $image_path, $rating, $features, $is_trending, $id);
            $stmt->execute();
            $notification_message = 'Product updated successfully!';
        } elseif ($_POST['action'] === 'delete_product') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('Invalid product ID');

            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $notification_message = 'Product deleted successfully!';
        } elseif ($_POST['action'] === 'delete_subscriber') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('Invalid subscriber ID');

            $stmt = $conn->prepare("DELETE FROM subscribers WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $notification_message = 'Subscriber deleted successfully!';
        } elseif ($_POST['action'] === 'clear_subscribers') {
            $conn->query("DELETE FROM subscribers");
            $notification_message = 'All subscribers cleared!';
        } elseif ($_POST['action'] === 'delete_order') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('Invalid order ID');

            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();

                $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $conn->commit();
                $notification_message = 'Order deleted successfully!';
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        } elseif ($_POST['action'] === 'delete_message') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) throw new Exception('Invalid message ID');

            $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $notification_message = 'Message deleted successfully!';
        } elseif ($_POST['action'] === 'update_order_status') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);

            if (!$id) throw new Exception('Invalid order ID');
            if (!in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
                throw new Exception('Invalid status');
            }

            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->bind_param("si", $status, $id);
                $stmt->execute();

                $stmt = $conn->prepare("SELECT user_id FROM orders WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $order = $result->fetch_assoc();

                if ($order['user_id']) {
                    $message = "Your order #$id has been updated to $status.";
                    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $stmt->bind_param("is", $order['user_id'], $message);
                    $stmt->execute();
                }
                $conn->commit();
                $notification_message = 'Order status updated successfully!';
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
        } elseif ($_POST['action'] === 'export_subscribers') {
            $subscribers = $conn->query("SELECT email, created_at FROM subscribers ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename=subscribers.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Email', 'Subscribed At']);
            foreach ($subscribers as $subscriber) {
                fputcsv($output, [$subscriber['email'], $subscriber['created_at']]);
            }
            fclose($output);
            exit;
        }
    } catch (Exception $e) {
        $notification_message = $e->getMessage();
        $notification_type = 'error';
    }
}

// Fetch data
$categories = $conn->query("SELECT * FROM categories ORDER BY name LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$total_categories = $conn->query("SELECT COUNT(*) FROM categories")->fetch_row()[0];
$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$total_products = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$total_price = $conn->query("SELECT SUM(price * 1) as total FROM products")->fetch_all(MYSQLI_ASSOC)[0]['total'];
$subscribers = $conn->query("SELECT * FROM subscribers ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$total_subscribers = $conn->query("SELECT COUNT(*) FROM subscribers")->fetch_row()[0];
$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$total_messages = $conn->query("SELECT COUNT(*) FROM contact_messages")->fetch_row()[0];
$orders = $conn->query("SELECT o.*, GROUP_CONCAT(CONCAT(oi.product_name, ' x ', oi.quantity)) as items FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id WHERE o.status != 'cancelled' GROUP BY o.id ORDER BY o.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$total_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE status != 'cancelled'")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MAHIRWE SMART BUSINESS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold-start: #FFD700;
            --gold-end: #B8860B;
            --bg-light: #F9FAFB;
            --bg-dark: #111827;
            --card-light: #FFFFFF;
            --card-dark: #1F2937;
            --dark-text-base: #ffffffff;
            --light-text-base: #080808ff;
            --border-light: #E5E7EB;
            --border-dark: #4B5563;
            --button-bg: #2563EB;
            --button-hover: #1E40AF;
            --danger-bg: #EF4444;
            --danger-hover: #B91C1C;
            --success-bg: #22C55E;
            --success-hover: #15803D;
            --warning-bg: #F59E0B;
            --warning-hover: #D97706;
            --email-bg: #8B5CF6;
            --email-hover: #6D28D9;
            --error-bg: #DC2626;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: var(--dark-text-base);
            transition: background-color 0.3s, color 0.3s;
            margin: 0;
            font-size: 14px;
        }

        .light {
            background-color: var(--bg-light);
            color: var(--light-text-base);
        }

        .gradient-gold {
            background: linear-gradient(to right, var(--gold-start), var(--gold-end));
        }

        .gradient-text {
            background: linear-gradient(to right, var(--gold-start), var(--gold-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .animate-slide-in {
            animation: slideIn 0.5s ease-out;
        }

        .animate-glow {
            animation: glow 2s ease-in-out infinite alternate;
        }

        .animate-row {
            animation: fadeInRow 0.5s ease-out forwards;
        }

        .animate-pop {
            animation: pop 0.3s ease-out;
        }

        @keyframes slideIn {
            0% { transform: translateX(20px); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
        }

        @keyframes glow {
            from { box-shadow: 0 0 5px rgba(255,215,0,0.3); }
            to { box-shadow: 0 0 10px rgba(255,215,0,0.5); }
        }

        @keyframes fadeInRow {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes pop {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        tr {
            animation-delay: calc(var(--row-index) * 0.1s);
        }

        header {
            background-color: var(--card-dark);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 0.5rem 1rem;
            color: var(--dark-text-base);
        }

        .light header {
            background-color: var(--card-light);
            color: var(--light-text-base);
        }

        .dark-mode-toggle {
            background: linear-gradient(135deg, var(--border-dark), #D1D5DB);
            color: var(--dark-text-base);
            border-radius: 9999px;
            padding: 0.5rem;
            transition: all 0.3s ease;
        }

        .light .dark-mode-toggle {
            background: linear-gradient(135deg, #E5E7EB, #D1D5DB);
            color: var(--light-text-base);
        }

        .dark-mode-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 0 10px rgba(255,215,0,0.4);
        }

        .card {
            background-color: var(--card-dark);
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--dark-text-base);
        }

        .light .card {
            background-color: var(--card-light);
            color: var(--light-text-base);
        }

        .card:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        input, select, textarea {
            border: 1px solid var(--border-dark);
            border-radius: 0.5rem;
            padding: 0.5rem;
            transition: all 0.3s ease;
            width: 100%;
            box-sizing: border-box;
            font-size: 13px;
            background-color: #2D3748;
            color: var(--dark-text-base);
        }

        .light input, .light select, .light textarea {
            border-color: var(--border-light);
            background-color: #F9FAFB;
            color: var(--light-text-base);
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--gold-start);
            box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.3);
        }

        input.invalid, select.invalid, textarea.invalid {
            border-color: var(--error-bg);
            background-color: #FFF1F2;
        }

        .error-message {
            color: var(--error-bg);
            font-size: 12px;
            margin-top: 0.25rem;
        }

        button, a.button {
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            color: white;
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold-start), var(--gold-end));
        }

        .btn-gold:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(255,215,0,0.4);
        }

        .btn-blue {
            background: linear-gradient(135deg, var(--button-bg), var(--button-hover));
        }

        .btn-blue:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(37,99,235,0.4);
        }

        .btn-red {
            background: linear-gradient(135deg, var(--danger-bg), var(--danger-hover));
        }

        .btn-red:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(239,68,68,0.4);
        }

        .btn-green {
            background: linear-gradient(135deg, var(--success-bg), var(--success-hover));
        }

        .btn-green:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(34,197,94,0.4);
        }

        .btn-email {
            background: linear-gradient(135deg, var(--email-bg), var(--email-hover));
        }

        .btn-email:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(139,92,246,0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-bg), var(--warning-hover));
        }

        .btn-warning:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(245,158,11,0.4);
        }

        .btn-loading::after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
            margin-left: 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .view-more-btn {
            background: linear-gradient(135deg, var(--button-bg), var(--button-hover));
            color: white;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 13px;
            margin-top: 0.5rem;
        }

        .view-more-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(37,99,235,0.4);
        }

        .view-more-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
        }

        .view-more-btn:hover::after {
            width: 150%;
            height: 150%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-dark);
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .light table {
            background: var(--card-light);
        }

        th, td {
            padding: 0.5rem;
            text-align: left;
            font-size: 13px;
            color: var(--dark-text-base);
        }

        .light th, .light td {
            color: var(--light-text-base);
        }

        th {
            background: linear-gradient(to right, var(--gold-start), var(--gold-end));
            color: white;
        }

        tr {
            border-bottom: 1px solid var(--border-dark);
        }

        .light tr {
            border-bottom: 1px solid var(--border-light);
        }

        tr:hover {
            background-color: #374151;
        }

        .light tr:hover {
            background-color: #F3F4F6;
        }

        footer {
            background-color: #000000;
            color: var(--dark-text-base);
            padding: 1.5rem 0;
            font-size: 12px;
        }

        .light footer {
            color: var(--dark-text-base);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: var(--card-dark);
            border-radius: 0.75rem;
            padding: 1.5rem;
            max-width: 500px;
            width: 90%;
            animation: pop 0.3s ease-out;
            position: relative;
            color: var(--dark-text-base);
        }

        .light .modal-content {
            background: var(--card-light);
            color: var(--light-text-base);
        }

        .modal-close {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            font-size: 1.2rem;
            cursor: pointer;
            color: var(--dark-text-base);
        }

        .light .modal-close {
            color: var(--light-text-base);
        }

        .modal-close:hover {
            color: var(--gold-start);
        }

        .main-container {
            display: flex;
            min-height: calc(100vh - 56px);
        }

        .sidebar {
            width: 200px;
            background-color: var(--card-dark);
            padding: 0.75rem;
            position: sticky;
            top: 56px;
            height: calc(100vh - 56px);
            overflow-y: auto;
            transition: left 0.3s ease;
            z-index: 40;
        }

        .light .sidebar {
            background-color: var(--card-light);
        }

        @media (max-width: 767px) {
            .sidebar {
                position: fixed;
                left: -200px;
                top: 56px;
            }

            .sidebar.open {
                left: 0;
            }
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar li {
            margin-bottom: 0.25rem;
        }

        .sidebar-link {
            display: block;
            padding: 0.5rem 0.75rem;
            color: var(--dark-text-base);
            text-decoration: none;
            border-radius: 0.375rem;
            font-size: 13px;
            transition: background-color 0.3s;
        }

        .light .sidebar-link {
            color: var(--light-text-base);
        }

        .sidebar-link:hover {
            background-color: #374151;
        }

        .light .sidebar-link:hover {
            background-color: #F3F4F6;
        }

        .content {
            flex: 1;
            padding: 1.5rem 0.75rem;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.5rem;
        }

        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .flex-center {
            display: flex;
            align-items: center;
        }

        .space-x-4 > * + * {
            margin-left: 1rem;
        }

        .logo-container {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .text-xl {
            font-size: 1.25rem;
        }

        .text-base {
            font-size: 1rem;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .font-bold {
            font-weight: bold;
        }

        .font-semibold {
            font-weight: 600;
        }

        .notification {
            position: fixed;
            top: 0.5rem;
            right: 0.5rem;
            z-index: 50;
            display: none;
        }

        .notification-inner {
            display: flex;
            align-items: center;
            background-color: var(--success-bg);
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 13px;
        }

        .notification-error {
            background-color: var(--error-bg);
        }

        .mr-2 {
            margin-right: 0.5rem;
        }

        .mr-1 {
            margin-right: 0.25rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }

        .stats-card {
            padding: 1rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 12px;
            margin-bottom: 0.25rem;
            color: var(--dark-text-base);
        }

        .light .form-group label {
            color: var(--light-text-base);
        }

        .col-span-2 {
            grid-column: span 2 / span 2;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .border-b {
            border-bottom: 1px solid var(--border-dark);
        }

        .light .border-b {
            border-bottom: 1px solid var(--border-light);
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .mt-4 {
            margin-top: 1rem;
        }

        .mb-8 {
            margin-bottom: 2rem;
        }

        .inline {
            display: inline;
        }

        .list-disc {
            list-style-type: disc;
        }

        .pl-4 {
            padding-left: 1rem;
        }

        .whitespace-pre-wrap {
            white-space: pre-wrap;
            word-break: break-word;
        }

        .sidebar-toggle {
            display: none;
            padding: 0.5rem;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--dark-text-base);
        }

        .light .sidebar-toggle {
            color: var(--light-text-base);
        }

        @media (max-width: 767px) {
            .sidebar-toggle {
                display: block;
            }
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .icon-mr {
            margin-right: 0.25rem;
        }

        .actions-flex {
            display: flex;
            gap: 0.25rem;
        }

        .search-container {
            margin-bottom: 1rem;
        }

        .search-container input {
            max-width: 300px;
        }
    </style>
</head>
<body class="transition-colors duration-300">
    <!-- Notification System -->
    <div id="notification" class="notification animate-slide-in">
        <div class="notification-inner">
            <i id="notificationIcon" class="fas fa-check-circle mr-2"></i>
            <span id="notificationText"><?php echo htmlspecialchars($notification_message); ?></span>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="flex-between">
                <div class="flex-center space-x-4">
                    <button id="sidebarToggle" class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="logo-container gradient-gold animate-glow">
                        <i class="fas fa-bolt text-white text-base"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold gradient-text">MAHIRWE</h1>
                        <p class="text-sm font-semibold">SMART BUSINESS ADMIN</p>
                    </div>
                </div>
                <div class="flex-center space-x-4">
                    <button id="darkModeToggle" class="dark-mode-toggle animate-pop">
                        <i class="fas fa-moon light:hidden"></i>
                        <i class="fas fa-sun hidden light:block"></i>
                    </button>
                    <form action="admin_logout.php" method="POST" class="inline">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <button type="submit" class="btn-red animate-pop">
                            <i class="fas fa-sign-out-alt mr-1"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content with Sidebar -->
    <div class="main-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <ul>
                <li><a href="#dashboard-stats" class="sidebar-link"><i class="fas fa-chart-bar icon-mr"></i> Dashboard Stats</a></li>
                <li><a href="#add-category" class="sidebar-link"><i class="fas fa-plus-circle icon-mr"></i> Add Category</a></li>
                <li><a href="#categories-list" class="sidebar-link"><i class="fas fa-list-ul icon-mr"></i> Categories List</a></li>
                <li><a href="#add-product" class="sidebar-link"><i class="fas fa-box-open icon-mr"></i> Add Product</a></li>
                <li><a href="#products-list" class="sidebar-link"><i class="fas fa-boxes icon-mr"></i> Products List</a></li>
                <li><a href="#orders-list" class="sidebar-link"><i class="fas fa-shopping-cart icon-mr"></i> Orders</a></li>
                <li><a href="#subscribers" class="sidebar-link"><i class="fas fa-users icon-mr"></i> Subscribers</a></li>
                <li><a href="#messages" class="sidebar-link"><i class="fas fa-envelope icon-mr"></i> Messages</a></li>
            </ul>
        </aside>

        <!-- Content -->
        <div class="content">
            <!-- Dashboard Stats -->
            <div id="dashboard-stats" class="stats-grid">
                <div class="card stats-card animate-slide-in">
                    <h3 class="text-base font-semibold"><i class="fas fa-th-large icon-mr"></i>Total Categories</h3>
                    <p class="text-xl font-bold gradient-text"><?php echo $total_categories; ?></p>
                </div>
                <div class="card stats-card animate-slide-in" style="animation-delay: 0.2s;">
                    <h3 class="text-base font-semibold"><i class="fas fa-box-open icon-mr"></i>Total Products</h3>
                    <p class="text-xl font-bold gradient-text"><?php echo $total_products; ?></p>
                </div>
                <div class="card stats-card animate-slide-in" style="animation-delay: 0.4s;">
                    <h3 class="text-base font-semibold"><i class="fas fa-wallet icon-mr"></i>Total Inventory</h3>
                    <p class="text-xl font-bold gradient-text">RWF <?php echo number_format($total_price, 2); ?></p>
                </div>
                <div class="card stats-card animate-slide-in" style="animation-delay: 0.6s;">
                    <h3 class="text-base font-semibold"><i class="fas fa-users icon-mr"></i>Total Subscribers</h3>
                    <p class="text-xl font-bold gradient-text"><?php echo $total_subscribers; ?></p>
                </div>
                <div class="card stats-card animate-slide-in" style="animation-delay: 0.8s;">
                    <h3 class="text-base font-semibold"><i class="fas fa-shopping-cart icon-mr"></i>Total Orders</h3>
                    <p class="text-xl font-bold gradient-text"><?php echo $total_orders; ?></p>
                </div>
            </div>

            <!-- Add Category -->
            <div id="add-category" class="card animate-slide-in">
                <h2 class="section-title gradient-text"><i class="fas fa-th-large icon-mr"></i>Add Category</h2>
                <form id="addCategoryForm" method="POST" class="form-grid">
                    <input type="hidden" name="action" value="add_category">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="form-group">
                        <label for="categoryName">Category Name</label>
                        <input type="text" id="categoryName" name="name" placeholder="Category Name" required>
                        <span class="error-message" id="categoryNameError"></span>
                    </div>
                    <div class="form-group">
                        <label for="iconClass">Font Awesome Icon Class</label>
                        <input type="text" id="iconClass" name="icon_class" placeholder="e.g., fas fa-snowflake" required>
                        <span class="error-message" id="iconClassError"></span>
                    </div>
                    <button type="submit" class="btn-gold col-span-2 animate-glow">
                        <i class="fas fa-plus icon-mr"></i>Add Category
                    </button>
                </form>
            </div>

            <!-- Edit Category Modal -->
            <div id="editCategoryModal" class="modal">
                <div class="modal-content">
                    <span class="modal-close"><i class="fas fa-times"></i></span>
                    <h2 class="section-title gradient-text"><i class="fas fa-edit icon-mr"></i>Edit Category</h2>
                    <form id="editCategoryForm" method="POST" class="form-grid">
                        <input type="hidden" name="action" value="edit_category">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="id" id="editCategoryId">
                        <div class="form-group">
                            <label for="editCategoryName">Category Name</label>
                            <input type="text" name="name" id="editCategoryName" placeholder="Category Name" required>
                            <span class="error-message" id="editCategoryNameError"></span>
                        </div>
                        <div class="form-group">
                            <label for="editCategoryIconClass">Font Awesome Icon Class</label>
                            <input type="text" name="icon_class" id="editCategoryIconClass" placeholder="e.g., fas fa-snowflake" required>
                            <span class="error-message" id="editCategoryIconClassError"></span>
                        </div>
                        <button type="submit" class="btn-gold col-span-2 animate-glow">
                            <i class="fas fa-save icon-mr"></i>Update Category
                        </button>
                    </form>
                </div>
            </div>

            <!-- Categories List -->
            <div id="categories-list" class="card animate-slide-in">
                <h2 class="section-title gradient-text"><i class="fas fa-th-large icon-mr"></i>Categories</h2>
                <div class="search-container">
                    <input type="text" id="categorySearch" placeholder="Search categories..." class="search-input">
                </div>
                <div class="table-responsive">
                    <table id="categoriesTable">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2 text-left"><i class="fas fa-tag icon-mr"></i>Name</th>
                                <th class="py-2 text-left"><i class="fas fa-icons icon-mr"></i>Icon Class</th>
                                <th class="py-2 text-left"><i class="fas fa-cogs icon-mr"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesBody">
                            <?php foreach ($categories as $index => $category): ?>
                                <tr class="border-b animate-row" style="--row-index: <?php echo $index; ?>;" data-name="<?php echo htmlspecialchars($category['name']); ?>" data-icon="<?php echo htmlspecialchars($category['icon_class']); ?>">
                                    <td class="py-2"><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td class="py-2"><?php echo htmlspecialchars($category['icon_class']); ?></td>
                                    <td class="py-2 actions-flex">
                                        <button onclick="openEditCategoryModal(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', '<?php echo htmlspecialchars($category['icon_class']); ?>')" class="btn-blue"><i class="fas fa-edit mr-1"></i>Edit</button>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="delete_category">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                            <button type="submit" class="btn-red" onclick="return confirm('Are you sure you want to delete this category?')"><i class="fas fa-trash mr-1"></i>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-4">
                    <?php if ($total_categories > 5): ?>
                        <button id="viewMoreCategories" class="view-more-btn"><i class="fas fa-chevron-down icon-mr"></i>View More</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add Product -->
            <div id="add-product" class="card animate-slide-in">
                <h2 class="section-title gradient-text"><i class="fas fa-box-open icon-mr"></i>Add Product</h2>
                <form id="addProductForm" method="POST" enctype="multipart/form-data" class="form-grid">
                    <input type="hidden" name="action" value="add_product">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="form-group">
                        <label for="productCategory">Category</label>
                        <select id="productCategory" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="error-message" id="productCategoryError"></span>
                    </div>
                    <div class="form-group">
                        <label for="productName">Product Name</label>
                        <input type="text" id="productName" name="name" placeholder="Product Name" required>
                        <span class="error-message" id="productNameError"></span>
                    </div>
                    <div class="form-group col-span-2">
                        <label for="productDescription">Description</label>
                        <textarea id="productDescription" name="description" placeholder="Description" style="height: 80px;"></textarea>
                        <span class="error-message" id="productDescriptionError"></span>
                    </div>
                    <div class="form-group">
                        <label for="productPrice">Price (RWF)</label>
                        <input type="number" id="productPrice" name="price" placeholder="Price (RWF)" step="0.01" required>
                        <span class="error-message" id="productPriceError"></span>
                    </div>
                    <div class="form-group">
                        <label for="productPriceBefore">Price Before (RWF)</label>
                        <input type="number" id="productPriceBefore" name="price_before" placeholder="Price Before (RWF)" step="0.01">
                        <span class="error-message" id="productPriceBeforeError"></span>
                    </div>
                    <div class="form-group">
                        <label for="productImage">Image</label>
                        <input type="file" id="productImage" name="image" accept="image/*">
                        <span class="error-message" id="productImageError"></span>
                    </div>
                    <div class="form-group">
                        <label for="productRating">Rating (0-5)</label>
                        <input type="number" id="productRating" name="rating" placeholder="Rating (0-5)" step="0.1" min="0" max="5">
                        <span class="error-message" id="productRatingError"></span>
                    </div>
                    <div class="form-group">
                        <label for="productIsTrending">Trending</label>
                        <input type="checkbox" id="productIsTrending" name="is_trending">
                        <span class="error-message" id="productIsTrendingError"></span>
                    </div>
                    <div class="form-group col-span-2">
                        <label for="productFeatures">Features (comma-separated)</label>
                        <input type="text" id="productFeatures" name="features" placeholder="Features (comma-separated)">
                        <span class="error-message" id="productFeaturesError"></span>
                    </div>
                    <button type="submit" class="btn-gold col-span-2 animate-glow">
                        <i class="fas fa-plus icon-mr"></i>Add Product
                    </button>
                </form>
            </div>

            <!-- Edit Product Modal -->
            <div id="editProductModal" class="modal">
                <div class="modal-content">
                    <span class="modal-close"><i class="fas fa-times"></i></span>
                    <h2 class="section-title gradient-text"><i class="fas fa-edit icon-mr"></i>Edit Product</h2>
                    <form id="editProductForm" method="POST" enctype="multipart/form-data" class="form-grid">
                        <input type="hidden" name="action" value="edit_product">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="id" id="editProductId">
                        <input type="hidden" name="existing_image" id="editProductImage">
                        <div class="form-group">
                            <label for="editProductCategoryId">Category</label>
                            <select name="category_id" id="editProductCategoryId" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="error-message" id="editProductCategoryIdError"></span>
                        </div>
                        <div class="form-group">
                            <label for="editProductName">Product Name</label>
                            <input type="text" name="name" id="editProductName" placeholder="Product Name" required>
                            <span class="error-message" id="editProductNameError"></span>
                        </div>
                        <div class="form-group col-span-2">
                            <label for="editProductDescription">Description</label>
                            <textarea name="description" id="editProductDescription" placeholder="Description" style="height: 80px;"></textarea>
                            <span class="error-message" id="editProductDescriptionError"></span>
                        </div>
                        <div class="form-group">
                            <label for="editProductPrice">Price (RWF)</label>
                            <input type="number" name="price" id="editProductPrice" placeholder="Price (RWF)" step="0.01" required>
                            <span class="error-message" id="editProductPriceError"></span>
                        </div>
                        <div class="form-group">
                            <label for="editProductPriceBefore">Price Before (RWF)</label>
                            <input type="number" name="price_before" id="editProductPriceBefore" placeholder="Price Before (RWF)" step="0.01">
                            <span class="error-message" id="editProductPriceBeforeError"></span>
                        </div>
                        <div class="form-group">
                            <label for="editProductImage">Image</label>
                            <input type="file" name="image" id="editProductImageInput" accept="image/*">
                            <span class="error-message" id="editProductImageError"></span>
                        </div>
                        <div class="form-group">
                            <label for="editProductRating">Rating (0-5)</label>
                            <input type="number" name="rating" id="editProductRating" placeholder="Rating (0-5)" step="0.1" min="0" max="5">
                            <span class="error-message" id="editProductRatingError"></span>
                        </div>
                        <div class="form-group">
                            <label for="editProductIsTrending">Trending</label>
                            <input type="checkbox" name="is_trending" id="editProductIsTrending">
                            <span class="error-message" id="editProductIsTrendingError"></span>
                        </div>
                        <div class="form-group col-span-2">
                            <label for="editProductFeatures">Features (comma-separated)</label>
                            <input type="text" name="features" id="editProductFeatures" placeholder="Features (comma-separated)">
                            <span class="error-message" id="editProductFeaturesError"></span>
                        </div>
                        <button type="submit" class="btn-gold col-span-2 animate-glow">
                            <i class="fas fa-save icon-mr"></i>Update Product
                        </button>
                    </form>
                </div>
            </div>

            <!-- Products List -->
            <div id="products-list" class="card animate-slide-in">
                <h2 class="section-title gradient-text"><i class="fas fa-box-open icon-mr"></i>Products</h2>
                <div class="search-container">
                    <input type="text" id="productSearch" placeholder="Search products..." class="search-input">
                </div>
                <div class="table-responsive">
                    <table id="productsTable">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2 text-left"><i class="fas fa-box icon-mr"></i>Name</th>
                                <th class="py-2 text-left"><i class="fas fa-th-large icon-mr"></i>Category</th>
                                <th class="py-2 text-left"><i class="fas fa-coins icon-mr"></i>Price</th>
                                <th class="py-2 text-left"><i class="fas fa-star icon-mr"></i>Trending</th>
                                <th class="py-2 text-left"><i class="fas fa-cogs icon-mr"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productsBody">
                            <?php foreach ($products as $index => $product): ?>
                                <tr class="border-b animate-row" style="--row-index: <?php echo $index; ?>;" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-category="<?php echo htmlspecialchars($product['category_name']); ?>">
                                    <td class="py-2"><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td class="py-2"><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td class="py-2">RWF <?php echo number_format($product['price'], 2); ?></td>
                                    <td class="py-2"><?php echo $product['is_trending'] ? '<i class="fas fa-star text-yellow-400"></i>' : ''; ?></td>
                                    <td class="py-2 actions-flex">
                                        <button onclick="openEditProductModal(<?php echo $product['id']; ?>, <?php echo $product['category_id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', '<?php echo htmlspecialchars(addslashes($product['description'])); ?>', <?php echo $product['price']; ?>, <?php echo $product['price_before'] ?: 'null'; ?>, '<?php echo htmlspecialchars($product['image']); ?>', <?php echo $product['rating'] ?: 'null'; ?>, '<?php echo htmlspecialchars(addslashes($product['features'])); ?>', <?php echo $product['is_trending'] ? 'true' : 'false'; ?>)" class="btn-blue"><i class="fas fa-edit mr-1"></i>Edit</button>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="delete_product">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="btn-red" onclick="return confirm('Are you sure you want to delete this product?')"><i class="fas fa-trash mr-1"></i>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-4">
                    <?php if ($total_products > 5): ?>
                        <button id="viewMoreProducts" class="view-more-btn"><i class="fas fa-chevron-down icon-mr"></i>View More</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Orders List -->
            <div id="orders-list" class="card animate-slide-in">
                <h2 class="section-title gradient-text"><i class="fas fa-shopping-cart icon-mr"></i>Orders</h2>
                <div class="search-container">
                    <input type="text" id="orderSearch" placeholder="Search orders..." class="search-input">
                </div>
                <div class="table-responsive">
                    <table id="ordersTable">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2 text-left"><i class="fas fa-hashtag icon-mr"></i>Order ID</th>
                                <th class="py-2 text-left"><i class="fas fa-boxes icon-mr"></i>Products</th>
                                <th class="py-2 text-left"><i class="fas fa-coins icon-mr"></i>Total Amount</th>
                                <th class="py-2 text-left"><i class="fas fa-credit-card icon-mr"></i>Payment Method</th>
                                <th class="py-2 text-left"><i class="fas fa-receipt icon-mr"></i>Transaction ID</th>
                                <th class="py-2 text-left"><i class="fas fa-calendar-alt icon-mr"></i>Created At</th>
                                <th class="py-2 text-left"><i class="fas fa-info-circle icon-mr"></i>Status</th>
                                <th class="py-2 text-left"><i class="fas fa-cogs icon-mr"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ordersBody">
                            <?php foreach ($orders as $index => $order): ?>
                                <tr class="border-b animate-row" style="--row-index: <?php echo $index; ?>;" data-id="<?php echo htmlspecialchars($order['id']); ?>" data-items="<?php echo htmlspecialchars($order['items'] ?? ''); ?>">
                                    <td class="py-2"><?php echo htmlspecialchars($order['id']); ?></td>
                                    <td class="py-2 whitespace-pre-wrap"><?php echo htmlspecialchars($order['items'] ?? 'N/A'); ?></td>
                                    <td class="py-2">RWF <?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td class="py-2"><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                    <td class="py-2"><?php echo htmlspecialchars($order['transaction_id'] ?? 'N/A'); ?></td>
                                    <td class="py-2"><?php echo $order['created_at']; ?></td>
                                    <td class="py-2"><?php echo htmlspecialchars($order['status']); ?></td>
                                    <td class="py-2 actions-flex">
                                        <?php if (!empty($order['screenshot_path'])): ?>
                                            <a href="<?php echo htmlspecialchars($order['screenshot_path']); ?>" target="_blank" class="btn-blue"><i class="fas fa-image mr-1"></i>View Screenshot</a>
                                        <?php endif; ?>
                                        <button onclick="openUpdateStatusModal(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')" class="btn-warning"><i class="fas fa-sync mr-1"></i>Update Status</button>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="delete_order">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" class="btn-red" onclick="return confirm('Are you sure you want to delete this order?')"><i class="fas fa-trash mr-1"></i>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-4">
                    <?php if ($total_orders > 5): ?>
                        <button id="viewMoreOrders" class="view-more-btn"><i class="fas fa-chevron-down icon-mr"></i>View More</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Update Order Status Modal -->
            <div id="updateStatusModal" class="modal">
                <div class="modal-content">
                    <span class="modal-close"><i class="fas fa-times"></i></span>
                    <h2 class="section-title gradient-text"><i class="fas fa-sync icon-mr"></i>Update Order Status</h2>
                    <form id="updateStatusForm" method="POST" class="form-grid">
                        <input type="hidden" name="action" value="update_order_status">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="id" id="updateOrderId">
                        <div class="form-group">
                            <label for="updateOrderStatus">Status</label>
                            <select name="status" id="updateOrderStatus" required>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <span class="error-message" id="updateOrderStatusError"></span>
                        </div>
                        <button type="submit" class="btn-gold col-span-2 animate-glow">
                            <i class="fas fa-save icon-mr"></i>Update Status
                        </button>
                    </form>
                </div>
            </div>

            <!-- Subscribers -->
            <div id="subscribers" class="card animate-slide-in">
                <h2 class="section-title gradient-text"><i class="fas fa-users icon-mr"></i>Subscribers</h2>
                <div class="flex-between mb-4">
                    <p class="text-base font-semibold"><i class="fas fa-users icon-mr"></i>Total Subscribers: <?php echo $total_subscribers; ?></p>
                    <div class="flex-center space-x-4">
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="export_subscribers">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <button type="submit" class="btn-green animate-pop">
                                <i class="fas fa-download icon-mr"></i>Export CSV
                            </button>
                        </form>
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="clear_subscribers">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <button type="submit" class="btn-red animate-pop" onclick="return confirm('Are you sure you want to clear all subscribers?')">
                                <i class="fas fa-trash icon-mr"></i>Clear Subscribers
                            </button>
                        </form>
                    </div>
                </div>
                <div class="search-container">
                    <input type="text" id="subscriberSearch" placeholder="Search subscribers..." class="search-input">
                </div>
                <div class="table-responsive">
                    <table id="subscribersTable">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2 text-left"><i class="fas fa-at icon-mr"></i>Email</th>
                                <th class="py-2 text-left"><i class="fas fa-calendar-alt icon-mr"></i>Subscribed At</th>
                                <th class="py-2 text-left"><i class="fas fa-cogs icon-mr"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="subscribersBody">
                            <?php foreach ($subscribers as $index => $subscriber): ?>
                                <tr class="border-b animate-row" style="--row-index: <?php echo $index; ?>;" data-email="<?php echo htmlspecialchars($subscriber['email']); ?>">
                                    <td class="py-2"><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                    <td class="py-2"><?php echo $subscriber['created_at']; ?></td>
                                    <td class="py-2 actions-flex">
                                        <a href="mailto:<?php echo htmlspecialchars($subscriber['email']); ?>?subject=Re:%20Your%20Subscription%20to%20Mahirwe%20Smart%20Business" class="btn-email"><i class="fas fa-envelope mr-1"></i>Email</a>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="delete_subscriber">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="id" value="<?php echo $subscriber['id']; ?>">
                                            <button type="submit" class="btn-red" onclick="return confirm('Are you sure you want to delete this subscriber?')"><i class="fas fa-trash mr-1"></i>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-4">
                    <?php if ($total_subscribers > 5): ?>
                        <a href="subscribers.php" class="btn-gold animate-glow"><i class="fas fa-chevron-down icon-mr"></i>View More</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Messages -->
            <div id="messages" class="card animate-slide-in">
                <h2 class="section-title gradient-text"><i class="fas fa-envelope icon-mr"></i>Messages</h2>
                <div class="search-container">
                    <input type="text" id="messageSearch" placeholder="Search messages..." class="search-input">
                </div>
                <div class="table-responsive">
                    <table id="messagesTable">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2 text-left"><i class="fas fa-user icon-mr"></i>Name</th>
                                <th class="py-2 text-left"><i class="fas fa-at icon-mr"></i>Email</th>
                                <th class="py-2 text-left"><i class="fas fa-comment icon-mr"></i>Message</th>
                                <th class="py-2 text-left"><i class="fas fa-calendar-alt icon-mr"></i>Sent At</th>
                                <th class="py-2 text-left"><i class="fas fa-cogs icon-mr"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="messagesBody">
                            <?php foreach ($messages as $index => $message): ?>
                                <tr class="border-b animate-row" style="--row-index: <?php echo $index; ?>;" data-name="<?php echo htmlspecialchars($message['name']); ?>" data-email="<?php echo htmlspecialchars($message['email']); ?>" data-message="<?php echo htmlspecialchars($message['message']); ?>">
                                    <td class="py-2"><?php echo htmlspecialchars($message['name']); ?></td>
                                    <td class="py-2"><?php echo htmlspecialchars($message['email']); ?></td>
                                    <td class="py-2 whitespace-pre-wrap"><?php echo htmlspecialchars($message['message']); ?></td>
                                    <td class="py-2"><?php echo $message['created_at']; ?></td>
                                    <td class="py-2 actions-flex">
                                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re:%20Your%20Message%20to%20Mahirwe%20Smart%20Business" class="btn-email"><i class="fas fa-envelope mr-1"></i>Reply</a>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="delete_message">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                                            <button type="submit" class="btn-red" onclick="return confirm('Are you sure you want to delete this message?')"><i class="fas fa-trash mr-1"></i>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-4">
                    <?php if ($total_messages > 5): ?>
                        <a href="messages.php" class="btn-gold animate-glow"><i class="fas fa-chevron-down icon-mr"></i>View More</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="text-center">
            <p class="text-sm">&copy; 2025 MAHIRWE SMART BUSINESS. All rights reserved.</p>
        </div>
    </footer>
    <script>
        // Theme Management
        function initializeTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light') {
                document.documentElement.classList.add('light');
            }
            updateToggleButton();
            document.getElementById('darkModeToggle').addEventListener('click', () => {
                document.documentElement.classList.toggle('light');
                localStorage.setItem('theme', document.documentElement.classList.contains('light') ? 'light' : 'dark');
                updateToggleButton();
            });
        }

        function updateToggleButton() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const isLight = document.documentElement.classList.contains('light');
            darkModeToggle.querySelector('.fa-moon').classList.toggle('light:hidden', isLight);
            darkModeToggle.querySelector('.fa-sun').classList.toggle('hidden', !isLight);
        }

        // Sidebar Toggle for Mobile
        function initializeSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 767 && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            });

            // Smooth scroll for sidebar links
            document.querySelectorAll('.sidebar-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetId = link.getAttribute('href').substring(1);
                    document.getElementById(targetId).scrollIntoView({ behavior: 'smooth' });
                    if (window.innerWidth <= 767) {
                        sidebar.classList.remove('open');
                    }
                });
            });
        }

        // Notification Handling
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');
            const notificationIcon = document.getElementById('notificationIcon');
            notificationText.textContent = message;
            notification.className = 'notification animate-slide-in';
            notification.classList.add(type === 'error' ? 'notification-error' : '');
            notificationIcon.className = `fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'} mr-2`;
            notification.style.display = 'block';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        // Modal Handling
        function initializeModals() {
            const modals = document.querySelectorAll('.modal');
            const modalCloses = document.querySelectorAll('.modal-close');

            modalCloses.forEach(close => {
                close.addEventListener('click', () => {
                    modals.forEach(modal => modal.style.display = 'none');
                });
            });

            window.addEventListener('click', (e) => {
                modals.forEach(modal => {
                    if (e.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });
        }

        function openEditCategoryModal(id, name, iconClass) {
            document.getElementById('editCategoryId').value = id;
            document.getElementById('editCategoryName').value = name;
            document.getElementById('editCategoryIconClass').value = iconClass;
            document.getElementById('editCategoryModal').style.display = 'flex';
        }

        function openEditProductModal(id, categoryId, name, description, price, priceBefore, image, rating, features, isTrending) {
            document.getElementById('editProductId').value = id;
            document.getElementById('editProductCategoryId').value = categoryId;
            document.getElementById('editProductName').value = name;
            document.getElementById('editProductDescription').value = description;
            document.getElementById('editProductPrice').value = price;
            document.getElementById('editProductPriceBefore').value = priceBefore || '';
            document.getElementById('editProductImage').value = image;
            document.getElementById('editProductRating').value = rating || '';
            document.getElementById('editProductFeatures').value = features;
            document.getElementById('editProductIsTrending').checked = isTrending;
            document.getElementById('editProductModal').style.display = 'flex';
        }

        function openUpdateStatusModal(id, status) {
            document.getElementById('updateOrderId').value = id;
            document.getElementById('updateOrderStatus').value = status;
            document.getElementById('updateStatusModal').style.display = 'flex';
        }

        // Form Validation
        function validateForm(formId) {
            const form = document.getElementById(formId);
            let isValid = true;
            form.querySelectorAll('input[required], select[required], textarea[required]').forEach(input => {
                const errorElement = document.getElementById(`${input.id}Error`);
                if (!input.value.trim()) {
                    input.classList.add('invalid');
                    errorElement.textContent = `${input.previousElementSibling.textContent} is required`;
                    isValid = false;
                } else {
                    input.classList.remove('invalid');
                    errorElement.textContent = '';
                }
                if (input.name === 'rating' && input.value && (input.value < 0 || input.value > 5)) {
                    input.classList.add('invalid');
                    errorElement.textContent = 'Rating must be between 0 and 5';
                    isValid = false;
                }
            });
            return isValid;
        }

        // Search Functionality
        function initializeSearch(tableId, searchId, dataAttributes) {
            const searchInput = document.getElementById(searchId);
            const tableBody = document.getElementById(tableId);
            searchInput.addEventListener('input', () => {
                const searchTerm = searchInput.value.toLowerCase();
                const rows = tableBody.querySelectorAll('tr');
                rows.forEach(row => {
                    let match = false;
                    dataAttributes.forEach(attr => {
                        if (row.dataset[attr].toLowerCase().includes(searchTerm)) {
                            match = true;
                        }
                    });
                    row.style.display = match ? '' : 'none';
                });
            });
        }

        // View More Functionality
        function initializeViewMore(buttonId, tableId, fetchUrl, dataAttributes) {
            const button = document.getElementById(buttonId);
            if (!button) return;
            let offset = 5;
            button.addEventListener('click', async () => {
                try {
                    button.classList.add('btn-loading');
                    const response = await fetch(`${fetchUrl}?offset=${offset}&limit=5`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();
                    const tableBody = document.getElementById(tableId);
                    data.forEach((item, index) => {
                        const row = document.createElement('tr');
                        row.className = 'border-b animate-row';
                        row.style = `--row-index: ${offset + index};`;
                        dataAttributes.forEach(attr => {
                            row.dataset[attr] = item[attr];
                        });
                        row.innerHTML = generateRowHTML(item);
                        tableBody.appendChild(row);
                    });
                    offset += 5;
                    if (data.length < 5) {
                        button.style.display = 'none';
                    }
                } catch (error) {
                    showNotification('Failed to load more data', 'error');
                } finally {
                    button.classList.remove('btn-loading');
                }
            });
        }

        // Generate Row HTML (Placeholder - Replace with actual row generation logic based on table)
        function generateRowHTML(item) {
            // This is a placeholder. You should implement specific row generation logic for each table
            return ''; // Implement based on table structure
        }

        // Initialize Everything
        document.addEventListener('DOMContentLoaded', () => {
            initializeTheme();
            initializeSidebar();
            initializeModals();

            // Initialize form validation
            ['addCategoryForm', 'editCategoryForm', 'addProductForm', 'editProductForm', 'updateStatusForm'].forEach(formId => {
                const form = document.getElementById(formId);
                if (form) {
                    form.addEventListener('submit', (e) => {
                        if (!validateForm(formId)) {
                            e.preventDefault();
                        }
                    });
                }
            });

            // Initialize search
            initializeSearch('categoriesBody', 'categorySearch', ['name', 'icon']);
            initializeSearch('productsBody', 'productSearch', ['name', 'category']);
            initializeSearch('ordersBody', 'orderSearch', ['id', 'items']);
            initializeSearch('subscribersBody', 'subscriberSearch', ['email']);
            initializeSearch('messagesBody', 'messageSearch', ['name', 'email', 'message']);

            // Initialize view more (Note: You need to create corresponding PHP endpoints for these)
            initializeViewMore('viewMoreCategories', 'categoriesBody', 'fetch_categories.php', ['name', 'icon']);
            initializeViewMore('viewMoreProducts', 'productsBody', 'fetch_products.php', ['name', 'category']);
            initializeViewMore('viewMoreOrders', 'ordersBody', 'fetch_orders.php', ['id', 'items']);

            // Show notification if set
            <?php if ($notification_message): ?>
                showNotification('<?php echo addslashes($notification_message); ?>', '<?php echo $notification_type; ?>');
            <?php endif; ?>
        });
    </script>
</body>
</html>