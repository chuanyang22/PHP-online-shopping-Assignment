<?php
// product_detail.php
require_once 'lib/db.php';

// 1. Get the Product ID from the URL (the ?id=1 part)
if (!isset($_GET['id'])) {
    die("Error: Product ID is missing.");
}
$product_id = $_GET['id'];

// 2. Fetch the product details from the database
try {
    $stmt = $pdo->prepare("SELECT products.*, categories.name AS category_name 
                           FROM products 
                           LEFT JOIN categories ON products.category_id = categories.id 
                           WHERE products.id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Error: Product not found in our database.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Details</title>
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: #f0f2f5; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
        }
        .detail-card { 
            background: white; 
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            max-width: 500px; 
            width: 90%; 
            text-align: center; 
        }
        .detail-card img { 
            width: 100%; 
            max-height: 300px; 
            object-fit: contain; 
            border-radius: 10px; 
            margin-bottom: 20px; 
        }
        .price { 
            font-size: 28px; 
            color: #28a745; 
            font-weight: bold; 
            margin: 10px 0; 
        }
        .category { color: #666; font-style: italic; margin-bottom: 20px; }
        .stock { 
            display: inline-block; 
            padding: 5px 15px; 
            background: #e9ecef; 
            border-radius: 20px; 
            font-weight: bold; 
        }
        .back-link { 
            display: block; 
            margin-top: 30px; 
            text-decoration: none; 
            color: #4a90e2; 
            font-weight: bold; 
        }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="detail-card">
    <img src="uploads/<?php echo htmlspecialchars($product['image_name']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    
    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
    
    <div class="category">Category: <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
    
    <div class="price">RM <?php echo number_format($product['price'], 2); ?></div>
    
    <div class="stock">
        <?php if ($product['stock_quantity'] > 0): ?>
            ✅ In Stock: <?php echo $product['stock_quantity']; ?>
        <?php else: ?>
            ❌ Out of Stock
        <?php endif; ?>
    </div>

    <?php if ($product['stock_quantity'] > 0): ?>
        <form action="cart.php" method="POST" style="margin-top: 25px;">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <input type="hidden" name="action" value="add">
            
            <label for="quantity" style="font-weight: bold; color: #555;">Qty:</label>
            <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" style="width: 60px; padding: 8px; text-align: center; border-radius: 5px; border: 1px solid #ccc; margin: 0 10px;">
            
            <button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px;">🛒 Add to Cart</button>
        </form>
    <?php endif; ?>

    <form action="wishlist_action.php" method="POST" style="margin-top: 15px;">
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <input type="hidden" name="action" value="add">
        <button type="submit" style="background: #e84393; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; width: 100%; max-width: 250px;">❤️ Save to Wishlist</button>
    </form>

    <a href="index.php" class="back-link">⬅ Back to Store</a>
</div>

</body>
</html>