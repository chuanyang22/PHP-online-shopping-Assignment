<?php
require_once '../lib/auth.php';
require_once '../lib/db.php';
require_once '../lib/helpers.php';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    header("Location: order_list.php?success=1");
    exit();
}

// Fetch all orders with member names
$stmt = $pdo->query("SELECT o.*, m.username FROM orders o JOIN member m ON o.member_id = m.id ORDER BY o.order_date DESC");
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="../css/mainstyle.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .status-pill { padding: 5px 12px; border-radius: 20px; font-size: 0.8em; font-weight: bold; text-transform: uppercase; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-shipped { background: #cfe2ff; color: #084298; }
        .status-cancelled { background: #f8d7da; color: #842029; }
        .status-completed { background: #d1e7dd; color: #0f5132; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f8f9fa; padding: 15px; text-align: left; color: #6c757d; font-size: 0.9em; border-bottom: 2px solid #dee2e6; }
        td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        .action-btn { background: #3498db; color: white; text-decoration: none; padding: 6px 12px; border-radius: 5px; font-size: 0.85em; font-weight: bold; }
        .update-btn { background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; }
        select { padding: 5px; border-radius: 5px; border: 1px solid #ddd; }
    </style>
</head>
<body>

    <div style="display: flex; align-items: center; margin-bottom: 20px; padding: 10px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <a href="dashboard.php" style="text-decoration: none; margin-right: 10px;"><button type="button" class="btn-green" style="background:#27ae60; color:white; border:none; padding:10px; border-radius:5px; cursor:pointer;">📊 Dashboard</button></a>
        <a href="order_list.php" style="text-decoration: none; margin-right: 10px;"><button type="button" class="btn-green" style="background:#27ae60; color:white; border:none; padding:10px; border-radius:5px; cursor:pointer;">📦 Manage Orders</button></a>
        <a href="products_crud.php" style="text-decoration: none; margin-right: 10px;"><button type="button" class="btn-green" style="background:#27ae60; color:white; border:none; padding:10px; border-radius:5px; cursor:pointer;">🛒 Manage Products</button></a>
        <a href="logout.php" style="text-decoration: none; margin-left: auto;"><button type="button" class="btn-red" style="background:#e74c3c; color:white; border:none; padding:10px; border-radius:5px; cursor:pointer;">🚪 Log Out</button></a>
    </div>

    <div class="card">
        <h2 style="margin:0; color: #2c3e50;">Customer Orders</h2>
        
        <?php if (isset($_GET['success'])): ?>
            <div style="background: #d1e7dd; color: #0f5132; padding: 10px; border-radius: 5px; margin-top: 15px;">Status updated successfully!</div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ORDER ID</th>
                    <th>CUSTOMER</th>
                    <th>DATE</th>
                    <th>TOTAL</th>
                    <th>STATUS</th>
                    <th>UPDATE ACTION</th>
                    <th>DETAILS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><strong>#<?= $o['id'] ?></strong></td>
                    <td><?= htmlspecialchars($o['username']) ?></td>
                    <td style="color: #7f8c8d; font-size: 0.9em;"><?= date('M d, Y', strtotime($o['order_date'])) ?></td>
                    <td style="font-weight: bold;">RM <?= number_format($o['total_amount'], 2) ?></td>
                    <td>
                        <?php 
                            $class = 'status-' . strtolower($o['status']);
                            echo "<span class='status-pill $class'>{$o['status']}</span>";
                        ?>
                    </td>
                    <td>
                        <form method="POST" style="display: flex; gap: 5px;">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status">
                                <option value="Pending" <?= $o['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Shipped" <?= $o['status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="Completed" <?= $o['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="Cancelled" <?= $o['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="update-btn">Save</button>
                        </form>
                    </td>
                    <td>
                        <a href="order_details.php?id=<?= $o['id'] ?>" class="action-btn">Manage</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>