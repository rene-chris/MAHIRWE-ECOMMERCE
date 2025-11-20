<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$product_id = $_GET['id'];
$product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $price_before = isset($_POST['price_before']) ? floatval($_POST['price_before']) : null;
    $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : 0;
    $features = $conn->real_escape_string($_POST['features']);
    
    $image_path = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "Uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_path = $target_dir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }
    
    $stmt = $conn->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, price_before = ?, image = ?, rating = ?, features = ? WHERE id = ?");
    $stmt->bind_param("issddssdi", $category_id, $name, $description, $price, $price_before, $image_path, $rating, $features, $product_id);
    
    if ($stmt->execute()) {
        header('Location: admin.php');
    } else {
        echo "Error updating product";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - MAHIRWE SMART BUSINESS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-gold { background: linear-gradient(to right, #FFD700, #B8860B); }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Edit Product</h1>
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <form action="edit_product.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <select name="category_id" class="px-4 py-2 border rounded-lg" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" placeholder="Product Name" class="px-4 py-2 border rounded-lg" required>
                    <textarea name="description" placeholder="Description" class="px-4 py-2 border rounded-lg"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    <input type="number" name="price" value="<?php echo $product['price']; ?>" placeholder="Price (RWF)" step="0.01" class="px-4 py-2 border rounded-lg" required>
                    <input type="number" name="price_before" value="<?php echo $product['price_before']; ?>" placeholder="Price Before (RWF)" step="0.01" class="px-4 py-2 border rounded-lg">
                    <input type="file" name="image" accept="image/*" class="px-4 py-2 border rounded-lg">
                    <input type="number" name="rating" value="<?php echo $product['rating']; ?>" placeholder="Rating (0-5)" step="0.1" min="0" max="5" class="px-4 py-2 border rounded-lg">
                    <input type="text" name="features" value="<?php echo htmlspecialchars($product['features']); ?>" placeholder="Features (comma-separated)" class="px-4 py-2 border rounded-lg">
                    <button type="submit" class="px-4 py-2 bg-gradient-gold text-white rounded-lg hover:opacity-90 col-span-2">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>