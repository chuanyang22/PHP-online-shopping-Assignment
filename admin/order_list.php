<?php
require_once 'lib/auth.php';
require_once 'lib/db.php';
require_once 'lib/helpers.php';

require_admin(); // Secure this page for admins only

// Fetch all orders and join with member table to get usernames
$stmt = $pdo->query("SELECT o.*, m.username 
                     FROM orders o 
                     JOIN member m ON o.member_id = m.id 
                     ORDER BY o.order_date DESC");
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin: All Orders</title>
    <link rel="stylesheet" href="css/mainstyle.css">
</head>
<body>
    <div class="page-container">
        <h2>Customer Orders Management</h2>
        <p><a href="admin_dashboard.php">← Back to Dashboard</a></p>

        <table width="100%" style="border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background: #f4f4f4; border-bottom: 2px solid #ddd;">
                    <th style="padding: 10px;">Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 10px;">#<?= $order['id'] ?></td>
                    <td><?= sanitize($order['username']) ?></td>
                    <td><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                    <td>RM <?= number_format($order['total_amount'], 2) ?></td>
                    <td>
                        <span class="status-badge <?= strtolower($order['status']) ?>">
                            <?= $order['status'] ?>
                        </span>
                    </td>
                    <td><a href="admin_order_details.php?id=<?= $order['id'] ?>">Manage</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>