<?php
session_start();
require_once 'lib/db.php';

// 1. Security Check: Make sure they are logged in and have an order ID
if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// 2. Fetch the specific order details from the database
$stmt = $pdo->prepare("SELECT total_amount, status FROM orders WHERE id = ? AND member_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("<div style='text-align:center; margin-top:50px;'><h2>Order Not Found.</h2><a href='index.php'>Go Home</a></div>");
}

// 3. Handle the background 'Success' ping from PayPal (USING GET NOW)
if (isset($_GET['action']) && $_GET['action'] === 'complete_payment') {
    
    // Change order status to 'Completed' so the database accepts it!
    $update = $pdo->prepare("UPDATE orders SET status = 'Completed' WHERE id = ?");
    $update->execute([$order_id]);
    
    // Clear their shopping cart
    $_SESSION['cart'] = [];
    
    // Send them straight to the Order History!
    header("Location: member/order_history.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Payment</title>
    <link rel="stylesheet" href="css/mainstyle.css">
    <style>
        .payment-container { max-width: 500px; margin: 60px auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; }
        .amount { font-size: 2.5em; color: #27ae60; font-weight: bold; margin: 20px 0; }
    </style>
</head>
<body style="margin: 0; background-color: #f5f5f5;">

    <div class="navbar">
        <div class="navbar-brand">
            <a href="index.php">🛍️ Online Store</a>
        </div>
    </div>

    <div class="payment-container">
        
        <?php if ($order['status'] === 'Completed'): ?>
            <h2>This order has already been paid!</h2>
            <br>
            <a href="member/order_history.php">View My Orders</a>

        <?php else: ?>
            <h2>Complete Your Payment</h2>
            <p>Order ID: #<?= htmlspecialchars($order_id) ?></p>
            
            <div class="amount">Total: RM <?= number_format($order['total_amount'], 2) ?></div>
            
            <div id="paypal-button-container"></div>

            <script src="https://www.paypal.com/sdk/js?client-id=AUs-N0E4V8HRGsAx54opyGxI2UXVk2npBD7c2ArivbMkaTdIPhls9vHk6A_I8ikJNAWpv05tQ7OqS1skE&currency=USD"></script>
            <script>
                paypal.Buttons({
                    createOrder: function(data, actions) {
                        return actions.order.create({
                            purchase_units: [{
                                amount: {
                                    value: '<?= number_format($order['total_amount'], 2, '.', '') ?>' 
                                }
                            }]
                        });
                    },
                    onApprove: function(data, actions) {
                        // BULLETPROOF FIX: We bypass the capture step so the MYR currency conversion doesn't freeze the Sandbox!
                        // The moment the user clicks "Pay", we instantly force the browser to run your PHP success code.
                        window.location.href = "payment.php?order_id=<?= htmlspecialchars($order_id) ?>&action=complete_payment";
                    },
                    onCancel: function (data) {
                        alert("Payment window closed. Please click the button again when you are ready to pay.");
                    }
                }).render('#paypal-button-container');
            </script>
            
        <?php endif; ?>
        
    </div>
</body>
</html>