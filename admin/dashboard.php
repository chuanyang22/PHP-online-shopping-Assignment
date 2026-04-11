<?php
require_once '../lib/auth.php';
require_once '../lib/db.php';
require_once '../lib/helpers.php';

// Ensure only admins can see this
// require_admin(); // Uncomment this if you have a require_admin function

// Query to get top selling products
// It joins products and order_items to sum up the quantities sold
$stmt = $pdo->query("
    SELECT p.name, SUM(oi.quantity) as total_sold 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    GROUP BY oi.product_id 
    ORDER BY total_sold DESC 
    LIMIT 5
");
$chart_data = $stmt->fetchAll();

// Prepare data for the JavaScript Chart
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
    <title>Admin Dashboard - Analytics</title>
    <link rel="stylesheet" href="../css/mainstyle.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="page-container" style="max-width: 800px; margin: auto;">
        <h2>Admin Dashboard</h2>
        <p><a href="admin_order_list.php">Manage Orders</a> | <a href="../index.php">View Store</a></p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3>🔥 Top Selling Products</h3>
            <canvas id="sellingChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('sellingChart').getContext('2d');
        const sellingChart = new Chart(ctx, {
            type: 'bar', // You can change this to 'pie' or 'line'
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Quantity Sold',
                    data: <?php echo json_encode($values); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderColor: 'rgba(0, 0, 0, 0.1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    </script>
</body>
</html>