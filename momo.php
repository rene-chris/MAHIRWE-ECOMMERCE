<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch cart from session
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total_amount = array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $cart));

// Debug: Log session data (remove in production)
error_log("Session Data: " . print_r($_SESSION, true));
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay with MTN Mobile Money - MAHIRWE SMART BUSINESS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* CSS Variables */
        :root {
            --gold-start: #FFD700;
            --gold-end: #B8860B;
            --bg-light: #F9FAFB;
            --bg-dark: #1F2937;
            --card-light: #FFFFFF;
            --card-dark: #000000ff; /* Matches airtel.php */
            --text-light: #000000; /* Black for light mode */
            --text-dark: #FFFFFF; /* White for dark mode */
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
        }

        /* Base Styles */
        * {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(to bottom, var(--bg-light), #E5E7EB);
            color: var(--text-light);
            transition: background-color 0.3s, color 0.3s;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .dark {
            background: linear-gradient(to bottom, var(--bg-dark), #111827);
            color: var(--text-dark);
        }

        /* Utility Classes */
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

        /* Layout */
        .container {
            max-width: 1280px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        header {
            background: linear-gradient(to right, var(--card-light), #F3F4F6);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            position: sticky;
            top: 0;
            z-index: 50;
            transition: background-color 0.3s;
            padding: 0.5rem 0; /* Reduced height to match airtel.php */
        }

        .dark header {
            background: linear-gradient(to right, var(--card-dark), #1F2937);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-container {
            width: 2.5rem;
            height: 2.5rem;
            background: linear-gradient(135deg, var(--gold-start), var(--gold-end));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Typography */
        .title {
            font-size: 1.875rem;
            font-weight: 900;
        }

        .subtitle {
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
        }

        .dark .subtitle {
            color: #d1d5db;
        }

        .main-title {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 2.5rem;
            text-align: center;
        }

        @media (min-width: 768px) {
            .main-title {
                font-size: 3.75rem;
            }
        }

        .card-title {
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
        }

        .order-summary-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .cart-total {
            font-size: 1.25rem;
            font-weight: 700;
            text-align: right;
        }

        /* Components */
        .dark-mode-toggle {
            background: linear-gradient(135deg, #E5E7EB, #D1D5DB);
            color: var(--text-light);
            border-radius: 50%;
            padding: 0.8rem;
            transition: all 0.3s ease;
            border: 2px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dark .dark-mode-toggle {
            background: linear-gradient(135deg, var(--gold-start), var(--gold-end));
            color: var(--text-dark);
            border-color: var(--border-dark);
        }

        .dark-mode-toggle:hover {
            transform: scale(1.1) rotate(10deg);
            box-shadow: 0 0 15px rgba(255,215,0,0.5);
        }

        button, a.button {
            border-radius: 0.75rem;
            padding: 0.9rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 2px solid var(--gold-start);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none; /* Explicitly ensure no text-decoration */
        }

            .btn-gold {
            background: linear-gradient(135deg, var(--gold-start), var(--gold-end));
            color: white;
            padding:10px;
            text-align:center;
            border-radius:6px;
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

        .btn-blue {
            background: linear-gradient(135deg, var(--button-bg), var(--button-hover));
            color: white;
            border-color: var(--button-bg);
             padding:10px;
            text-align:center;
            border-radius:6px;
            text-decoration: none; /* Explicitly ensure no text-decoration */
        }

        .btn-blue:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37,99,235,0.5);
        }

        .card {
            background: linear-gradient(to bottom, var(--card-light), #F3F4F6);
            border-radius: 1.5rem;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            padding: 2rem;
            max-width: 32rem;
            margin: 2rem auto; /* Centered with margin for better spacing */
            display: flex;
            flex-direction: column;
        }

        .dark .card {
            background: linear-gradient(to bottom, var(--card-dark), #1F2937);
        }

        .card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25);
            transform: translateY(-4px);
        }

        .notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 50;
            display: flex;
        }

        .notification-content {
            display: flex;
            align-items: center;
            background-color: #22C55E;
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .notification.error .notification-content {
            background-color: #EF4444;
        }

        .notification.warning .notification-content {
            background-color: #F59E0B;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #F3F4F6;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .dark .cart-item {
            background-color: #4B5563;
        }

        .instructions {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .dark .instructions {
            color: var(--text-dark);
        }

        .instructions-list {
            list-style-type: decimal;
            padding-left: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
        }

        .instructions-list li {
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .dark .instructions-list li {
            color: var(--text-dark);
        }

        .form-group {
            margin-bottom: 1.5rem;
            display: flex;
            flex-direction: column;
        }

        .form-label {
            display: block;
            color: var(--text-light);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .dark .form-label {
            color: var(--text-dark);
        }

        input, select, textarea {
            border: 2px solid var(--border-light);
            border-radius: 0.75rem;
            padding: 0.9rem;
            transition: all 0.3s ease;
            background-color: #F9FAFB;
            width: 100%;
            display: flex;
        }

        .dark input, .dark select, .dark textarea {
            background-color: #2D3748;
            border-color: var(--border-dark);
            color: var(--text-dark);
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--gold-start);
            box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.4);
        }

        /* Main Content */
        .main-content {
            padding: 3rem 0;
            display: flex;
            flex-direction: column;
            flex-grow: 1; /* Ensures content takes available space */
        }

        /* Footer */
        footer {
            background: linear-gradient(to top, #111827, #1F2937);
            color: #D1D5DB;
            padding: 3rem 0;
            margin-top: auto;
        }

        .footer-content {
            text-align: center;
            font-size: 0.875rem;
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>
    <!-- Notification System -->
    <div id="notification" class="notification hidden animate-slide-in">
        <div class="notification-content">
            <i id="notificationIcon" class="fas fa-check-circle mr-2"></i>
            <span id="notificationText"></span>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo-group">
                    <div class="logo-container animate-glow">
                        <i class="fas fa-bolt text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="title gradient-text">MAHIRWE</h1>
                        <p class="subtitle">SMART BUSINESS</p>
                    </div>
                </div>
                <div class="nav-buttons">
                    <button id="darkModeToggle" class="dark-mode-toggle">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden"></i>
                    </button>
                    <a href="index.php" class="btn-blue animate-pop">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Shop
                    </a>
                    <a href="logout.php" class="btn-gold animate-pop">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container main-content">
        <h1 class="main-title gradient-text animate-slide-in">Pay with MTN Mobile Money</h1>
        <div class="card animate-slide-in">
            <h2 class="card-title gradient-text">Complete Your Payment</h2>
            <div class="mb-8">
                <h3 class="order-summary-title">Order Summary</h3>
                <?php if (empty($cart)): ?>
                    <p class="instructions">Your cart is empty.</p>
                <?php else: ?>
                    <ul class="space-y-3 mb-6">
                        <?php foreach ($cart as $index => $item): ?>
                            <li class="cart-item animate-row" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                <span class="font-medium"><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                                <span class="font-semibold">RWF <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="cart-total gradient-text">
                        Total: RWF <?php echo number_format($total_amount, 2); ?>
                    </div>
                <?php endif; ?>
            </div>
            <p class="instructions">Follow these steps to complete your payment:</p>
            <ol class="instructions-list">
                <li class="animate-row" style="animation-delay: 0s;">Dial *182# on your MTN phone</li>
                <li class="animate-row" style="animation-delay: 0.1s;">Select option 1: Send Money</li>
                <li class="animate-row" style="animation-delay: 0.2s;">Enter recipient number: <strong>0788813118</strong></li>
                <li class="animate-row" style="animation-delay: 0.3s;">Enter amount: <strong>RWF <?php echo number_format($total_amount, 2); ?></strong></li>
                <li class="animate-row" style="animation-delay: 0.4s;">Confirm with your PIN</li>
                <li class="animate-row" style="animation-delay: 0.5s;">You'll receive a confirmation SMS</li>
            </ol>
            <form id="paymentForm" enctype="multipart/form-data" class="space-y-6">
                <div class="form-group">
                    <label for="transaction_id" class="form-label">Transaction ID</label>
                    <input type="text" id="transaction_id" name="transaction_id" placeholder="Enter transaction ID" required>
                </div>
                <div class="form-group">
                    <label for="screenshot" class="form-label">Upload Payment Screenshot (Optional)</label>
                    <input type="file" id="screenshot" name="screenshot" accept="image/*">
                </div>
                <button type="submit" class="btn-gold w-full animate-glow">
                    <i class="fas fa-check mr-2"></i>Approve Payment
                </button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2025 MAHIRWE SMART BUSINESS. All rights reserved.</p>
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
            notification.className = `notification animate-slide-in ${type === 'success' ? '' : type === 'warning' ? 'warning' : 'error'}`;
            notificationIcon.className = `fas ${type === 'success' ? 'fa-check-circle' : type === 'warning' ? 'fa-exclamation-circle' : 'fa-times-circle'} mr-2`;
            notification.classList.remove('hidden');
            setTimeout(() => notification.classList.add('hidden'), 4000);
        }

        // Handle form submission
        document.getElementById('paymentForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('payment_method', 'MTN Mobile Money');
            formData.append('user_id', '<?php echo $_SESSION['user_id']; ?>');
            formData.append('total_amount', '<?php echo $total_amount; ?>');
            formData.append('cart', JSON.stringify(<?php echo json_encode($cart); ?>));

            try {
                const response = await fetch('process_payment.php', {
                    method: 'POST',
                    body: formData
                });
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                const data = await response.json();
                if (data.success) {
                    showNotification('Payment approved! Redirecting to account...', 'success');
                    setTimeout(() => {
                        window.location.href = 'account.php';
                    }, 1500);
                } else {
                    showNotification(data.message || 'Error processing payment', 'error');
                }
            } catch (error) {
                console.error('Fetch error:', error);
                showNotification('Network error: Unable to process payment', 'error');
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', initializeTheme);
    </script>
</body>
</html>