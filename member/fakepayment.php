<?php
require_once '../lib/auth.php';
require_once '../lib/db.php';

require_login();

// 1. Get the total from the previous page (or session)
$total = 5.00; // Hardcoded for your test

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $member_id = $_SESSION['user_id'];
    $address = $_POST['address'] ?? 'No Address Provided';

    try {
        $pdo->beginTransaction();

        // Insert into orders table
        $stmt = $pdo->prepare("INSERT INTO orders (member_id, total_amount, shipping_address, status, order_date) VALUES (?, ?, ?, 'Pending', NOW())");
        $stmt->execute([$member_id, $total, $address]);
        
        $order_id = $pdo->lastInsertId();

        // Insert into order_items
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, 1, 1, 5.00)");
        $stmt_item->execute([$order_id]);

        $pdo->commit();

        header("Location: order_history.php?success=order_placed");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Database Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fake Payment Gateway</title>
    <link rel="stylesheet" href="../css/mainstyle.css">
</head>
<body style="background: #f1f2f6; display: flex; justify-content: center; align-items: center; height: 100vh;">

    <div style="background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 400px; text-align: center;">
        <h2 style="color: #2c3e50;">💳 Payment Portal</h2>
        <p style="color: #7f8c8d;">Please confirm your payment of:</p>
        <h1 style="color: #2ecc71; margin: 20px 0;">RM <?= number_format($total, 2) ?></h1>
        
        <form method="POST">
            <input type="hidden" name="address" value="Test Address 123">
            
            <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: left; font-size: 0.9em;">
                <p><strong>Card Number:</strong> **** **** **** 4242</p>
                <p><strong>Expiry:</strong> 12/26</p>
            </div>

            <button type="submit" name="confirm_payment" style="background: #3498db; color: white; border: none; padding: 15px 30px; border-radius: 5px; cursor: pointer; font-size: 1.1em; font-weight: bold; width: 100%;">
                PAY NOW (Simulated)
            </button>
        </form>
        
        <p style="margin-top: 15px;"><a href="cart.php" style="color: #e74c3c; text-decoration: none;">Cancel Payment</a></p>
    </div>

</body>
</html>