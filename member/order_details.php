<?php
// order_details.php
require_once 'lib/auth.php';
require_once 'lib/db.php';
require_once 'lib/helpers.php';

require_login();

$order_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$order_id) {
    header("Location: order_history.php");
    exit;
}

// 1. Fetch order details first so we can check the status for cancellation
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND member_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found or access denied.");
}

// --- NEW CANCELLATION LOGIC START ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    // Re-verify status is still Pending before allowing update
    if ($order['status'] === 'Pending') {
        $cancelStmt = $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ? AND member_id = ?");
        $cancelStmt->execute([$order_id, $user_id]);
        
        // Refresh to show updated status and success message
        header("Location: order_details.php?id=" . $order_id . "&success=cancelled");
        exit;
    } else {
        $error_msg = "This order is already being processed and cannot be cancelled.";
    }
}
// --- NEW CANCELLATION LOGIC END ---

// Fetch items in this order
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name 
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
    <title>Order Details #<?= $order_id ?></title>
    <link rel="stylesheet" href="css/mainstyle.css">
</head>
<body style="background-color: #f4f7f6;">

    <div class="page-container">
        <div class="auth-footer" style="text-align: left; margin-bottom: 20px;">
            <a href="order_history.php">← Back to History</a>
        </div>

        <h2 style="color: #333;">Order Details #<?= $order['id'] ?></h2>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'cancelled'): ?>
            <div class="auth-success" style="margin-bottom: 20px;">Order has been successfully cancelled.</div>
        <?php endif; ?>
        
        <?php if (isset($error_msg)): ?>
            <div class="auth-error" style="margin-bottom: 20px;"><?= $error_msg ?></div>
        <?php endif; ?>

        <div style="text-align: left; margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px;">
            <p><strong>Status:</strong> <?= sanitize($order['status']) ?></p>
            <p><strong>Date Placed:</strong> <?= date('d M Y', strtotime($order['order_date'])) ?></p>

            <?php if ($order['status'] === 'Pending'): ?>
                <hr style="border: 0; border-top: 1px solid #ddd; margin: 15px 0;">
                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                    <button type="submit" name="cancel_order" class="auth-btn" style="background-color: #e53e3e; width: auto; padding: 8px 15px; font-size: 13px;">
                        Cancel Order
                    </button>
                </form>
            <?php endif; ?>
            </div>

        <table style="width:100%; border-collapse: collapse;">
            <tr style="border-bottom: 2px solid #eee;">
                <th style="text-align:left; padding: 10px;">Product</th>
                <th style="text-align:center;">Qty</th>
                <th style="text-align:right; padding: 10px;">Price</th>
            </tr>
            <?php foreach ($items as $item): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="text-align:left; padding: 10px;"><?= sanitize($item['product_name']) ?></td>
                <td style="text-align:center;"><?= $item['quantity'] ?></td>
                <td style="text-align:right; padding: 10px;">RM <?= number_format($item['price_at_purchase'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2" style="text-align:right; padding: 20px 10px;"><strong>Grand Total:</strong></td>
                <td style="text-align:right; padding: 20px 10px;"><strong>RM <?= number_format($order['total_amount'], 2) ?></strong></td>
            </tr>
        </table>
    </div>

</body>
</html>
