<?php
require_once '../lib/auth.php';
require_once '../lib/db.php';
require_login(); // Ensure the user is logged in

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $member_id = $_SESSION['user_id'];

    // Update status only if it belongs to this member AND it is currently 'Pending'
    $stmt = $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ? AND member_id = ? AND status = 'Pending'");
    
    if ($stmt->execute([$order_id, $member_id])) {
        header("Location: order_history.php?success=cancelled");
    } else {
        header("Location: order_history.php?error=failed");
    }
    exit();
}