<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize notification variables
$notification_message = '';
$notification_type = 'success';

// Handle delete and clear actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'delete_order') {
            $order_id = (int)$_POST['order_id'];
            $conn->query("DELETE FROM order_items WHERE order_id = $order_id");
            $result = $conn->query("DELETE FROM orders WHERE id = $order_id AND user_id = $user_id");
            if ($conn->affected_rows > 0) {
                $notification_message = 'Order deleted successfully!';
            } else {
                $notification_message = 'Order not found or you do not have permission to delete it.';
                $notification_type = 'error';
            }
        } elseif ($_POST['action'] === 'clear_orders') {
            $conn->query("DELETE FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE user_id = $user_id)");
            $conn->query("DELETE FROM orders WHERE user_id = $user_id");
            $notification_message = 'All orders cleared successfully!';
        }
    } catch (mysqli_sql_exception $e) {
        $notification_message = 'Database error: ' . htmlspecialchars($e->getMessage());
        $notification_type = 'error';
    }
}

// Fetch orders and their items
try {
    $orders = $conn->query("
        SELECT o.id, o.total_amount, o.status, o.created_at, o.payment_method, oi.product_name, oi.quantity, oi.price
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = $user_id
        ORDER BY o.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    $notification_message = 'Failed to fetch orders: ' . htmlspecialchars($e->getMessage());
    $notification_type = 'error';
    $orders = [];
}

// Group items by order
$grouped_orders = [];
foreach ($orders as $order) {
    if (!isset($grouped_orders[$order['id']])) {
        $grouped_orders[$order['id']] = [
            'id' => $order['id'],
            'total_amount' => $order['total_amount'],
            'status' => $order['status'],
            'created_at' => $order['created_at'],
            'payment_method' => $order['payment_method'],
            'items' => []
        ];
    }
    if ($order['product_name']) {
        $grouped_orders[$order['id']]['items'][] = [
            'product_name' => $order['product_name'],
            'quantity' => $order['quantity'],
            'price' => $order['price']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - MAHIRWE SMART BUSINESS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold-start: #FFD700;
            --gold-end: #B8860B;
            --bg-light: #F9FAFB;
            --bg-dark: #1F2937;
            --card-light: #FFFFFF;
            --card-dark: #374151;
            --text-light: #1F2937;
            --text-dark: #F9FAFB;
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
            --processing-bg: #3B82F6;
            --shipped-bg: #8B5CF6;
            --delivered-bg: #10B981;
            --cancelled-bg: #6B7280;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom, var(--bg-light), #E5E7EB);
            transition: background-color 0.3s, color 0.3s;
        }

        .dark {
            background: linear-gradient(to bottom, var(--bg-dark), #111827);
            color: var(--text-dark);
        }

        .gradient-gold {
            background: linear-gradient(135deg, var(--gold-start), var(--gold-end));
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--gold-start), var(--gold-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .animate-slide-in {
            animation: slideIn 0.6s ease-out;
        }

        .animate-glow {
            animation: glow 1.5s ease-in-out infinite alternate;
        }

        .animate-pop {
            animation: pop 0.4s ease-out;
        }

        @keyframes slideIn {
            0% { transform: translateY(30px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        @keyframes glow {
            from { box-shadow: 0 0 8px rgba(255,215,0,0.4); }
            to { box-shadow: 0 0 20px rgba(255,215,0,0.8); }
        }

        @keyframes pop {
            0% { transform: scale(0.85); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        header {
            background: linear-gradient(to right, var(--card-light), #F3F4F6);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transition: background-color 0.3s;
        }

        .dark header {
            background: linear-gradient(to right, var(--card-dark), #1F2937);
        }

        .dark-mode-toggle {
            background: linear-gradient(135deg, #E5E7EB, #D1D5DB);
            color: var(--text-light);
            border-radius: 50%;
            padding: 0.8rem;
            transition: all 0.3s ease;
        }

        .dark .dark-mode-toggle {
            background: linear-gradient(135deg, var(--gold-start), var(--gold-end));
            color: var(--text-dark);
        }

        .dark-mode-toggle:hover {
            transform: scale(1.1) rotate(10deg);
            box-shadow: 0 0 15px rgba(255,215,0,0.5);
        }

        .card {
            background: linear-gradient(to bottom, var(--card-light), #F3F4F6);
            border-radius: 1.5rem;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .dark .card {
            background: linear-gradient(to bottom, var(--card-dark), #1F2937);
        }

        .card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25);
            transform: translateY(-4px);
        }

        .btn-blue {
            background: linear-gradient(135deg, var(--button-bg), var(--button-hover));
            color: white;
            text-align:center;
            border-radius:6px;
            padding:10px;
        }

        .btn-blue:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37,99,235,0.5);
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold-start), var(--gold-end));
            color: white;
            text-align:center;
            padding:10px;
            border-radius:5px;
        }

        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,215,0,0.5);
        }

        .btn-gold::after {
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

        .btn-gold:hover::after {
            width: 200%;
            height: 200%;
        }

        .btn-red {
            background: linear-gradient(135deg, var(--danger-bg), var(--danger-hover));
            color: white;
            text-align:center;
            border-radius:5px;
            padding:7px;
        }

        .btn-red:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239,68,68,0.5);
        }

        footer {
            background: linear-gradient(to top, #111827, #1F2937);
            color: #D1D5DB;
        }

        .status-pending {
            background-color: var(--warning-bg);
            color: white;
        }

        .status-processing {
            background-color: var(--processing-bg);
            color: white;
        }

        .status-shipped {
            background-color: var(--shipped-bg);
            color: white;
        }

        .status-delivered {
            background-color: var(--delivered-bg);
            color: white;
        }

        .status-cancelled {
            background-color: var(--cancelled-bg);
            color: white;
        }
    </style>
</head>
<body class="transition-colors duration-300">
    <!-- Notification System -->
    <div id="notification" class="fixed top-4 right-4 z-50 hidden animate-slide-in">
        <div class="flex items-center bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg">
            <i id="notificationIcon" class="fas fa-check-circle mr-2"></i>
            <span id="notificationText"><?php echo htmlspecialchars($notification_message); ?></span>
        </div>
    </div>

    <!-- Header -->
    <header class="shadow-xl sticky top-0 z-50 transition-colors duration-300">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="w-14 h-14 gradient-gold rounded-full flex items-center justify-center shadow-lg animate-glow">
                        <i class="fas fa-bolt text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-black gradient-text">MAHIRWE</h1>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">SMART BUSINESS</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="darkModeToggle" class="dark-mode-toggle">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden"></i>
                    </button>
                    <a href="index.php" class="btn-blue animate-pop flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Shop
                    </a>
                    <a href="logout.php" class="btn-gold animate-pop flex items-center">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-12">
        <h1 class="text-4xl md:text-5xl font-black gradient-text mb-10 text-center animate-slide-in">My Transaction History</h1>
        <div class="card p-8 max-w-4xl mx-auto animate-slide-in">
            <?php if (empty($grouped_orders)): ?>
                <p class="text-gray-600 dark:text-gray-300 text-center">No transactions yet.</p>
            <?php else: ?>
                <div class="flex justify-end mb-4">
                    <form method="POST" onsubmit="return confirm('Are you sure you want to clear all orders?')">
                        <input type="hidden" name="action" value="clear_orders">
                        <button type="submit" class="btn-red animate-pop flex items-center">
                            <i class="fas fa-trash-alt mr-2"></i>Clear All Orders
                        </button>
                    </form>
                </div>
                <?php foreach ($grouped_orders as $index => $order): ?>
                    <div class="mb-6 animate-slide-in" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Order #<?php echo $order['id']; ?></h3>
                            <div class="flex items-center space-x-4">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this order?')">
                                    <input type="hidden" name="action" value="delete_order">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" class="btn-red animate-pop flex items-center">
                                        <i class="fas fa-trash mr-2"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
                            <p class="text-gray-600 dark:text-gray-300"><strong>Payment Method:</strong> <?php echo $order['payment_method']; ?></p>
                            <p class="text-gray-600 dark:text-gray-300"><strong>Date:</strong> <?php echo $order['created_at']; ?></p>
                            <p class="text-gray-600 dark:text-gray-300"><strong>Total:</strong> RWF <?php echo number_format($order['total_amount'], 2); ?></p>
                            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mt-4">Items:</h4>
                            <ul class="space-y-2">
                                <?php foreach ($order['items'] as $item): ?>
                                    <li class="flex justify-between text-gray-800 dark:text-gray-200">
                                        <span><?php echo htmlspecialchars($item['product_name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                                        <span>RWF <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-12">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <p class="text-sm text-gray-400">&copy; 2025 MAHIRWE SMART BUSINESS. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Initialize theme
        function initializeTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            }
            updateToggleButton();
            document.getElementById('darkModeToggle').addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
                updateToggleButton();
            });
        }

        function updateToggleButton() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const isDark = document.documentElement.classList.contains('dark');
            darkModeToggle.querySelector('.fa-moon').classList.toggle('dark:hidden', isDark);
            darkModeToggle.querySelector('.fa-sun').classList.toggle('hidden', !isDark);
        }

        // Show notification
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');
            const notificationIcon = document.getElementById('notificationIcon');
            notificationText.textContent = message;
            notification.className = `fixed top-4 right-4 z-50 animate-slide-in flex items-center ${type === 'success' ? 'bg-green-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-red-500'} text-white px-4 py-3 rounded-lg shadow-lg`;
            notificationIcon.className = `fas ${type === 'success' ? 'fa-check-circle' : type === 'warning' ? 'fa-exclamation-circle' : 'fa-times-circle'} mr-2`;
            notification.classList.remove('hidden');
            setTimeout(() => notification.classList.add('hidden'), 4000);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            initializeTheme();
            <?php if ($notification_message): ?>
                showNotification('<?php echo htmlspecialchars($notification_message); ?>', '<?php echo $notification_type; ?>');
            <?php endif; ?>
        });
    </script>
</body>
</html>