<?php
require_once '../lib/auth.php';
require_once '../lib/db.php';
require_once '../lib/helpers.php';
// require_admin(); // Commented out

$order_id = $_GET['id'] ?? null;
if (!$order_id) die("Missing ID");

$stmt = $pdo->prepare("SELECT o.*, m.username, m.email FROM orders o JOIN member m ON o.member_id = m.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

$items_stmt = $pdo->prepare("SELECT oi.*, p.name, p.image_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details #<?= $order_id ?></title>
    <link rel="stylesheet" href="../css/mainstyle.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; padding: 20px; }
        .invoice-box { background: white; max-width: 800px; margin: auto; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px; }
        .section-title { color: #7f8c8d; text-transform: uppercase; font-size: 0.8em; letter-spacing: 1px; margin-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #f8f9fa; color: #2c3e50; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        .total-row { font-size: 1.4em; font-weight: bold; color: #27ae60; }
    </style>
</head>
<body>

    <div style="display: flex; align-items: center; margin-bottom: 20px; padding: 10px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <a href="dashboard.php" style="text-decoration: none; margin-right: 10px;"><button type="button" class="btn-green" style="background:#27ae60; color:white; border:none; padding:10px; border-radius:5px; cursor:pointer;">📊 Dashboard</button></a>
        <a href="order_list.php" style="text-decoration: none; margin-right: 10px;"><button type="button" class="btn-green" style="background:#27ae60; color:white; border:none; padding:10px; border-radius:5px; cursor:pointer;">📦 Manage Orders</button></a>
        <a href="products_crud.php" style="text-decoration: none; margin-right: 10px;"><button type="button" class="btn-green" style="background:#27ae60; color:white; border:none; padding:10px; border-radius:5px; cursor:pointer;">🛒 Manage Products</button></a>
        <a href="logout.php" style="text-decoration: none; margin-left: auto;"><button type="button" class="btn-red" style="background:#e74c3c; color:white; border:none; padding:10px; border-radius:5px; cursor:pointer;">🚪 Log Out</button></a>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div>
                <h1 style="margin:0; color: #2c3e50;">Order Details</h1>
                <p style="color: #7f8c8d; margin: 5px 0;">ID: #<?= $order['id'] ?> | Date: <?= date('d M Y', strtotime($order['order_date'])) ?></p>
            </div>
            <div style="text-align: right;">
                <span style="background: #27ae60; color: white; padding: 10px 20px; border-radius: 10px; font-weight: bold;"><?= $order['status'] ?></span>
            </div>
        </div>

        <div class="grid">
            <div>
                <div class="section-title">Customer Information</div>
                <p><strong><?= htmlspecialchars($order['username']) ?></strong></p>
                <p style="color: #555;"><?= htmlspecialchars($order['email']) ?></p>
            </div>
            <div>
                <div class="section-title">Shipping Address</div>
                <p style="color: #555; line-height: 1.6;"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th style="text-align:center;">Qty</th>
                    <th style="text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td style="display: flex; align-items: center; gap: 15px;">
                        <img src="../uploads/<?= $item['image_name'] ?>" width="45" height="45" style="border-radius: 5px; object-fit: cover;">
                        <span><?= htmlspecialchars($item['name']) ?></span>
                    </td>
                    <td>RM <?= number_format($item['price_at_purchase'], 2) ?></td>
                    <td style="text-align:center;"><?= $item['quantity'] ?></td>
                    <td style="text-align:right;">RM <?= number_format($item['price_at_purchase'] * $item['quantity'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" style="text-align: right; padding-top: 30px; font-weight: bold; color: #7f8c8d;">GRAND TOTAL</td>
                    <td style="text-align: right; padding-top: 30px;" class="total-row">RM <?= number_format($order['total_amount'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <div style="text-align: center; margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px;">
            <a href="order_list.php" style="text-decoration: none; color: #3498db; font-weight: bold;">← Return to Orders</a>
        </div>
    </div>
</body>
</html>