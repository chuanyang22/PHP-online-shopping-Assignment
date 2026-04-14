<?php
require_once '../lib/auth.php';
require_once '../lib/db.php';
require_once '../lib/helpers.php';

// 1. Get Chart Data
$stmt = $pdo->query("
    SELECT p.name, SUM(oi.quantity) as total_sold 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    GROUP BY oi.product_id 
    ORDER BY total_sold DESC 
    LIMIT 5
");
$chart_data = $stmt->fetchAll();

$labels = [];
$values = [];
foreach ($chart_data as $row) {
    $labels[] = $row['name'];
    $values[] = $row['total_sold'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/mainstyle.css"> <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="background: #f4f7f6; padding: 20px;">

    <div style="display: flex; align-items: center; margin-bottom: 20px; padding: 10px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <a href="dashboard.php" style="text-decoration: none; margin-right: 10px;">
            <button type="button" class="btn-green" style="background:#27ae60; color:white; border:none; padding:10px; border-radius:5px;">📊 Dashboard</button>
        </a>
        <a href="order_list.php" style="text-decoration: none; margin-right: 10px;">
            <button type="button" class="btn-green" style="background:#27ae60; color:white; border:none; padding:10px; border-radius:5px;">📦 Manage Orders</button>
        </a>
        <a href="products_crud.php" style="text-decoration: none; margin-right: 10px;">
            <button type="button" class="btn-green" style="background:#27ae60; color:white; border:none; padding:10px; border-radius:5px;">🛒 Manage Products</button>
        </a>
        <a href="logout.php" style="text-decoration: none; margin-left: auto;">
            <button type="button" class="btn-red" style="background:#e74c3c; color:white; border:none; padding:10px; border-radius:5px;">🚪 Log Out</button>
        </a>
    </div>

    <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <h3 style="color: #34495e;">🔥 Top Selling Products</h3>
        
        <?php if(empty($labels)): ?>
            <div style="text-align: center; padding: 50px;">
                <p style="color: #95a5a6; font-size: 1.2em;">No sales recorded yet.</p>
                <p style="color: #bdc3c7;">Try placing an order as a member first!</p>
            </div>
        <?php else: ?>
            <canvas id="sellingChart" style="max-height: 400px;"></canvas>
        <?php endif; ?>
    </div>

    <script>
        <?php if(!empty($labels)): ?>
        const ctx = document.getElementById('sellingChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Units Sold',
                    data: <?php echo json_encode($values); ?>,
                    backgroundColor: '#1dee12',
                    borderRadius: 5
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
        <?php endif; ?>
    </script>
</body>
</html>