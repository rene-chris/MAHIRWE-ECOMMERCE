<?php
session_start();
require_once 'config.php';

// Fetch categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Fetch products with pagination, ensuring no duplicates
$productsPerPage = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $productsPerPage;

$products_query = "SELECT DISTINCT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id";
$where_clauses = [];
if (isset($_GET['category']) && $_GET['category'] != 'all') {
    $category = $conn->real_escape_string($_GET['category']);
    $where_clauses[] = "c.name = '$category'";
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where_clauses[] = "(p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}
if (!empty($where_clauses)) {
    $products_query .= " WHERE " . implode(' AND ', $where_clauses);
}
$products_query .= " LIMIT $productsPerPage OFFSET $offset";

$products = $conn->query($products_query)->fetch_all(MYSQLI_ASSOC);
$total_products_query = "SELECT COUNT(DISTINCT p.id) FROM products p LEFT JOIN categories c ON p.category_id = c.id" . (!empty($where_clauses) ? " WHERE " . implode(' AND ', $where_clauses) : "");
$total_products = $conn->query($total_products_query)->fetch_row()[0];
$total_pages = ceil($total_products / $productsPerPage);

// Fetch trending products for slideshow
$trending_products = $conn->query("SELECT DISTINCT * FROM products WHERE is_trending = 1 LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAHIRWE SMART BUSINESS - Premium Home & Business Electronics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'gold': '#FFD700',
                        'gold-dark': '#B8860B',
                        'gold-light': '#FFF8DC',
                        'dark-bg': '#0f0f0f',
                        'dark-card': '#1a1a1a',
                        'dark-border': '#333333'
                    },
                    fontFamily: {
                        'inter': ['Inter', 'system-ui', 'sans-serif']
                    },
                    animation: {
                        'bounce-slow': 'bounce 3s infinite',
                        'pulse-slow': 'pulse 3s infinite',
                        'fade-in': 'fadeIn 0.8s ease-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'slide-in': 'slideIn 0.5s ease-out',
                        'float': 'float 6s ease-in-out infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                        'shake': 'shake 0.5s ease-in-out',
                        'slide-left': 'slideLeft 0.5s ease-in-out',
                        'slide-right': 'slideRight 0.5s ease-in-out',
                        'zoom-in': 'zoomIn 0.3s ease-out',
                        'blur-in': 'blurIn 0.5s ease-out',
                        'gradient-shift': 'gradientShift 3s ease infinite'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        slideIn: {
                            '0%': { transform: 'translateX(20px)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' }
                        },
                        slideLeft: {
                            '0%': { transform: 'translateX(100%)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' }
                        },
                        slideRight: {
                            '0%': { transform: 'translateX(0)', opacity: '1' },
                            '100%': { transform: 'translateX(100%)', opacity: '0' }
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        glow: {
                            'from': { boxShadow: '0 0 5px rgba(255,215,0,0.3)' },
                            'to': { boxShadow: '0 0 20px rgba(255,215,0,0.6)' }
                        },
                        zoomIn: {
                            '0%': { transform: 'scale(0.9)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' }
                        },
                        blurIn: {
                            '0%': { filter: 'blur(10px)', opacity: '0' },
                            '100%': { filter: 'blur(0)', opacity: '1' }
                        },
                        gradientShift: {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .hero-bg {
            background: linear-gradient(135deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.6) 100%), url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .gradient-gold {
            background: linear-gradient(135deg, #FFD700 0%, #B8860B 100%);
        }
        .gradient-text {
            background: linear-gradient(135deg, #FFD700 0%, #B8860B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .gradient-border {
            border: 2px solid;
            border-image: linear-gradient(135deg, #FFD700 0%, #B8860B 100%) 1;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .dark .glass-effect {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .slideshow-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            border-radius: 1rem;
        }
        .slideshow-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }
        .slideshow-slide.active {
            opacity: 1;
        }
        .slideshow-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            cursor: pointer;
            z-index: 10;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        .slideshow-nav:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: translateY(-50%) scale(1.1);
        }
        .slideshow-prev { left: 10px; }
        .slideshow-next { right: 10px; }
        .notification-enter {
            transform: translateX(100%);
            opacity: 0;
        }
        .notification-enter-active {
            transform: translateX(0);
            opacity: 1;
            transition: all 0.3s ease-in;
        }
        .notification-exit {
            transform: translateX(0);
            opacity: 1;
        }
        .notification-exit-active {
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease-out;
        }
        .product-card {
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .product-card img {
            object-fit: cover;
            width: 100%;
            height: 100%;
            transition: transform 0.5s ease;
        }
        .product-card:hover img {
            transform: scale(1.05);
        }
        .product-card.grid-view {
            display: block;
        }
        .product-card.list-view .flex {
            display: flex;
            align-items: center;
            background: #fff;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .product-card .flex-1 {
            flex: 1;
        }
        .chatbot-header {
            background: linear-gradient(135deg, #FFD700 0%, #B8860B 100%);
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .chatbot-message {
            transition: all 0.3s ease;
        }
        .chatbot-message.bot {
            background: #f1f5f9;
            border-radius: 15px 15px 15px 0;
        }
        .chatbot-message.user {
            background: #FFD700;
            border-radius: 15px 15px 0 15px;
            color: #1f2937;
        }
        .chatbot-container {
            animation: slide-left 0.3s ease-in-out;
        }
        .chatbot-container.hidden {
            animation: slide-right 0.3s ease-in-out;
        }
        .dark .rating-container, .dark .action-container {
            background: #2d2d2d !important;
        }
        .feature-card {
            position: relative;
            overflow: hidden;
        }
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,215,0,0.1) 0%, rgba(184,134,11,0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .feature-card:hover::before {
            opacity: 1;
        }
        .feature-icon {
            transition: transform 0.3s ease;
        }
        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }
        .deal-card {
            position: relative;
            overflow: hidden;
        }
        .deal-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
        }
        .deal-card:hover::after {
            left: 100%;
        }
        .testimonial-card {
            position: relative;
        }
        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: -10px;
            left: 20px;
            font-size: 80px;
            color: rgba(255,215,0,0.2);
            font-family: Georgia, serif;
        }
        @media (max-width: 640px) {
            .product-card .w-48 {
                width: 120px;
                height: 120px;
            }
            .product-card h4 {
                font-size: 1rem;
            }
            .product-card p {
                font-size: 0.875rem;
            }
            .product-card .text-2xl {
                font-size: 1.25rem;
            }
            .product-card button {
                padding: 0.5rem;
                font-size: 0.875rem;
            }
            #chatbotContainer {
                width: 90%;
                max-height: 70vh;
            }
            .hero-bg {
                background-attachment: scroll;
            }
        }
        .loading-spinner {
            border: 4px solid rgba(255, 215, 0, 0.3);
            border-radius: 50%;
            border-top: 4px solid #FFD700;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .parallax-section {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        .stagger-animation > * {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease forwards;
        }
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50 transition-colors duration-300 font-inter">
    <!-- Notification System -->
    <div id="notification" class="fixed top-4 right-4 z-50">
        <div id="notificationContainer" class="space-y-2"></div>
    </div>

    <!-- Shopping Cart Modal -->
    <div id="cartModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="modal-content w-full max-w-4xl p-8 bg-white dark:bg-dark-card rounded-2xl shadow-2xl animate-zoom-in">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Shopping Cart</h3>
                <button onclick="closeCart()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-2xl transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="cartItems" class="space-y-4 mb-6 max-h-96 overflow-y-auto"></div>
            <div class="border-t pt-4">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-xl font-bold text-gray-800 dark:text-white">Total:</span>
                    <span id="cartTotal" class="text-2xl font-black text-gold">RWF 0</span>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <button onclick="clearCart()" class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        Clear Cart
                    </button>
                    <button onclick="showPaymentOptions()" class="flex-1 gradient-gold text-white py-3 px-6 rounded-lg font-bold hover:opacity-90 transition-opacity">
                        <i class="fas fa-credit-card mr-2"></i>Pay Now
                    </button>
                    <button onclick="checkoutWhatsApp()" class="flex-1 gradient-gold text-white py-3 px-6 rounded-lg font-bold hover:opacity-90 transition-opacity">
                        <i class="fab fa-whatsapp mr-2"></i>Checkout via WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Options Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="modal-content w-full max-w-md p-8 bg-white dark:bg-dark-card rounded-2xl shadow-2xl animate-zoom-in">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Choose Payment Method</h3>
                <button onclick="closePaymentModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-2xl transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4">
                <a href="momo.php" class="block px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-center">
                    Pay with Mobile Money
                </a>
                <a href="airtel.php" class="block px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-center">
                    Pay with Airtel Money
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications Dropdown -->
    <div id="notificationDropdown" class="absolute right-0 mt-2 w-80 bg-white dark:bg-dark-card rounded-xl shadow-2xl overflow-hidden z-50 hidden border border-gray-200 dark:border-dark-border max-h-96 overflow-y-auto animate-blur-in">
        <div class="p-4 border-b border-gray-200 dark:border-dark-border flex justify-between items-center">
            <h3 class="font-semibold text-lg text-gray-800 dark:text-white">Notifications</h3>
            <button onclick="clearAllNotifications()" class="text-sm text-red-500 hover:text-red-700 transition-colors">Clear All</button>
        </div>
        <div id="notificationList" class="divide-y divide-gray-200 dark:divide-dark-border"></div>
        <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400" id="noNotifications" style="display: none;">No new notifications</div>
    </div>

    <!-- Chatbot Container -->
    <div id="chatbotContainer" class="fixed bottom-24 right-4 w-96 max-h-[80vh] bg-white dark:bg-dark-card shadow-2xl rounded-2xl flex flex-col hidden z-50 chatbot-container">
        <div class="chatbot-header gradient-gold text-white p-4 relative">
            <div class="flex items-center space-x-4">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center animate-pulse">
                    <i class="fas fa-bolt text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold">MAHIRWE Assistant</h3>
                    <p class="text-xs opacity-90">AI-powered shopping assistant</p>
                </div>
            </div>
            <button onclick="toggleChatbot()" class="absolute top-4 right-4 text-white hover:text-gray-200 text-lg transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="chatbotMessages" class="flex-1 overflow-y-auto p-4 bg-gray-50 dark:bg-gray-900 space-y-4"></div>
        <div class="p-4 bg-white dark:bg-dark-card border-t border-gray-200 dark:border-gray-700">
            <div class="flex gap-2 mb-3">
                <input type="text" id="chatbotInput" placeholder="Ask about products..." 
                       class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold dark:bg-gray-700 dark:text-white text-sm transition-all">
                <button onclick="sendChatMessage()" class="px-4 py-2 gradient-gold text-white rounded-lg hover:opacity-90 transition-opacity">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div class="flex flex-wrap gap-2 justify-center">
                <button onclick="quickQuestion('prices')" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-xs hover:bg-gold hover:text-white transition-colors animate-slide-in" style="animation-delay: 0.1s;">
                    Prices
                </button>
                <button onclick="quickQuestion('delivery')" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-xs hover:bg-gold hover:text-white transition-colors animate-slide-in" style="animation-delay: 0.2s;">
                    Delivery
                </button>
                <button onclick="quickQuestion('warranty')" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-xs hover:bg-gold hover:text-white transition-colors animate-slide-in" style="animation-delay: 0.3s;">
                    Warranty
                </button>
            </div>
        </div>
    </div>

    <!-- Floating Buttons -->
    <a href="https://wa.me/250788813118?text=Hello%20MAHIRWE%20SMART%20BUSINESS!%20I'm%20interested%20in%20your%20products." 
       class="fixed bottom-4 right-4 w-14 h-14 bg-green-500 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-green-600 transition-all z-50 animate-float" 
       target="_blank">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>
    <div class="fixed bottom-20 right-4 chatbot-float bg-gold text-white px-4 py-2 rounded-full shadow-lg hover:shadow-xl transition-all cursor-pointer z-50 animate-float" onclick="toggleChatbot()">
        <i class="fas fa-headset mr-2"></i>Chat Now
    </div>
    <button onclick="scrollToTop()" class="fixed bottom-36 right-4 w-12 h-12 bg-gold text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 opacity-0 z-50" id="scrollTopBtn">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Header -->
    <header class="bg-white dark:bg-dark-card shadow-xl sticky top-0 z-50 transition-colors duration-300 glass-effect">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 gradient-gold rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-bolt text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black gradient-text" id="logo">MAHIRWE</h1>
                        <p class="text-xs text-gray-600 dark:text-gray-300 font-semibold">SMART BUSINESS</p>
                    </div>
                </div>
                <nav class="hidden lg:flex space-x-6 items-center">
                    <a href="#home" class="text-gray-700 dark:text-gray-300 hover:text-gold transition-colors font-semibold relative group">
                        Home
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gold transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="#products" class="text-gray-700 dark:text-gray-300 hover:text-gold transition-colors font-semibold relative group">
                        Products
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gold transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="#categories" class="text-gray-700 dark:text-gray-300 hover:text-gold transition-colors font-semibold relative group">
                        Categories
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gold transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="#deals" class="text-gray-700 dark:text-gray-300 hover:text-gold transition-colors font-semibold relative group">
                        Deals
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gold transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="#about" class="text-gray-700 dark:text-gray-300 hover:text-gold transition-colors font-semibold relative group">
                        About
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gold transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="#contact" class="text-gray-700 dark:text-gray-300 hover:text-gold transition-colors font-semibold relative group">
                        Contact
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-gold transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search products..." 
                               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold text-sm dark:bg-gray-700 dark:text-white w-64 transition-all">
                        <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </nav>
                <div class="flex items-center space-x-2">
                    <button onclick="toggleCart()" class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gold hover:text-white transition-colors relative">
                        <i class="fas fa-shopping-cart"></i>
                        <div id="cartBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center hidden">0</div>
                    </button>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button id="notificationBell" class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gold hover:text-white transition-colors relative">
                            <i class="fas fa-bell"></i>
                            <span id="notificationCount" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                        </button>
                        <a href="account.php" class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gold hover:text-white transition-colors">
                            <i class="fas fa-user"></i>
                        </a>
                        <a href="logout.php" class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gold hover:text-white transition-colors">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gold hover:text-white transition-colors">
                            <i class="fas fa-sign-in-alt"></i>
                        </a>
                        <a href="register.php" class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gold hover:text-white transition-colors">
                            <i class="fas fa-user-plus"></i>
                        </a>
                    <?php endif; ?>
                    <button id="darkModeToggle" class="p-2 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        <i class="fas fa-moon text-gray-700"></i>
                        <i class="fas fa-sun text-gold hidden"></i>
                    </button>
                    <button id="mobileMenuBtn" class="lg:hidden p-2 rounded-full bg-gray-100 dark:bg-gray-700">
                        <i class="fas fa-bars text-gray-700 dark:text-gray-300"></i>
                    </button>
                </div>
            </div>
            <div id="mobileMenu" class="lg:hidden hidden mt-4 py-4 border-t border-gray-200 dark:border-gray-700 stagger-animation">
                <nav class="flex flex-col space-y-3">
                    <a href="#home" class="text-gray-700 dark:text-gray-300 hover:text-gold transition-colors font-semibold">Home</a>
                    <a href="#products" class="text-gray-700 dark:text-gray-300 hover:text-gold transition-colors font-semibold">Products</a>
                    <a href="#categories" class="text-gray-700 dark:text-gray-300 hover:text-gold transition-colors font-semibold">Categories</a>
                    <a href="#deals" class="text-gray-700 dark:text-gray-300 hover:text-gold transition-colors font-semibold">Deals</a>
                    <a href="#about" class="text-gray-700 dark:text-gray-300 hover:text-gold transition-colors font-semibold">About</a>
                    <a href="#contact" class="text-gray-700 dark:text-gray-300 hover:text-gold transition-colors font-semibold">Contact</a>
                    <div class="relative px-4">
                        <input type="text" id="mobileSearchInput" placeholder="Search products..." 
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold text-sm dark:bg-gray-700 dark:text-white transition-all">
                        <i class="fas fa-search absolute right-7 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero-bg min-h-screen flex items-center relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-transparent to-black/50"></div>
        <div class="container mx-auto px-4 relative z-10 flex flex-col lg:flex-row items-center justify-between">
            <div class="max-w-4xl lg:w-2/3">
                <div class="animate-fade-in">
                    <h2 class="text-4xl md:text-6xl lg:text-7xl font-black text-white mb-6 leading-tight">
                        PREMIUM<br>
                        <span class="gradient-text">HOME APPLIANCES</span>
                    </h2>
                    <p class="text-lg md:text-xl text-white mb-8 max-w-2xl leading-relaxed">
                        Discover quality home and business appliances with MAHIRWE SMART BUSINESS. 
                        Experience the best in modern technology with our curated collection of premium products.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 mb-12">
                        <a href="#products" class="gradient-gold text-white px-8 py-4 rounded-full font-bold text-lg hover:opacity-90 transition-opacity inline-flex items-center justify-center shadow-2xl animate-glow">
                            <i class="fas fa-shopping-bag mr-3"></i>Shop Now
                        </a>
                        <a href="#about" class="bg-white/20 backdrop-blur-sm text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-white/30 transition-colors inline-flex items-center justify-center">
                            <i class="fas fa-play mr-3"></i>Learn More
                        </a>
                    </div>
                    <div class="grid grid-cols-3 gap-4 md:gap-8 max-w-2xl">
                        <div class="text-center animate-slide-in" style="animation-delay: 0.2s;">
                            <div class="text-2xl md:text-4xl font-black text-gold mb-2">200+</div>
                            <div class="text-white font-semibold text-sm md:text-base">Products</div>
                        </div>
                        <div class="text-center animate-slide-in" style="animation-delay: 0.4s;">
                            <div class="text-2xl md:text-4xl font-black text-gold mb-2">3000+</div>
                            <div class="text-white font-semibold text-sm md:text-base">Happy Customers</div>
                        </div>
                        <div class="text-center animate-slide-in" style="animation-delay: 0.6s;">
                            <div class="text-2xl md:text-4xl font-black text-gold mb-2">24/7</div>
                            <div class="text-white font-semibold text-sm md:text-base">Support</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full lg:w-1/3 mt-8 lg:mt-0">
                <div class="w-full max-w-sm bg-black/50 backdrop-blur-md p-6 rounded-2xl z-20 shadow-xl glass-effect">
                    <h3 class="text-xl font-bold text-white mb-4">Trending Products</h3>
                    <div class="slideshow-container">
                        <?php if (empty($trending_products)): ?>
                            <div class="text-center text-white py-8">
                                <i class="fas fa-star text-4xl mb-4"></i>
                                <p class="text-sm">No trending products available right now. Check back soon for exciting updates!</p>
                                <a href="#products" class="mt-4 inline-block px-4 py-2 bg-gold text-black rounded-full text-sm hover:bg-yellow-400 transition-colors">Browse All Products</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($trending_products as $index => $product): ?>
                                <div class="slideshow-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-64 object-cover rounded-lg" loading="lazy">
                                    <div class="absolute bottom-0 left-0 bg-black/70 text-white p-4 w-full rounded-b-lg">
                                        <h4 class="font-bold text-lg"><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p class="text-sm">RWF <?php echo number_format($product['price'], 2); ?></p>
                                        <div class="flex items-center mt-2">
                                            <div class="rating-stars text-sm mr-2">
                                                <?php
                                                $rating = floatval($product['rating']);
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $rating) {
                                                        echo '<i class="fas fa-star text-gold"></i>';
                                                    } elseif ($i - 0.5 <= $rating) {
                                                        echo '<i class="fas fa-star-half-alt text-gold"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star text-gray-400"></i>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <span class="text-gray-400 text-xs">({$product['rating']})</span>
                                        </div>
                                        <div class="mt-3 flex space-x-2">
                                            <button onclick="quickView(<?php echo $product['id']; ?>)" class="px-4 py-1 bg-gold text-black rounded-full text-sm hover:bg-yellow-400 transition-colors">View</button>
                                            <button onclick="addToCart(<?php echo $product['id']; ?>)" class="px-4 py-1 bg-gray-700 text-white rounded-full text-sm hover:bg-gray-600 transition-colors">Add to Cart</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($trending_products)): ?>
                            <div class="slideshow-nav slideshow-prev"><i class="fas fa-chevron-left"></i></div>
                            <div class="slideshow-nav slideshow-next"><i class="fas fa-chevron-right"></i></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce-slow">
            <div class="w-6 h-10 border-2 border-white rounded-full flex justify-center">
                <div class="w-1 h-3 bg-white rounded-full mt-2 animate-pulse"></div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-20 bg-white dark:bg-dark-bg transition-colors duration-300">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h3 class="text-3xl md:text-4xl font-black text-gray-800 dark:text-white mb-6">Our Products</h3>
                <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">Discover our collection of quality appliances</p>
            </div>
            <div class="flex flex-wrap justify-center mb-12 gap-4">
                <button class="filter-btn active px-6 py-3 rounded-full gradient-gold text-white font-bold text-sm shadow-lg transition-all" data-filter="all">
                    <i class="fas fa-th-large mr-2"></i>All Products
                </button>
                <?php foreach ($categories as $category): ?>
                    <button class="filter-btn px-6 py-3 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold hover:bg-gold hover:text-white transition-all duration-300" data-filter="<?php echo htmlspecialchars($category['name']); ?>">
                        <i class="<?php echo htmlspecialchars($category['icon_class']); ?> mr-2"></i><?php echo htmlspecialchars($category['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <div class="flex items-center space-x-4">
                    <select id="sortSelect" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold text-sm dark:bg-gray-700 dark:text-white transition-all">
                        <option value="name">Sort by Name</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="rating">Sort by Rating</option>
                    </select>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span id="productCount"><?php echo $total_products; ?></span> products found
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button id="gridView" class="p-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button id="listView" class="p-2 bg-gold text-white rounded transition-colors">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            <div id="loadingState" class="text-center py-12 hidden">
                <div class="loading-spinner mx-auto mb-4"></div>
                <p class="text-gray-600 dark:text-gray-400">Loading products...</p>
            </div>
            <div id="productsGrid" class="grid grid-cols-1 gap-6"></div>
            <div id="noProductsFound" class="text-center py-12 hidden">
                <i class="fas fa-search text-6xl text-gray-400 mb-4"></i>
                <h4 class="text-xl font-bold text-gray-600 dark:text-gray-400 mb-2">No products found</h4>
                <p class="text-gray-500 dark:text-gray-500">Try adjusting your search or filter criteria</p>
            </div>
            <div class="text-center mt-12 flex justify-center gap-4">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&category=<?php echo isset($_GET['category']) ? htmlspecialchars($_GET['category']) : 'all'; ?>" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gold hover:text-white transition-colors">
                        Previous
                    </a>
                <?php endif; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&category=<?php echo isset($_GET['category']) ? htmlspecialchars($_GET['category']) : 'all'; ?>" class="px-6 py-3 gradient-gold text-white rounded-lg font-bold hover:opacity-90 transition-opacity">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Deals Section -->
    <section id="deals" class="py-20 relative overflow-hidden parallax-section" style="background-image: linear-gradient(135deg, rgba(147,51,234,0.8) 0%, rgba(79,70,229,0.8) 100%), url('https://images.unsplash.com/photo-1607082350899-7e105aa886ae?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
        <div class="relative z-10 container mx-auto px-4 text-center">
            <h3 class="text-3xl md:text-4xl font-black text-white mb-6 animate-pulse">🔥 FLASH DEALS</h3>
            <p class="text-lg text-white mb-12">Limited time offers on premium appliances</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div class="deal-card bg-white/10 backdrop-blur-sm p-6 rounded-2xl border border-white/20 hover:bg-white/20 transition-all duration-300">
                    <div class="text-4xl mb-4">❄️</div>
                    <h4 class="text-xl font-bold text-white mb-4">Refrigerators</h4>
                    <div class="text-2xl font-black text-gold mb-2">30% OFF</div>
                    <p class="text-white">Hisense & Sayona models</p>
                    <button onclick="filterByCategory('refrigerators')" class="mt-4 px-6 py-2 bg-gold text-black rounded-full font-bold hover:bg-yellow-400 transition-colors">
                        Shop Now
                    </button>
                </div>
                <div class="deal-card bg-white/10 backdrop-blur-sm p-6 rounded-2xl border border-white/20 hover:bg-white/20 transition-all duration-300">
                    <div class="text-4xl mb-4">📺</div>
                    <h4 class="text-xl font-bold text-white mb-4">Smart TVs</h4>
                    <div class="text-2xl font-black text-gold mb-2">25% OFF</div>
                    <p class="text-white">All sizes available</p>
                    <button onclick="filterByCategory('tvs')" class="mt-4 px-6 py-2 bg-gold text-black rounded-full font-bold hover:bg-yellow-400 transition-colors">
                        Shop Now
                    </button>
                </div>
                <div class="deal-card bg-white/10 backdrop-blur-sm p-6 rounded-2xl border border-white/20 hover:bg-white/20 transition-all duration-300">
                    <div class="text-4xl mb-4">🏠</div>
                    <h4 class="text-xl font-bold text-white mb-4">Kitchen Appliances</h4>
                    <div class="text-2xl font-black text-gold mb-2">35% OFF</div>
                    <p class="text-white">Blenders, Rice Cookers & More</p>
                    <button onclick="filterByCategory('kitchen')" class="mt-4 px-6 py-2 bg-gold text-black rounded-full font-bold hover:bg-yellow-400 transition-colors">
                        Shop Now
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white dark:bg-dark-bg transition-colors duration-300">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h3 class="text-3xl md:text-4xl font-black text-gray-800 dark:text-white mb-4">Why Choose Us</h3>
                <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">Experience premium service and quality products</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="feature-card text-center p-8 rounded-2xl hover:shadow-2xl transition-all duration-300 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-700">
                    <div class="feature-icon w-20 h-20 gradient-gold rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-shipping-fast text-white text-3xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 dark:text-white mb-3">Free Delivery</h4>
                    <p class="text-gray-600 dark:text-gray-300">On orders above RWF 300,000</p>
                </div>
                <div class="feature-card text-center p-8 rounded-2xl hover:shadow-2xl transition-all duration-300 bg-gradient-to-br from-green-50 to-emerald-100 dark:from-gray-800 dark:to-gray-700">
                    <div class="feature-icon w-20 h-20 gradient-gold rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-shield-alt text-white text-3xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 dark:text-white mb-3">2 Year Warranty</h4>
                    <p class="text-gray-600 dark:text-gray-300">On all products</p>
                </div>
                <div class="feature-card text-center p-8 rounded-2xl hover:shadow-2xl transition-all duration-300 bg-gradient-to-br from-purple-50 to-violet-100 dark:from-gray-800 dark:to-gray-700">
                    <div class="feature-icon w-20 h-20 gradient-gold rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-headset text-white text-3xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 dark:text-white mb-3">24/7 Support</h4>
                    <p class="text-gray-600 dark:text-gray-300">Round-the-clock service</p>
                </div>
                <div class="feature-card text-center p-8 rounded-2xl hover:shadow-2xl transition-all duration-300 bg-gradient-to-br from-yellow-50 to-orange-100 dark:from-gray-800 dark:to-gray-700">
                    <div class="feature-icon w-20 h-20 gradient-gold rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-money-bill-wave text-white text-3xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800 dark:text-white mb-3">Best Prices</h4>
                    <p class="text-gray-600 dark:text-gray-300">Competitive pricing</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h3 class="text-3xl md:text-4xl font-black text-gray-800 dark:text-white mb-6">About MAHIRWE SMART BUSINESS</h3>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
                        Founded by <strong>NYIRAMANA Mary</strong>, we're Rwanda's trusted source for quality appliances. 
                        With years of experience in the industry, we bring you the best in home and business electronics 
                        with exceptional customer service and support.
                    </p>
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div class="text-center">
                            <div class="text-2xl font-black text-gold mb-2">3000+</div>
                            <div class="text-gray-600 dark:text-gray-300">Happy Customers</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-black text-gold mb-2">200+</div>
                            <div class="text-gray-600 dark:text-gray-300">Quality Products</div>
                        </div>
                    </div>
                    <a href="#contact" class="gradient-gold text-white px-8 py-3 rounded-full font-bold text-lg hover:opacity-90 transition-opacity inline-block">
                        Get In Touch
                    </a>
                </div>
                <div class="relative">
                    <div class="bg-gradient-to-r from-gold to-orange-500 rounded-2xl p-1">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" 
                             alt="About Us" class="rounded-2xl shadow-2xl w-full">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-white dark:bg-dark-bg transition-colors duration-300">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h3 class="text-3xl md:text-4xl font-black text-gray-800 dark:text-white mb-6">What Our Customers Say</h3>
                <p class="text-lg text-gray-600 dark:text-gray-300">Real reviews from real customers</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="testimonial-card bg-gray-50 dark:bg-dark-card p-6 rounded-2xl shadow-lg">
                    <div class="rating-stars mb-4 text-gold">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 mb-4 italic">"Excellent service and fast delivery! The product quality exceeded my expectations."</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gold rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <div class="font-bold text-gray-800 dark:text-white">Jean Claude</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">Kigali</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card bg-gray-50 dark:bg-dark-card p-6 rounded-2xl shadow-lg">
                    <div class="rating-stars mb-4 text-gold">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 mb-4 italic">"Great products and excellent customer service! Will definitely shop here again."</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gold rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <div class="font-bold text-gray-800 dark:text-white">Marie Louise</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">Musanze</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card bg-gray-50 dark:bg-dark-card p-6 rounded-2xl shadow-lg">
                    <div class="rating-stars mb-4 text-gold">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 mb-4 italic">"Best prices in Rwanda! The warranty gives me peace of mind. Highly recommended."</p>
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gold rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <div class="font-bold text-gray-800 dark:text-white">Paul Kagame Jr</div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">Huye</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-20 bg-gradient-to-r from-gold to-orange-500 animate-gradient-shift" style="background-size: 200% 200%;">
        <div class="container mx-auto px-4 text-center">
            <h3 class="text-3xl font-black text-white mb-4">Stay Updated!</h3>
            <p class="text-lg text-white/90 mb-8 max-w-2xl mx-auto">Subscribe for the latest deals and offers</p>
            <div class="max-w-md mx-auto">
                <div class="flex">
                    <input type="email" id="newsletterEmail" placeholder="Enter your email" 
                           class="flex-1 px-4 py-3 rounded-l-lg border-0 focus:outline-none focus:ring-2 focus:ring-white text-sm transition-all">
                    <button onclick="subscribeNewsletter()" class="px-6 py-3 bg-white text-gold rounded-r-lg font-bold hover:bg-gray-100 transition-colors">
                        Subscribe
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gray-900 text-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h3 class="text-3xl md:text-4xl font-black mb-6">Get In Touch</h3>
                <p class="text-lg max-w-2xl mx-auto">Contact NYIRAMANA Mary for the best deals!</p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <div class="space-y-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 gradient-gold rounded-full flex items-center justify-center mr-4">
                            <i class="fab fa-whatsapp text-white text-xl"></i>
                        </div>
                        <div>
                            <div class="font-bold">WhatsApp</div>
                            <div class="text-gray-300">+250 788 813 118</div>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="w-12 h-12 gradient-gold rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-envelope text-white text-xl"></i>
                        </div>
                        <div>
                            <div class="font-bold">Email</div>
                            <div class="text-gray-300">mahirwerene321@gmail.com</div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8">
                        <h4 class="text-xl font-bold mb-6">Quick Contact</h4>
                        <a href="https://wa.me/250788813118?text=Hello%20MAHIRWE%20SMART%20BUSINESS!%20I%20would%20like%20to%20know%20more%20about%20your%20home%20appliances." 
                           class="gradient-gold text-white px-8 py-4 rounded-full font-bold text-lg hover:opacity-90 transition-opacity inline-flex items-center">
                            <i class="fab fa-whatsapp mr-3"></i>Message Mrs. Mary
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 gradient-gold rounded-full flex items-center justify-center">
                            <i class="fas fa-bolt text-white"></i>
                        </div>
                        <div>
                            <h4 class="text-xl font-black gradient-text">MAHIRWE</h4>
                            <p class="text-sm text-gray-300">SMART BUSINESS</p>
                        </div>
                    </div>
                    <p class="text-gray-300 mb-4">Your trusted partner for quality appliances.</p>
                </div>
                <div>
                    <h5 class="font-bold text-lg mb-4 text-gold">Quick Links</h5>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="#home" class="hover:text-gold transition-colors">Home</a></li>
                        <li><a href="#products" class="hover:text-gold transition-colors">Products</a></li>
                        <li><a href="#categories" class="hover:text-gold transition-colors">Categories</a></li>
                        <li><a href="#contact" class="hover:text-gold transition-colors">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-bold text-lg mb-4 text-gold">Services</h5>
                    <ul class="space-y-2 text-gray-300">
                        <li>Free Installation</li>
                        <li>2-Year Warranty</li>
                        <li>24/7 Support</li>
                        <li>Free Delivery (300k+)</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-6 text-center text-gray-400 text-sm">
                <p>&copy; 2025 MAHIRWE SMART BUSINESS. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // JavaScript code remains the same as in the original
        // Only the styling has been enhanced
        let cart = [];
        let isGridView = true;
        let chatbotIsOpen = false;
        let currentProducts = <?php echo json_encode($products); ?>.map(p => ({
            id: p.id,
            name: p.name,
            price: `RWF ${parseFloat(p.price).toLocaleString()}`,
            category: p.category_name,
            image: p.image,
            rating: parseFloat(p.rating),
            description: p.description,
            features: p.features ? p.features.split(',').map(f => f.trim()) : []
        }));
        let debounceTimeout;
        let lastQueryType = '';
        let lastSearchQuery = '';
        let currentPage = 1;
        let chatbotProductPage = 1;
        let chatbotProducts = [];
        let chatbotTotalPages = 1;
        let notifications = [];

        function initializeTheme() {
            const savedTheme = localStorage.getItem('theme');
            const initialTheme = savedTheme || 'light';
            if (initialTheme === 'dark') {
                document.documentElement.classList.add('dark');
                updateToggleIcons(true);
            } else {
                document.documentElement.classList.remove('dark');
                updateToggleIcons(false);
            }
            document.getElementById('darkModeToggle').addEventListener('click', () => {
                const isDarkMode = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
                updateToggleIcons(isDarkMode);
            });
        }

        function updateToggleIcons(isDarkMode) {
            const moonIcon = document.querySelector('#darkModeToggle .fa-moon');
            const sunIcon = document.querySelector('#darkModeToggle .fa-sun');
            moonIcon.classList.toggle('hidden', isDarkMode);
            sunIcon.classList.toggle('hidden', !isDarkMode);
        }

        function showNotification(message, type = 'success') {
            const notificationContainer = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            notification.className = `flex items-center text-white px-4 py-3 rounded-lg shadow-lg mb-2 notification-enter ${type === 'success' ? 'bg-green-500' : type === 'warning' ? 'bg-yellow-500' : 'bg-red-500'}`;
            notification.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'warning' ? 'fa-exclamation-circle' : 'fa-times-circle'} mr-2"></i>
                <span>${message}</span>
            `;
            notificationContainer.appendChild(notification);
            notifications.push({ message, type, timestamp: new Date() });
            updateNotificationList();
            setTimeout(() => {
                notification.classList.remove('notification-enter');
                notification.classList.add('notification-enter-active');
            }, 10);
            setTimeout(() => {
                notification.classList.remove('notification-enter-active');
                notification.classList.add('notification-exit');
                setTimeout(() => {
                    notification.classList.add('notification-exit-active');
                    setTimeout(() => {
                        notification.remove();
                        notifications = notifications.filter(n => n.message !== message || n.timestamp !== notifications.find(nn => nn.message === message).timestamp);
                        updateNotificationList();
                    }, 300);
                }, 10);
            }, 5000);
        }

        function updateNotificationList() {
            const notificationList = document.getElementById('notificationList');
            const noNotifications = document.getElementById('noNotifications');
            const notificationCount = document.getElementById('notificationCount');
            
            if (notifications.length === 0) {
                noNotifications.style.display = 'block';
                notificationList.innerHTML = '';
                notificationCount.classList.add('hidden');
                return;
            }
            
            noNotifications.style.display = 'none';
            notificationCount.classList.remove('hidden');
            notificationCount.textContent = notifications.length;
            
            notificationList.innerHTML = notifications.map(n => `
                <div class="p-3 flex items-center space-x-3 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="fas ${n.type === 'success' ? 'fa-check-circle text-green-500' : n.type === 'warning' ? 'fa-exclamation-circle text-yellow-500' : 'fa-times-circle text-red-500'}"></i>
                    <span class="text-sm text-gray-700 dark:text-gray-300">${n.message}</span>
                </div>
            `).join('');
        }

        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('hidden');
        }

        function clearAllNotifications() {
            notifications = [];
            updateNotificationList();
            showNotification('All notifications cleared', 'warning');
        }

        function addToCart(productId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const product = currentProducts.find(p => p.id === productId);
                    const existingItem = cart.find(item => item.id === productId);
                    if (existingItem) {
                        existingItem.quantity += 1;
                    } else {
                        cart.push({...product, quantity: 1});
                    }
                    updateCartBadge();
                    saveCartToStorage();
                    renderCartItems();
                    showNotification('Product added to cart!', 'success');
                } else {
                    showNotification('Failed to add product to cart', 'error');
                }
            })
            .catch(() => showNotification('Error adding to cart', 'error'));
        }

        function removeFromCart(productId) {
            fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cart = cart.filter(item => item.id !== productId);
                    updateCartBadge();
                    saveCartToStorage();
                    renderCartItems();
                    showNotification('Product removed from cart', 'warning');
                }
            })
            .catch(() => showNotification('Error removing from cart', 'error'));
        }

        function updateCartQuantity(productId, quantity) {
            fetch('update_cart_quantity.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (quantity <= 0) {
                        removeFromCart(productId);
                    } else {
                        const item = cart.find(item => item.id === productId);
                        if (item) item.quantity = quantity;
                        updateCartBadge();
                        saveCartToStorage();
                        renderCartItems();
                    }
                }
            })
            .catch(() => showNotification('Error updating quantity', 'error'));
        }

        function clearCart() {
            if (cart.length === 0) return;
            showCustomModal('Clear Cart', 'Are you sure you want to clear your cart?', () => {
                fetch('clear_cart.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        cart = [];
                        updateCartBadge();
                        saveCartToStorage();
                        renderCartItems();
                        showNotification('Cart cleared', 'warning');
                    }
                })
                .catch(() => showNotification('Error clearing cart', 'error'));
            });
        }

        function renderCartItems() {
            fetch('get_cart.php')
            .then(response => response.json())
            .then(data => {
                cart = data.map(item => ({
                    ...item,
                    price: `RWF ${parseFloat(item.price).toLocaleString()}`
                }));
                const cartItems = document.getElementById('cartItems');
                const cartTotal = document.getElementById('cartTotal');
                
                if (cart.length === 0) {
                    cartItems.innerHTML = `
                        <div class="text-center py-12">
                            <i class="fas fa-shopping-cart text-6xl text-gray-400 mb-4"></i>
                            <h4 class="text-xl font-bold text-gray-600 dark:text-gray-400 mb-2">Your cart is empty</h4>
                            <p class="text-gray-500 dark:text-gray-500">Add some quality appliances to get started!</p>
                        </div>
                    `;
                    cartTotal.textContent = 'RWF 0';
                    return;
                }

                cartItems.innerHTML = cart.map(item => `
                    <div class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded-lg" loading="lazy">
                        <div class="flex-1">
                            <h5 class="font-semibold text-gray-800 dark:text-white">${item.name}</h5>
                            <p class="text-gold font-bold">${item.price}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="updateCartQuantity(${item.id}, ${item.quantity - 1})" class="w-8 h-8 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-500">
                                <i class="fas fa-minus text-sm"></i>
                            </button>
                            <span class="w-8 text-center font-semibold">${item.quantity}</span>
                            <button onclick="updateCartQuantity(${item.id}, ${item.quantity + 1})" class="w-8 h-8 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-500">
                                <i class="fas fa-plus text-sm"></i>
                            </button>
                        </div>
                        <button onclick="removeFromCart(${item.id})" class="text-red-500 hover:text-red-700 transition-colors">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `).join('');

                cartTotal.textContent = `RWF ${calculateCartTotal().toLocaleString()}`;
            })
            .catch(() => showNotification('Error loading cart', 'error'));
        }

        function calculateCartTotal() {
            return cart.reduce((total, item) => {
                const price = parseFloat(item.price.replace(/[^\d]/g, ''));
                return total + (price * item.quantity);
            }, 0);
        }

        function toggleCart() {
            const cartModal = document.getElementById('cartModal');
            cartModal.classList.toggle('hidden');
            if (!cartModal.classList.contains('hidden')) {
                renderCartItems();
            }
        }

        function closeCart() {
            document.getElementById('cartModal').classList.add('hidden');
        }

        function showPaymentOptions() {
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        function checkoutWhatsApp() {
            if (cart.length === 0) {
                showNotification('Your cart is empty!', 'warning');
                return;
            }
            const message = `Hello Mrs. NYIRAMANA Mary! 🛒\n\nI would like to order:\n\n${cart.map(item => `📦 ${item.name}\n💰 ${item.price} x ${item.quantity}\n`).join('\n')}\n💳 Total: RWF ${calculateCartTotal().toLocaleString()}\n\nPlease confirm availability and delivery details.`;
            window.open(`https://wa.me/250788813118?text=${encodeURIComponent(message)}`, '_blank');
            showNotification('Opening WhatsApp...', 'success');
        }

        function filterByCategory(category) {
            loadProducts(category, '', 1, false);
            scrollToSection('products');
        }

        function loadProducts(category = 'all', search = '', page = 1, append = false) {
            const loadingState = document.getElementById('loadingState');
            loadingState.classList.remove('hidden');
            
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            url.searchParams.set('category', category);
            if (search) url.searchParams.set('search', search);
            else url.searchParams.delete('search');
            window.history.pushState({}, '', url);

            fetch(`get_products.php?page=${page}&category=${encodeURIComponent(category)}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                currentProducts = data.products.map(p => ({
                    id: p.id,
                    name: p.name,
                    price: `RWF ${parseFloat(p.price).toLocaleString()}`,
                    category: p.category_name,
                    image: p.image,
                    rating: parseFloat(p.rating),
                    description: p.description,
                    features: p.features ? p.features.split(',').map(f => f.trim()) : []
                }));
                currentPage = page;
                document.getElementById('productCount').textContent = data.total_products;
                
                const noProductsFound = document.getElementById('noProductsFound');
                if (currentProducts.length === 0) {
                    noProductsFound.classList.remove('hidden');
                } else {
                    noProductsFound.classList.add('hidden');
                }

                renderProducts();
                updatePagination(data.total_pages, category, search);
            })
            .catch(() => showNotification('Error loading products', 'error'))
            .finally(() => loadingState.classList.add('hidden'));
        }

        function updatePagination(totalPages, category, search) {
            const paginationContainer = document.querySelector('.text-center.mt-12');
            paginationContainer.innerHTML = '';
            
            if (currentPage > 1) {
                paginationContainer.innerHTML += `
                    <a href="?page=${currentPage - 1}&category=${encodeURIComponent(category)}&search=${encodeURIComponent(search)}" 
                       class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gold hover:text-white transition-colors">
                        Previous
                    </a>
                `;
            }
            
            if (currentPage < totalPages) {
                paginationContainer.innerHTML += `
                    <a href="?page=${currentPage + 1}&category=${encodeURIComponent(category)}&search=${encodeURIComponent(search)}" 
                       class="px-6 py-3 gradient-gold text-white rounded-lg font-bold hover:opacity-90 transition-opacity">
                        Next
                    </a>
                `;
            }
        }

        function renderProducts(productsToShow = currentProducts) {
            const productsGrid = document.getElementById('productsGrid');
            const loadingState = document.getElementById('loadingState');
            const noProductsFound = document.getElementById('noProductsFound');
            const productCount = document.getElementById('productCount');
            
            loadingState.classList.remove('hidden');
            productsGrid.innerHTML = '';
            noProductsFound.classList.add('hidden');

            setTimeout(() => {
                loadingState.classList.add('hidden');
                
                if (productsToShow.length === 0) {
                    noProductsFound.classList.remove('hidden');
                    productCount.textContent = '0';
                    return;
                }

                productCount.textContent = productsToShow.length;

                productsGrid.className = isGridView ? 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6' : 'grid grid-cols-1 gap-6';

                productsToShow.forEach((product, index) => {
                    const productCard = document.createElement('div');
                    productCard.className = `product-card ${isGridView ? 'grid-view' : 'list-view'} rounded-2xl shadow-lg overflow-hidden bg-gray-50 dark:bg-dark-card animate-slide-up`;
                    productCard.style.animationDelay = `${index * 0.1}s`;
                    
                    productCard.innerHTML = isGridView ? `
                        <div class="w-full h-64 overflow-hidden">
                            <img src="${product.image}" alt="${product.name}" class="w-full h-full object-cover rounded-t-2xl transition-transform duration-500" loading="lazy">
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2 rating-container">
                                <div class="rating-stars text-sm">
                                    ${generateStars(product.rating)}
                                </div>
                                <span class="text-gray-500 dark:text-gray-400 text-sm">(${product.rating})</span>
                            </div>
                            <h4 class="font-bold text-lg mb-2 text-gray-800 dark:text-white">${product.name}</h4>
                            <p class="text-gray-600 dark:text-gray-300 mb-3 line-clamp-2">${product.description}</p>
                            <div class="text-2xl font-black text-gold mb-4">${product.price}</div>
                            <div class="flex space-x-2 action-container">
                                <button onclick="quickView(${product.id})" class="p-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:border-gold hover:text-gold transition-colors">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="addToCart(${product.id})" class="p-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:border-gold hover:text-gold transition-colors">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                <button onclick="contactWhatsApp('${product.name}', '${product.price}')" class="gradient-gold text-white py-2 px-4 rounded-lg font-bold hover:opacity-90 transition-opacity">
                                    <i class="fab fa-whatsapp mr-2"></i>Order
                                </button>
                            </div>
                        </div>
                    ` : `
                        <div class="flex">
                            <div class="w-48 h-48 flex-shrink-0 overflow-hidden">
                                <img src="${product.image}" alt="${product.name}" class="w-full h-full object-cover rounded-l-2xl transition-transform duration-500" loading="lazy">
                            </div>
                            <div class="flex-1 p-6 flex flex-col">
                                <div class="flex items-center justify-between mb-2 rating-container">
                                    <div class="rating-stars text-sm">
                                        ${generateStars(product.rating)}
                                    </div>
                                    <span class="text-gray-500 dark:text-gray-400 text-sm">(${product.rating})</span>
                                </div>
                                <h4 class="font-bold text-lg mb-2 text-gray-800 dark:text-white">${product.name}</h4>
                                <p class="text-gray-600 dark:text-gray-300 mb-3">${product.description}</p>
                                <div class="text-2xl font-black text-gold mb-4">${product.price}</div>
                                <div class="flex space-x-2 mt-auto action-container">
                                    <button onclick="quickView(${product.id})" class="p-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:border-gold hover:text-gold transition-colors">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="addToCart(${product.id})" class="p-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:border-gold hover:text-gold transition-colors">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                    <button onclick="contactWhatsApp('${product.name}', '${product.price}')" class="gradient-gold text-white py-2 px-4 rounded-lg font-bold hover:opacity-90 transition-opacity">
                                        <i class="fab fa-whatsapp mr-2"></i>Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    productsGrid.appendChild(productCard);
                });
            }, 300);
        }

        function generateStars(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    stars += '<i class="fas fa-star text-gold"></i>';
                } else if (i - 0.5 <= rating) {
                    stars += '<i class="fas fa-star-half-alt text-gold"></i>';
                } else {
                    stars += '<i class="far fa-star text-gray-400"></i>';
                }
            }
            return stars;
        }

        function quickView(productId) {
            fetch(`get_product.php?id=${productId}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(product => {
                const features = product.features ? product.features.split(',').map(f => f.trim()) : [];
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="modal-content w-full max-w-4xl p-8 bg-white dark:bg-dark-card rounded-2xl shadow-2xl animate-zoom-in">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Quick View</h3>
                            <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-2xl transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <img src="${product.image}" alt="${product.name}" class="w-full h-96 object-cover rounded-lg" loading="lazy">
                            </div>
                            <div>
                                <h4 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">${product.name}</h4>
                                <div class="flex items-center mb-4 rating-container">
                                    <div class="rating-stars text-sm">
                                        ${generateStars(parseFloat(product.rating))}
                                    </div>
                                    <span class="text-gray-500 dark:text-gray-400 text-sm ml-2">(${product.rating})</span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">${product.description}</p>
                                <div class="text-3xl font-black text-gold mb-4">RWF ${parseFloat(product.price).toLocaleString()}</div>
                                <div class="mb-4">
                                    <h5 class="font-semibold text-gray-800 dark:text-white mb-2">Features</h5>
                                    <ul class="list-disc list-inside text-gray-600 dark:text-gray-300">
                                        ${features.length > 0 ? features.map(feature => `<li>${feature}</li>`).join('') : '<li>No features listed</li>'}
                                    </ul>
                                </div>
                                <div class="flex space-x-3 action-container">
                                    <button onclick="addToCart(${product.id})" class="flex-1 gradient-gold text-white py-3 px-6 rounded-lg font-bold hover:opacity-90 transition-opacity">
                                        <i class="fas fa-shopping-cart mr-2"></i>Add to Cart
                                    </button>
                                    <button onclick="contactWhatsApp('${product.name}', 'RWF ${parseFloat(product.price).toLocaleString()}')" class="flex-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 py-3 px-6 rounded-lg font-bold hover:bg-gold hover:text-white transition-colors">
                                        <i class="fab fa-whatsapp mr-2"></i>Order via WhatsApp
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            })
            .catch(error => {
                console.error('Error in quickView:', error);
                showNotification('Error loading product details', 'error');
            });
        }

        function contactWhatsApp(name, price) {
            const message = `Hello Mrs. NYIRAMANA Mary! I'm interested in:\n📦 ${name}\n💰 ${price}\nPlease provide more details and confirm availability.`;
            window.open(`https://wa.me/250788813118?text=${encodeURIComponent(message)}`, '_blank');
            showNotification('Opening WhatsApp...', 'success');
        }

        function subscribeNewsletter() {
            const emailInput = document.getElementById('newsletterEmail');
            const email = emailInput.value.trim();
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showNotification('Please enter a valid email address', 'error');
                return;
            }
            
            fetch('subscribe_newsletter.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `email=${encodeURIComponent(email)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Subscribed successfully!', 'success');
                    emailInput.value = '';
                } else {
                    showNotification(data.message || 'Failed to subscribe', 'error');
                }
            })
            .catch(() => showNotification('Error subscribing to newsletter', 'error'));
        }

        function scrollToSection(sectionId) {
            document.getElementById(sectionId).scrollIntoView({ behavior: 'smooth' });
        }

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function toggleChatbot() {
            const chatbotContainer = document.getElementById('chatbotContainer');
            const chatbotFloat = document.querySelector('.chatbot-float');
            chatbotIsOpen = !chatbotIsOpen;
            chatbotContainer.classList.toggle('hidden', !chatbotIsOpen);
            chatbotFloat.classList.toggle('hidden', chatbotIsOpen);
            
            if (chatbotIsOpen && !document.getElementById('chatbotMessages').children.length) {
                addChatMessage('bot', 'Welcome to MAHIRWE SMART BUSINESS! How can I assist you today? Try asking about products, prices, delivery, or warranties.');
            }
        }

        function addChatMessage(sender, message) {
            const messagesContainer = document.getElementById('chatbotMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `chatbot-message ${sender} p-3 my-2 max-w-[80%] ${sender === 'user' ? 'ml-auto bg-gold text-gray-800' : 'mr-auto bg-gray-100 dark:bg-gray-800'}`;
            messageDiv.innerHTML = `<p class="text-sm">${message}</p>`;
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function sendChatMessage() {
            const input = document.getElementById('chatbotInput');
            const message = input.value.trim();
            if (!message) return;
            
            addChatMessage('user', message);
            input.value = '';
            
            if (message.toLowerCase().includes('price') || message.toLowerCase().includes('cost')) {
                fetchChatbotProducts(''); // Fetch all products for price queries
            } else if (message.toLowerCase().includes('delivery')) {
                addChatMessage('bot', 'We offer free delivery on orders above RWF 300,000. Delivery typically takes 1-3 business days within Rwanda.');
            } else if (message.toLowerCase().includes('warranty')) {
                addChatMessage('bot', 'All our products come with a 2-year warranty. Let me know if you need details on a specific item!');
            } else {
                fetchChatbotProducts(message);
            }
        }

        function quickQuestion(type) {
            const questions = {
                prices: 'What are the prices of your products?',
                delivery: 'Tell me about your delivery options',
                warranty: 'What is the warranty policy?'
            };
            document.getElementById('chatbotInput').value = questions[type];
            sendChatMessage();
        }

        function fetchChatbotProducts(query) {
            fetch(`get_products.php?search=${encodeURIComponent(query)}&page=${chatbotProductPage}`)
            .then(response => response.json())
            .then(data => {
                chatbotProducts = data.products.map(p => ({
                    id: p.id,
                    name: p.name,
                    price: `RWF ${parseFloat(p.price).toLocaleString()}`,
                    category: p.category_name,
                    image: p.image,
                    rating: parseFloat(p.rating),
                    description: p.description
                }));
                chatbotTotalPages = data.total_pages;

                let response = query ? 'Here are some products matching your query:<br>' : 'Here are some of our products:<br>';
                if (chatbotProducts.length === 0) {
                    response = query ? 'No products found. Try a different search term!' : 'No products available at the moment.';
                } else {
                    const start = (chatbotProductPage - 1) * 3;
                    const end = start + 3;
                    const productsToShow = chatbotProducts.slice(start, end);
                    response += productsToShow.map(p => `
                        <div class="flex items-center p-2 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
                            <img src="${p.image}" alt="${p.name}" class="w-16 h-16 object-cover rounded-lg mr-3">
                            <div>
                                <strong>${p.name}</strong><br>
                                ${p.price} | ${p.category}<br>
                                <button onclick="quickView(${p.id})" class="text-xs bg-gold text-white px-2 py-1 rounded">View Details</button>
                                <button onclick="addToCart(${p.id})" class="text-xs bg-gray-600 text-white px-2 py-1 rounded">Add to Cart</button>
                            </div>
                        </div>
                    `).join('');
                    if (chatbotTotalPages > 1) {
                        response += `
                            <div class="flex justify-between mt-2">
                                ${chatbotProductPage > 1 ? `<button onclick="chatbotProductPage--; fetchChatbotProducts('${query}')" class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded">Previous</button>` : '<span></span>'}
                                ${chatbotProductPage < chatbotTotalPages ? `<button onclick="chatbotProductPage++; fetchChatbotProducts('${query}')" class="px-2 py-1 bg-gold text-white rounded">Next</button>` : '<span></span>'}
                            </div>
                        `;
                    }
                }
                addChatMessage('bot', response);
            })
            .catch(() => addChatMessage('bot', 'Sorry, I couldn’t fetch products right now. Try again later!'));
        }

        function saveCartToStorage() {
            try {
                localStorage.setItem('mahirwe-cart', JSON.stringify(cart));
            } catch (e) {
                console.log('Storage not available');
            }
        }

        function loadCartFromStorage() {
            try {
                const savedCart = localStorage.getItem('mahirwe-cart');
                if (savedCart) {
                    cart = JSON.parse(savedCart);
                    updateCartBadge();
                }
            } catch (e) {
                console.log('Storage not available');
            }
        }

        function updateCartBadge() {
            const badge = document.getElementById('cartBadge');
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            badge.textContent = totalItems;
            badge.classList.toggle('hidden', totalItems === 0);
        }

        function showCustomModal(title, message, confirmCallback) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white dark:bg-dark-card p-6 rounded-lg shadow-lg max-w-sm w-full mx-4 animate-zoom-in">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">${title}</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">${message}</p>
                    <div class="flex justify-end space-x-3">
                        <button class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors" onclick="this.closest('.fixed').remove()">Cancel</button>
                        <button class="px-4 py-2 bg-red-500 text-white hover:bg-red-600 rounded transition-colors" onclick="this.closest('.fixed').remove(); (${confirmCallback})()">Confirm</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        function initializeSlideshow() {
            const slideshowContainer = document.querySelector('.slideshow-container');
            if (!slideshowContainer) return;

            const slides = slideshowContainer.querySelectorAll('.slideshow-slide');
            const prevBtn = slideshowContainer.querySelector('.slideshow-prev');
            const nextBtn = slideshowContainer.querySelector('.slideshow-next');
            let currentSlide = 0;

            if (slides.length === 0) return;

            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.style.opacity = i === index ? '1' : '0';
                    slide.classList.toggle('active', i === index);
                });
            }

            showSlide(currentSlide); // Ensure initial slide is visible

            prevBtn?.addEventListener('click', () => {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(currentSlide);
            });

            nextBtn?.addEventListener('click', () => {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            });

            setInterval(() => {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            }, 5000);
        }

        function initializeEventListeners() {
            document.getElementById('gridView').addEventListener('click', () => {
                isGridView = true;
                document.getElementById('gridView').classList.add('bg-gold', 'text-white');
                document.getElementById('gridView').classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
                document.getElementById('listView').classList.remove('bg-gold', 'text-white');
                document.getElementById('listView').classList.add('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
                renderProducts();
            });

            document.getElementById('listView').addEventListener('click', () => {
                isGridView = false;
                document.getElementById('listView').classList.add('bg-gold', 'text-white');
                document.getElementById('listView').classList.remove('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
                document.getElementById('gridView').classList.remove('bg-gold', 'text-white');
                document.getElementById('gridView').classList.add('bg-gray-200', 'text-gray-700', 'dark:bg-gray-700', 'dark:text-gray-300');
                renderProducts();
            });

            document.getElementById('sortSelect').addEventListener('change', () => {
                const sortValue = document.getElementById('sortSelect').value;
                currentProducts.sort((a, b) => {
                    if (sortValue === 'name') return a.name.localeCompare(b.name);
                    if (sortValue === 'price-low') return parseFloat(a.price.replace(/[^\d]/g, '')) - parseFloat(b.price.replace(/[^\d]/g, ''));
                    if (sortValue === 'price-high') return parseFloat(b.price.replace(/[^\d]/g, '')) - parseFloat(a.price.replace(/[^\d]/g, ''));
                    if (sortValue === 'rating') return b.rating - a.rating;
                });
                renderProducts();
            });

            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active', 'gradient-gold', 'text-white'));
                    btn.classList.add('active', 'gradient-gold', 'text-white');
                    lastQueryType = 'category';
                    loadProducts(btn.dataset.filter, lastSearchQuery, 1);
                });
            });

            const searchInputs = [document.getElementById('searchInput'), document.getElementById('mobileSearchInput')];
            searchInputs.forEach(input => {
                input.addEventListener('input', () => {
                    clearTimeout(debounceTimeout);
                    debounceTimeout = setTimeout(() => {
                        lastSearchQuery = input.value.trim();
                        lastQueryType = 'search';
                        loadProducts(document.querySelector('.filter-btn.active').dataset.filter, lastSearchQuery, 1);
                    }, 500);
                });
            });

            document.getElementById('mobileMenuBtn').addEventListener('click', () => {
                const mobileMenu = document.getElementById('mobileMenu');
                mobileMenu.classList.toggle('hidden');
                const icon = document.querySelector('#mobileMenuBtn .fas');
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-times');
            });

            document.getElementById('notificationBell')?.addEventListener('click', toggleNotifications);

            window.addEventListener('scroll', () => {
                const scrollTopBtn = document.getElementById('scrollTopBtn');
                scrollTopBtn.classList.toggle('opacity-0', window.scrollY < 200);
                scrollTopBtn.classList.toggle('opacity-100', window.scrollY >= 200);
            });

            document.getElementById('chatbotInput').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') sendChatMessage();
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            initializeTheme();
            loadCartFromStorage();
            renderProducts();
            initializeSlideshow();
            initializeEventListeners();
        });
    </script>
</body>
</html>