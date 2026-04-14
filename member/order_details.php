<?php
// Include the header first - this provides the DB connection ($pdo) and starts the session
include '../header.php'; 

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$member_id = $_SESSION['user_id'];

// Fetch order main info
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND member_id = ?");
$stmt->execute([$order_id, $member_id]);
$order = $stmt->fetch();

if (!$order) { 
    echo "<div class='home-container'><h2>Order not found.</h2></div>";
    include '../footer.php';
    exit;
}

// Fetch items in this order
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image_name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>
<style>
    /* 2. This hides the 'broken' links from the shared header just for this page */
    .nav-menu, .navbar-profile, .navbar-brand {
        display: none !important;
    }

    /* 3. Style for your NEW manual buttons */
    .member-nav {
        display: flex;
        justify-content: center;
        gap: 15px;
        background: white;
        padding: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .m-btn {
        text-decoration: none;
        color: #2c3e50;
        background: #f8f9fa;
        padding: 10px 20px;
        border-radius: 8px;
        border: 1px solid #ddd;
        display: flex;
        flex-direction: column;
        align-items: center;
        font-size: 0.8em;
        font-weight: bold;
    }
    .m-btn:hover { background: #e67e22; color: white; }
    .m-btn.active { background: #e67e22; color: white; border-color: #d35400; }
</style>

<div class="member-nav">
    <a href="../index.php" class="m-btn"><span>🏠</span> Home</a>
    <a href="../cart.php" class="m-btn"><span>🛒</span> My Cart</a>
    <a href="order_history.php" class="m-btn active"><span>📜</span> My Orders</a>
    <a href="../profile.php" class="m-btn"><span>🧏‍♂️</span> My Profile</a>
    <a href="../logout.php" class="m-btn" style="color: #e74c3c;"><span>🚪</span> Logout</a>
</div>
</style>
<div class="home-container" style="padding: 20px; max-width: 700px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="order_history.php" style="text-decoration: none; color: #3498db; font-weight: bold;">⬅️ Back to History</a>
    </div>

    <div style="background: #1abc9c; color: white; padding: 25px; border-radius: 12px; margin-bottom: 20px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="margin: 0;">Order #<?= $order['id'] ?> is <?= htmlspecialchars($order['status']) ?></h2>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">Placed on: <?= $order['order_date'] ?></p>
    </div>

    <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; border-left: 5px solid #f39c12;">
        <h4 style="margin-top: 0; color: #f39c12; text-transform: uppercase; font-size: 0.85em; letter-spacing: 1px;">Shipping Information</h4>
        <p style="color: #34495e; line-height: 1.6; margin-bottom: 0;">
            <strong>Address:</strong><br>
            <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
        </p>
    </div>

    <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <div style="padding: 15px; background: #f8f9fa; border-bottom: 1px solid #eee; font-weight: bold; color: #2c3e50;">Items Ordered</div>
        
        <?php foreach ($items as $item): 
            $img = !empty($item['image_name']) ? '../uploads/' . $item['image_name'] : '../uploads/default.png';
        ?>
            <div style="display: flex; gap: 15px; padding: 15px; border-bottom: 1px solid #f9f9f9; align-items: center;">
                <img src="<?= $img ?>" style="width: 70px; height: 70px; object-fit: contain; background: #fff; border: 1px solid #eee; border-radius: 8px;">
                
                <div style="flex-grow: 1;">
                    <div style="font-weight: bold; color: #2c3e50; font-size: 1.1em;"><?= htmlspecialchars($item['name']) ?></div>
                    <div style="font-size: 0.9em; color: #7f8c8d; margin-top: 4px;">Qty: <?= $item['quantity'] ?> × RM <?= number_format($item['price_at_purchase'], 2) ?></div>
                </div>

                <div style="font-weight: bold; color: #2c3e50;">
                    RM <?= number_format($item['price_at_purchase'] * $item['quantity'], 2) ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div style="padding: 20px; background: #fff; text-align: right; border-top: 2px solid #f8f9fa;">
            <div style="color: #7f8c8d; font-size: 0.9em; margin-bottom: 5px;">Total Amount Paid</div>
            <div style="font-size: 1.6em; font-weight: bold; color: #e67e22;">
                RM <?= number_format($order['total_amount'], 2) ?>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>