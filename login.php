<?php
// login.php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid password.';
        }
    } else {
        $error = 'Email not found.';
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MAHIRWE SMART BUSINESS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #FFD700;
            --gold-dark: #B8860B;
            --gold-light: #ffcc00ff;
            --dark-bg: #0f0f0f;
            --dark-card: #1a1a1a;
            --dark-border: #333333;
        }

        * {
            font-family: 'Inter', system-ui, sans-serif;
        }

        body {
             background-color: var(--dark-card);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            transition: background-color 0.3s;
        }

        body.dark {
            background-color: var(--dark-bg);
        }

        .container {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            max-width: 28rem;
            width: 100%;
            animation: slideUp 0.6s ease-out;
        }

        .dark .container {
            background-color: var(--dark-card);
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .logo-container {
            width: 3rem;
            height: 3rem;
            background: linear-gradient(45deg, var(--gold), var(--gold-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .title {
            font-size: 1.875rem;
            font-weight: 900;
            color: #1f2937;
        }

        .dark .title {
            color: white;
        }

        .subtitle {
            font-size: 0.875rem;
            color: #4b5563;
        }

        .dark .subtitle {
            color: #d1d5db;
        }

        .error-message {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .dark .error-message {
            background-color: rgba(220, 38, 38, 0.1);
            color: #f87171;
        }

        .error-message i {
            margin-right: 0.5rem;
        }

        .input-container {
            position: relative;
            margin-bottom: 1.5rem;
        }

        input {
            width: 80%;
            padding: 0.75rem 3rem 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.5);
        }

        .dark input {
            background-color: #374151;
            color: white;
            border-color: var(--dark-border);
        }

        .input-icon {
            position: absolute;
            right: 3rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        button {
            width: 95%;
            background: linear-gradient(45deg, var(--gold), var(--gold-dark));
            color: white;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 1.125rem;
            transition: opacity 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        button:hover {
            opacity: 0.9;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        button i {
            margin-right: 0.5rem;
        }

        .loader {
            width: 2.5rem;
            height: 2.5rem;
            border: 4px solid var(--gold-light);
            border-top-color: var(--gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 1rem auto;
        }

        .hidden {
            display: none;
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #4b5563;
            font-size: 1rem;
        }

        .dark .register-link {
            color: #d1d5db;
        }

        .register-link a {
            color: var(--gold);
            font-weight: 600;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes slideUp {
            0% { transform: translateY(20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <i class="fas fa-bolt text-white text-xl"></i>
            </div>
            <div>
                <h2 class="title">Login</h2>
                <p class="subtitle">MAHIRWE SMART BUSINESS</p>
            </div>
        </div>
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        <form id="loginForm" method="POST">
            <div class="input-container">
                <input type="email" name="email" placeholder="Email" required>
                <i class="fas fa-envelope input-icon"></i>
            </div>
            <div class="input-container">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fas fa-lock input-icon"></i>
            </div>
            <button type="submit" id="loginButton">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
            <div id="loader" class="loader hidden"></div>
        </form>
        <p class="register-link">
            Don't have an account? 
            <a href="register.php">Register</a>
        </p>
    </div>
    <script>
        function initializeTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function() {
            const loginButton = document.getElementById('loginButton');
            const loader = document.getElementById('loader');
            loginButton.disabled = true;
            loginButton.classList.add('opacity-50', 'cursor-not-allowed');
            loader.classList.remove('hidden');
        });

        document.addEventListener('DOMContentLoaded', initializeTheme);
    </script>
</body>
</html>