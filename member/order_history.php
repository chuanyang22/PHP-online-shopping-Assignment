<?php
require_once '../lib/auth.php';
require_once '../lib/db.php';
require_once '../lib/helpers.php';
include '../header.php'; 

require_login(); // Ensure user is logged in
?>

<style>
    /* Hides the default header links to use your custom member nav */
    .nav-menu, .navbar-profile, .navbar-brand {
        display: none !important;
    }

    /* Style for your Member Navigation */
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

    .cancel-btn {
        background: #e74c3c;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.8em;
        font-weight: bold;
        transition: 0.3s;
    }
    .cancel-btn:hover { background: #c0392b; }
</style>

<div class="member-nav">
    <a href="../index.php" class="m-btn"><span>🏠</span> Home</a>
    <a href="../cart.php" class="m-btn"><span>🛒</span> My Cart</a>
    <a href="order_history.php" class="m-btn active"><span>📜</span> My Orders</a>
    <a href="../profile.php" class="m-btn"><span>🧏‍♂️</span> My Profile</a>
    <a href="../logout.php" class="m-btn" style="color: #e74c3c;"><span>🚪</span> Logout</a>
</div>

<div class="home-container" style="padding: 20px; max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">My Purchases</h2>
        <?php if (isset($_GET['success']) && $_GET['success'] === 'cancelled'): ?>
            <span style="color: #27ae60; font-weight: bold;">✅ Order Cancelled</span>
        <?php endif; ?>
    </div>

    <?php
    // Fetch orders for the logged-in user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE member_id = ? ORDER BY order_date DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
    
    if ($orders):
        foreach ($orders as $order):
            // Fetch product preview
            $item_stmt = $pdo->prepare("SELECT p.image_name, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ? LIMIT 1");
            $item_stmt->execute([$order['id']]);
            $preview = $item_stmt->fetch();
            $img = !empty($preview['image_name']) ? '../uploads/' . $preview['image_name'] : '../uploads/default.png';
            
            // Set status color
            $status_color = '#7f8c8d'; // Default grey
            if($order['status'] == 'Pending') $status_color = '#f39c12';
            if($order['status'] == 'Shipped') $status_color = '#3498db';
            if($order['status'] == 'Completed') $status_color = '#27ae60';
            if($order['status'] == 'Cancelled') $status_color = '#e74c3c';
    ?>
            <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #eee;">
                
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 15px;">
                    <span style="font-weight: bold; color: #2c3e50;">Order #<?= $order['id'] ?></span>
                    <span style="color: <?= $status_color ?>; font-weight: bold; text-transform: uppercase; font-size: 0.85em;">
                        ● <?= htmlspecialchars($order['status']) ?>
                    </span>
                </div>

                <div style="display: flex; gap: 15px; align-items: center;">
                    <img src="<?= $img ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #f9f9f9;">
                    <div style="flex-grow: 1;">
                        <h4 style="margin: 0 0 5px 0; color: #34495e;"><?= htmlspecialchars($preview['name'] ?? 'Multiple Items') ?></h4>
                        <p style="margin: 0; color: #7f8c8d; font-size: 0.9em;"><?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></p>
                    </div>
                    
                    <div style="text-align: right;">
                        <p style="margin: 0 0 10px 0; font-weight: bold; color: #2ecc71; font-size: 1.1em;">RM <?= number_format($order['total_amount'], 2) ?></p>
                        
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <a href="order_details.php?id=<?= $order['id'] ?>" style="text-decoration: none; font-size: 0.8em; color: #3498db; font-weight: bold; padding: 8px 0;">View Details</a>

                            <?php if ($order['status'] === 'Pending'): ?>
                                <form method="POST" action="cancel_order.php" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit" class="cancel-btn">Cancel Order</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
    <?php
        endforeach;
    else:
        echo "<div style='text-align:center; padding: 50px;'><p>You haven't placed any orders yet.</p><a href='../index.php' class='m-btn' style='display:inline-block;'>Start Shopping</a></div>";
    endif;
    ?>
</div>

<?php include '../footer.php'; ?>