<?php
session_start();
require_once 'lib/db.php';
require_once 'lib/helpers.php';

if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to checkout. <a href='login.php'>Click here to login.</a>");
}

$user_id = $_SESSION['user_id'];
$error = '';

// Fetch cart items & total
$cart_total = 0;
$cart_items = [];
$placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute(array_keys($_SESSION['cart']));
$products_in_cart = $stmt->fetchAll();

foreach ($products_in_cart as $product) {
    $qty = $_SESSION['cart'][$product['id']];
    $subtotal = $product['price'] * $qty;
    $cart_total += $subtotal;
    $product['cart_qty'] = $qty;
    $product['subtotal'] = $subtotal;
    $cart_items[] = $product;
}

// Handle form submission AFTER PayPal approval
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address   = trim($_POST['shipping_address'] ?? '');
    $paypal_order_id    = trim($_POST['paypal_order_id'] ?? '');      // NEW
    $paypal_capture_id  = trim($_POST['paypal_capture_id'] ?? '');    // NEW

    if (empty($shipping_address)) {
        $error = "Please provide a shipping address.";
    } elseif (empty($paypal_order_id)) {
        // Prevent someone bypassing PayPal by submitting the form directly
        $error = "Payment was not completed. Please use the PayPal button.";
    } else {
        try {
            $pdo->beginTransaction();

            // Save order — now also stores payment info
            $stmt = $pdo->prepare("
                INSERT INTO orders 
                    (member_id, total_amount, shipping_address, status, payment_method, paypal_order_id, paypal_capture_id)
                VALUES 
                    (?, ?, ?, 'Paid', 'PayPal', ?, ?)
            ");
            $stmt->execute([$user_id, $cart_total, $shipping_address, $paypal_order_id, $paypal_capture_id]);
            $order_id = $pdo->lastInsertId();

            // Save order items & deduct stock
            $stmt_items   = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
            $update_stock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");

            foreach ($cart_items as $item) {
                $stmt_items->execute([$order_id, $item['id'], $item['cart_qty'], $item['price']]);
                $update_stock->execute([$item['cart_qty'], $item['id']]);
            }

            $pdo->commit();

            // Clear cart after successful order
            unset($_SESSION['cart']);

            // Redirect to a thank-you/success page (not payment.php — payment is already done!)
            header("Location: member/order_history.php");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to save order: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/mainstyle.css">
    <style>
        .checkout-container { max-width: 800px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .summary-box { background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #ddd; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; resize: vertical; height: 100px; box-sizing: border-box; }
        .msg { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; text-align: center; }
        .msg-error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        #paypal-button-container { margin-top: 20px; }
    </style>
</head>
<body style="margin: 0; background-color: #f5f5f5;">

<div class="navbar">
    <div class="navbar-brand">
        <a href="index.php">🛍️ Online Store</a>
    </div>
</div>

<div class="checkout-container">
    <h2>Checkout</h2>

    <?php if ($error): ?>
        <div class="msg msg-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Order Summary -->
    <div class="summary-box">
        <h3>Order Summary</h3>
        <ul style="list-style-type: none; padding: 0;">
            <?php foreach ($cart_items as $item): ?>
                <li style="margin-bottom: 10px; border-bottom: 1px dashed #ccc; padding-bottom: 10px;">
                    <?= htmlspecialchars($item['name']) ?> (x<?= $item['cart_qty'] ?>)
                    <span style="float: right;">RM <?= number_format($item['subtotal'], 2) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <h3 style="text-align: right; margin-top: 20px; color: #27ae60;">
            Total: RM <?= number_format($cart_total, 2) ?>
        </h3>
    </div>

    <!-- Hidden form — submitted programmatically after PayPal approval -->
    <form id="checkout-form" action="checkout.php" method="POST">
        <div class="form-group">
            <label for="shipping_address">Shipping Address</label>
            <textarea name="shipping_address" id="shipping_address"
                      placeholder="Enter your full street address, city, and postcode..." required></textarea>
        </div>

        <!-- These hidden fields get filled in by JavaScript after PayPal payment -->
        <input type="hidden" name="paypal_order_id"   id="paypal_order_id">
        <input type="hidden" name="paypal_capture_id" id="paypal_capture_id">

        <!-- PayPal button renders here -->
        <div id="paypal-button-container"></div>
    </form>
</div>

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=AUs-N0E4V8HRGsAx54opyGxI2UXVk2npBD7c2ArivbMkaTdIPhls9vHk6A_I8ikJNAWpv05tQ7OqS1sk&currency=MYR&disable-funding=card"></script>
<script>
paypal.Buttons({

    // Validate address BEFORE the PayPal popup opens
    onClick: function(data, actions) {
        const address = document.getElementById('shipping_address').value.trim();
        if (address === '') {
            alert('Please enter your shipping address before paying.');
            return actions.reject();
        }
        return actions.resolve();
    },

    // Tell PayPal how much to charge
    createOrder: function(data, actions) {
        return actions.order.create({
            purchase_units: [{
                amount: {
                    value: '<?= number_format($cart_total, 2, '.', '') ?>'
                }
            }]
        });
    },

    // Payment approved — capture it, then save the order to your DB
    onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
            // Fill the hidden fields with PayPal's transaction info
            document.getElementById('paypal_order_id').value   = data.orderID;
            document.getElementById('paypal_capture_id').value = details.purchase_units[0].payments.captures[0].id;

            // Now submit the form — PHP will save the order marked as 'Paid'
            document.getElementById('checkout-form').submit();
        });
    },

    // Payment was cancelled
    onCancel: function(data) {
        alert('Payment cancelled. Your cart is still saved.');
    },

    // Something went wrong
    onError: function(err) {
        alert('A PayPal error occurred. Please try again.');
        console.error(err);
    }

}).render('#paypal-button-container');
</script>

</body>
</html>