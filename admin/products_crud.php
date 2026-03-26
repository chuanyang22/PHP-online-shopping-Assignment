<?php
// Turn on the lights for errors!
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Connect to the database
require_once '../lib/db.php';

// 2. Check if the user clicked the "Save Product" button
if (isset($_POST['add_product'])) {
    $name = $_POST['product_name'];
    $price = $_POST['price'];

    // Handle the photo upload
    $image_name = $_FILES['product_image']['name'];
    $tmp_name = $_FILES['product_image']['tmp_name'];
    $target_folder = "../uploads/";

    // Move the uploaded picture from temp storage to your actual uploads folder
    move_uploaded_file($tmp_name, $target_folder . $image_name);

    // Save the details to the database using secure PDO
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, image_name) VALUES (?, ?, ?)");
        $stmt->execute([$name, $price, $image_name]);
        echo "<script>alert('Product successfully added to database!');</script>";
    } catch(PDOException $e) {
        echo "Error saving product: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .admin-container { max-width: 1000px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .form-group { margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="admin-container">
    <h1>Product Maintenance</h1>

    <div style="background: #f9f9f9; padding: 20px; border-radius: 5px;">
        <h2>Add New Product</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Product Name:</label><br>
                <input type="text" name="product_name" required>
            </div>
            <div class="form-group">
                <label>Price (RM):</label><br>
                <input type="number" step="0.01" name="price" required>
            </div>
            <div class="form-group">
                <label>Product Photo:</label><br>
                <input type="file" name="product_image" accept="image/*" required>
            </div>
            <button type="submit" name="add_product">Save Product</button>
        </form>
    </div>

    <h2 style="margin-top: 40px;">Current Products</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Photo</th>
                <th>Name</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Display products from the database
            try {
                $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($products) == 0) {
                    echo "<tr><td colspan='5' style='text-align:center;'>No products found. Add one above!</td></tr>";
                } else {
                    foreach ($products as $product) {
                        echo "<tr>";
                        echo "<td>" . $product['id'] . "</td>";
                        echo "<td><img src='../uploads/" . htmlspecialchars($product['image_name']) . "' width='50'></td>";
                        echo "<td>" . htmlspecialchars($product['name']) . "</td>";
                        echo "<td>RM " . number_format($product['price'], 2) . "</td>";
                        echo "<td><button>Edit</button> <button style='color: red;'>Delete</button></td>";
                        echo "</tr>";
                    }
                }
            } catch(PDOException $e) {
                echo "<tr><td colspan='5'>Error loading products: " . $e->getMessage() . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>