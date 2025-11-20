<?php
require_once 'config.php';

$message = isset($_POST['message']) ? trim($_POST['message']) : '';

$response = "Sorry, I didn’t understand that. Can you ask about our products, prices, delivery, or warranty? Try 'search [product name]' to find products.";

if (stripos($message, 'search') === 0 || stripos($message, 'find') === 0) {
    $search_term = trim(str_ireplace(['search', 'find'], '', $message));
    if (!empty($search_term)) {
        $search = $conn->real_escape_string($search_term);
        $query = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%' LIMIT 5";
        $result = $conn->query($query);
        $products = $result->fetch_all(MYSQLI_ASSOC);

        if (!empty($products)) {
            $response = "Here are some products matching '$search_term':\n\n";
            foreach ($products as $product) {
                $response .= "- {$product['name']} (RWF " . number_format($product['price'], 2) . ")\n";
                $response .= "  Description: {$product['description']}\n";
                $response .= "  Rating: {$product['rating']}\n\n";
            }
            $response .= "Type 'view [product ID]' for more details or visit #products section.";
        } else {
            $response = "No products found matching '$search_term'. Try a different search term.";
        }
    }
} elseif (stripos($message, 'prices') !== false) {
    $response = "Our products are competitively priced starting from RWF 10,000. Search for specific items to see prices.";
} elseif (stripos($message, 'delivery') !== false) {
    $response = "We offer free delivery on orders above RWF 300,000 within Rwanda. Standard delivery is RWF 5,000-10,000 depending on location.";
} elseif (stripos($message, 'warranty') !== false) {
    $response = "All our products come with a 2-year warranty. Contact us for any issues.";
}

echo json_encode(['response' => $response]);
?>