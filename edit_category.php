<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$category = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $category = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $icon_class = filter_input(INPUT_POST, 'icon_class', FILTER_SANITIZE_STRING);

    if ($name && $icon_class) {
        $stmt = $conn->prepare("UPDATE categories SET name = ?, icon_class = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $icon_class, $id);
        if ($stmt->execute()) {
            header('Location: admin.php');
        } else {
            $error = "Error updating category.";
        }
        $stmt->close();
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - MAHIRWE SMART BUSINESS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-gold { background: linear-gradient(to right, #FFD700, #B8860B); }
        .gradient-text {
            background: linear-gradient(to right, #FFD700, #B8860B);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .animate-slide-in { animation: slideIn 0.5s ease-out; }
        @keyframes slideIn {
            0% { transform: translateX(20px); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-dark-bg transition-colors duration-300">
    <div class="container mx-auto px-4 py-12">
        <div class="bg-white dark:bg-dark-card p-8 rounded-2xl shadow-lg max-w-lg mx-auto animate-slide-in">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Edit Category</h2>
            <?php if (isset($error)): ?>
                <div class="bg-red-500 text-white p-4 rounded-lg mb-6"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($category): ?>
                <form action="edit_category.php" method="POST" class="flex flex-col gap-4">
                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" placeholder="Category Name" class="px-4 py-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-gold" required>
                    <input type="text" name="icon_class" value="<?php echo htmlspecialchars($category['icon_class']); ?>" placeholder="Font Awesome Icon Class (e.g., fas fa-snowflake)" class="px-4 py-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-gold" required>
                    <div class="flex space-x-4">
                        <button type="submit" class="flex-1 px-6 py-3 bg-gradient-gold text-white rounded-lg font-bold hover:opacity-90 transition-opacity">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                        <a href="admin.php" class="flex-1 px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-center">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <p class="text-red-500">Category not found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>