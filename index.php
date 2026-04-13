<?php 
// This loads your database, session, and navbar
include 'header.php'; 

// --- SEARCH & PRODUCT FETCHING LOGIC ---
$search_keyword = '';
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id";

if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search_keyword = trim($_GET['search']);
    $query .= " WHERE p.name LIKE :search OR p.description LIKE :search";
}

$stmt = $pdo->prepare($query);

if ($search_keyword !== '') {
    $stmt->execute(['search' => "%$search_keyword%"]);
} else {
    $stmt->execute();
}

$products = $stmt->fetchAll();
?>

<div class="home-container" style="padding: 20px; max-width: 1200px; margin: 0 auto;">
    <h2>Welcome to the Store!</h2>
    
    <form method="GET" action="index.php" class="search-bar">
        <input type="text" name="search" placeholder="Search for products..." value="<?= htmlspecialchars($search_keyword) ?>">
        <button type="submit" class="btn" style="margin-top: 0;">Search</button>
        <?php if($search_keyword !== ''): ?>
            <a href="index.php" class="btn" style="background: #e74c3c; margin-top: 0;">Clear</a>
        <?php endif; ?>
    </form>

    <?php if(count($products) > 0): ?>
        <div class="product-grid">
            <?php foreach($products as $product): ?>
                <div class="product-card">
                    <?php $imagePath = !empty($product['image_name']) ? 'uploads/' . htmlspecialchars($product['image_name']) : 'uploads/default.png'; ?>
                    <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                    
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p style="font-size: 0.9em; color: #7f8c8d;"><?= htmlspecialchars($product['category_name']) ?></p>
                    <p class="price">$<?= number_format($product['price'], 2) ?></p>
                    
                    <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn">View Details</a>

                    <form action="cart.php" method="POST" style="display: inline-block;">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn" style="background: #27ae60;">🛒 Add</button>
                    </form>

                    <form action="wishlist_action.php" method="POST" style="display: inline-block;">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="action" value="add">
                        <button type="submit" class="btn" style="background: #9b59b6;">❤️ Save</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No products found matching your search.</p>
    <?php endif; ?>
</div>

<?php 
// This loads the copyright footer and scripts
include 'footer.php'; 
?>