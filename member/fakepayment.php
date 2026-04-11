<?php
require_once '../lib/auth.php';
require_once '../lib/db.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = $_SESSION['user_id'];
    $address = $_POST['address'];
    $total = 5.00; // Hardcoded for this test; normally calculated from cart

    try {
        $pdo->beginTransaction();

        // 1. Insert into orders table
        $stmt = $pdo->prepare("INSERT INTO orders (member_id, total_amount, shipping_address, status, order_date) VALUES (?, ?, ?, 'Pending', NOW())");
        $stmt->execute([$member_id, $total, $address]);
        
        $order_id = $pdo->lastInsertId();

        // 2. Insert into order_items (Using Product ID 1 as a test)
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, 1, 1, 5.00)");
        $stmt_item->execute([$order_id]);

        $pdo->commit();

        // Redirect to history to see the new order
        header("Location: order_history.php?success=order_placed");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Database Error: " . $e->getMessage());
    }
}