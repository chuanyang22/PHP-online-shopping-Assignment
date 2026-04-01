<?php
// order_history.php
require_once 'lib/auth.php';
require_once 'lib/db.php';
require_once 'lib/helpers.php';

// Ensure only logged-in members can access
require_login();

$user_id = $_SESSION['user_id'];

// Fetch all orders for this specific member
$stmt = $pdo->prepare("SELECT * FROM orders WHERE member_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Order History</title>
    <link rel="stylesheet" href="css/mainstyle.css">
    <style>
        /* Custom table styling to match your theme */
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .order-table th, .order-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .order-table th {
            background-color: #f8fafc;
            color: #4b5563;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-completed { background: #d1fae5; color: #065f46; }
    </style>
</head>
<body style="background-color: #f4f7f6;">

    <div class="page-container">
        <div class="auth-footer" style="text-align: left; margin-bottom: 20px;">
            <a href="profile.php">← Back to Profile</a>
        </div>

        <h2 style="color: #333;">📦 My Order History</h2>
        <p style="color: #666;">View and track your previous snack purchases.</p>

        <?php if (empty($orders)): ?>
            <div class="auth-card" style="margin-top: 30px;">
                <p>You haven't placed any orders yet!</p>
                <a href="index.php" class="auth-btn" style="text-decoration: none; display: inline-block; width: auto; padding: 10px 20px;">Start Shopping</a>
            </div>
        <?php else: ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></td>
                            <td>RM <?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <span class="status-badge <?= $order['status'] == 'Pending' ? 'status-pending' : 'status-completed' ?>">
                                    <?= sanitize($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="order_details.php?id=<?= $order['id'] ?>" style="color: #9333EA;">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>