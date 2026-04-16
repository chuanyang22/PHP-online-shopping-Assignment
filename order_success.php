<?php
session_start();
$order_id = intval($_GET['order_id'] ?? 0);
?>
<!DOCTYPE html>
<html>
<head><title>Order Confirmed</title></head>
<body style="text-align:center; margin-top: 80px; font-family: sans-serif;">
    <h1>🎉 Payment Successful!</h1>
    <p>Your order <strong>#<?= $order_id ?></strong> has been placed and paid via PayPal.</p>
    <a href="index.php">Continue Shopping</a>
</body>
</html>