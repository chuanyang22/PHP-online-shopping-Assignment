<?php
require_once 'lib/auth.php';
require_once 'lib/db.php';
require_once 'lib/helpers.php';

require_admin(); // Secure this page for admins only

// --- NEW: UPDATE STATUS LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        // Refresh the page to show the new status
        header("Location: admin_order_list.php?success=1");
        exit();
    }
}

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
    <style>
        /* Optional style to make the dropdown look cleaner */
        select.status-dropdown {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <h2>Customer Orders Management</h2>
        <p><a href="admin_dashboard.php">← Back to Dashboard</a></p>

        <?php if(isset($_GET['success'])): ?>
            <p style="color: green; font-weight: bold;">Status updated successfully!</p>
        <?php endif; ?>

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
                        <form method="POST" action="">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="new_status" class="status-dropdown" onchange="this.form.submit()">
                                <option value="Pending" <?= $order['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Processing" <?= $order['status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="Shipped" <?= $order['status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="Delivered" <?= $order['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </td>
                    
                    <td><a href="admin_order_details.php?id=<?= $order['id'] ?>">Manage</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>