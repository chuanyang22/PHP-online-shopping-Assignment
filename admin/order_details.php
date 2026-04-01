<?php
require_once 'lib/auth.php';
require_once 'lib/db.php';
require_once 'lib/helpers.php';
require_once 'lib/mailer.php'; 

require_admin(); //

$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header("Location: admin_orders.php");
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);

    // Fetch member details to send notification
    $stmt = $pdo->prepare("SELECT m.email, m.username FROM orders o JOIN member m ON o.member_id = m.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $member = $stmt->fetch();

    send_formatted_email(
        $member['email'], 
        $member['username'], 
        "Order #$order_id Updated", 
        "Status Update", 
        "Your order status has been changed to: <strong>$new_status</strong>."
    );

    $success_msg = "Status updated and customer notified via email.";
}

// Fetch Order info
$stmt = $pdo->prepare("SELECT o.*, m.username FROM orders o JOIN member m ON o.member_id = m.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

// Fetch Order Items
$stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Order #<?= $order_id ?></title>
    <link rel="stylesheet" href="css/mainstyle.css">
</head>
<body>
    <div class="page-container">
        <p style="text-align: left;"><a href="admin_orders.php">← Back to List</a></p>
        <h2>Order #<?= $order_id ?> Details</h2>
        <p>Customer: <strong><?= sanitize($order['username']) ?></strong></p>

        <?php if (isset($success_msg)): ?>
            <div class="auth-success"><?= $success_msg ?></div>
        <?php endif; ?>

        <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #ddd;">
            <form method="POST">
                <label>Update Status:</label>
                <select name="status" class="auth-input" style="width: 200px; display: inline-block;">
                    <option value="Pending" <?= $order['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Processing" <?= $order['status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="Completed" <?= $order['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button type="submit" name="update_status" class="auth-btn" style="width: auto; padding: 10px 20px;">Save</button>
            </form>
        </div>

        <table width="100%" style="text-align: left; border-collapse: collapse;">
            <tr style="border-bottom: 2px solid #eee;">
                <th>Item</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= sanitize($item['name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>RM <?= number_format($item['price_at_purchase'], 2) ?></td>
                <td>RM <?= number_format($item['quantity'] * $item['price_at_purchase'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold; border-top: 2px solid #eee;">
                <td colspan="3" style="text-align: right; padding-top: 10px;">Total Amount:</td>
                <td style="padding-top: 10px;">RM <?= number_format($order['total_amount'], 2) ?></td>
            </tr>
        </table>
    </div>
</body>
</html>