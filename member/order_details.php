<?php
require_once '../lib/auth.php';
require_once '../lib/db.php';
require_once '../lib/helpers.php';

require_login();
$order_id = $_GET['id'] ?? 0;
$member_id = $_SESSION['user_id'];

// Fetch order main info
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND member_id = ?");
$stmt->execute([$order_id, $member_id]);
$order = $stmt->fetch();

if (!$order) { die("Order not found."); }

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details #<?= $order['id'] ?></title>
    <link rel="stylesheet" href="css/mainstyle.css">
</head>
<body>
    <div class="page-container">
        <h2>Order Details #<?= $order['id'] ?></h2>
        <p>Status: <strong><?= $order['status'] ?></strong></p>
        <p>Shipping to: <?= sanitize($order['shipping_address']) ?></p>
        
        <table style="width:100%; border-collapse: collapse; margin-top: 20px; border: 1px solid #ddd;">
    <thead>
        <tr style="background-color: #e4985d; text-align: left; border-bottom: 2px solid #ddd;">
            <th style="padding: 12px;">Product Image</th>
            <th style="padding: 12px;">Product Name</th>
            <th style="padding: 12px;">Quantity</th>
            <th style="padding: 12px;">Price</th>
            <th style="padding: 12px;">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px; width: 100px;">
                    <?php 
                        $img = !empty($item['image_name']) ? '../uploads/' . $item['image_name'] : '../uploads/default.png'; 
                    ?>
                    <img src="<?= $img ?>" alt="Snack" style="width: 80px; height: 80px; object-fit: contain; border: 1px solid #ddd; padding: 5px; background: #fff;">
                </td>
                <td style="padding: 10px; vertical-align: middle;">
                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                </td>
                <td style="padding: 10px; vertical-align: middle;">
                    <?= $item['quantity'] ?>
                </td>
                <td style="padding: 10px; vertical-align: middle;">
                    RM <?= number_format($item['price_at_purchase'], 2) ?>
                </td>
                <td style="padding: 10px; vertical-align: middle;">
                    RM <?= number_format($item['price_at_purchase'] * $item['quantity'], 2) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
                
            </tbody>
        </table>
        <p><strong>Grand Total: RM <?= number_format($order['total_amount'], 2) ?></strong></p>
        <a href="order_history.php" style="text-decoration: none; background: #34495e; color: white; padding: 8px 15px; border-radius: 4px; font-size: 14px;">
        ⬅️ Back to History
        </a>
    </div>
</body>
</html>