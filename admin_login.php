<?php
session_start();
require_once 'config.php';

// Ensure database connection is valid
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Username and password are required";
    } else {
        // Prepare statement with error handling
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        if (!$stmt) {
            $error = "Database query preparation failed: " . $conn->error;
        } else {
            $stmt->bind_param("s", $username);
            if (!$stmt->execute()) {
                $error = "Query execution failed: " . $stmt->error;
            } else {
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    $error = "No user found with username: $username";
                } else {
                    $admin = $result->fetch_assoc();
                    if (password_verify($password, $admin['password'])) {
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_username'] = $admin['username'];
                        header('Location: admin.php');
                        exit;
                    } else {
                        $error = "Incorrect password";
                    }
                }
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MAHIRWE SMART BUSINESS</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* CSS Variables */
        :root {
            --gold: #FFD700;
            --gold-dark: #B8860B;
            --gold-light: #FFF8DC;
            --bg-light: #FFF3E0;
            --bg-dark: #0f0f0f;
            --card-light: #FFFFFF;
            --card-dark: #1a1a1a;
            --text-light: #000000; /* Black for light mode */
            --text-dark: #FFFFFF; /* White for dark mode */
            --border-light: #d1d5db;
            --border-dark: #333333;
        }

        /* Base Styles */
        * {
            font-family: 'Inter', system-ui, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(to bottom, var(--bg-light), #FFE0B2);
            color: var(--text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            transition: background-color 0.3s, color 0.3s;
        }

        body.dark {
            background: linear-gradient(to bottom, var(--bg-dark), #1F2937);
            color: var(--text-dark);
        }

        /* Utility Classes */
        .gradient-gold {
            background: linear-gradient(45deg, var(--gold), var(--gold-dark));
        }

        .gradient-text {
            background: linear-gradient(45deg, var(--gold), var(--gold-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .animate-slide-up {
            animation: slideUp 0.6s ease-out;
        }

        .animate-glow {
            animation: glow 1.5s ease-in-out infinite alternate;
        }

        @keyframes slideUp {
            0% { transform: translateY(20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        @keyframes glow {
            from { box-shadow: 0 0 8px rgba(255,215,0,0.4); }
            to { box-shadow: 0 0 20px rgba(255,215,0,0.8); }
        }

        /* Container */
        .container {
            background-color: var(--card-light);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            max-width: 28rem;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
        }

        .dark .container {
            background-color: var(--card-dark);
        }

        .container:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
            transform: translateY(-4px);
        }

        /* Header */
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
            color: var(--text-dark);
        }

        .subtitle {
            font-size: 0.875rem;
            font-weight: 600;
            color: #4b5563;
        }

        .dark .subtitle {
            color: #d1d5db;
        }

        /* Error Message */
        .error-message {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .dark .error-message {
            background-color: rgba(220, 38, 38, 0.1);
            color: #f87171;
        }

        .error-message i {
            margin-right: 0.5rem;
        }

        /* Form */
        .form-group {
            margin-bottom: 1.5rem;
            width: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        input {
            width: 100%;
            padding: 0.75rem 3rem 0.75rem 1rem;
            border: 1px solid var(--border-light);
            border-radius: 0.75rem;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: #F9FAFB;
        }

        input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.5);
        }

        .dark input {
            background-color: #374151;
            color: var(--text-dark);
            border-color: var(--border-dark);
        }

        .input-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        button {
            width: 100%;
            background: linear-gradient(45deg, var(--gold), var(--gold-dark));
            color: white;
            padding: 0.75rem;
            border-radius: 0.75rem;
            border: none;
            font-weight: 700;
            font-size: 1.125rem;
            transition: opacity 0.2s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        button i {
            margin-right: 0.5rem;
        }

        /* Loader */
        .loader {
            display: none;
            border: 4px solid var(--gold-light);
            border-top: 4px solid var(--gold);
            border-radius: 50%;
            width: 2.5rem;
            height: 2.5rem;
            animation: spin 1s linear infinite;
            margin: 1rem auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container animate-slide-up">
        <div class="header">
            <div class="logo-container animate-glow">
                <i class="fas fa-bolt text-white text-xl"></i>
            </div>
            <div>
                <h2 class="title gradient-text">Admin Login</h2>
                <p class="subtitle">MAHIRWE SMART BUSINESS</p>
            </div>
        </div>
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        <form id="loginForm" action="admin_login.php" method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                <i class="fas fa-user input-icon"></i>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fas fa-lock input-icon"></i>
            </div>
            <button type="submit" id="loginButton">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
            <div id="loader" class="loader"></div>
        </form>
    </div>
    <script>
        // Initialize theme
        function initializeTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            }
        }

        // Form submission with loader
        document.getElementById('loginForm').addEventListener('submit', function() {
            const loginButton = document.getElementById('loginButton');
            const loader = document.getElementById('loader');
            loginButton.disabled = true;
            loginButton.style.opacity = '0.5';
            loginButton.style.cursor = 'not-allowed';
            loader.style.display = 'block';
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', initializeTheme);
    </script>
</body>
</html>