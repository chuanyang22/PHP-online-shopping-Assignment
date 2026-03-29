<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../lib/db.php';

// 1. Check if the URL has an ID (e.g., product_edit.php?id=3)
if (!isset($_GET['id'])) {
    die("Error: No product ID provided.");
}
$product_id = $_GET['id'];

// 2. Fetch the product's current details from the database
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Error: Product not found in the database.");
}

// 3. Listen for the "Update Product" button click
if (isset($_POST['update_product'])) {
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id']; // --- NEW: Grab the updated category! ---
    
    // Check if they uploaded a NEW photo, otherwise keep the old one
    $image_name = $product['image_name']; 
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $image_name = $_FILES['product_image']['name'];
        $tmp_name = $_FILES['product_image']['tmp_name'];
        move_uploaded_file($tmp_name, "../uploads/" . $image_name);
    }

    // --- NEW: Update query now saves the category_id too! ---
    try {
        $update_stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, stock_quantity = ?, category_id = ?, image_name = ? WHERE id = ?");
        $update_stmt->execute([$name, $price, $stock, $category_id, $image_name, $product_id]);
        
        echo "<script>
                alert('Product updated successfully!');
                window.location.href = 'products_crud.php';
              </script>";
    } catch(PDOException $e) {
        echo "Error updating product: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <style>
        /* --- UPDATED TO MATCH YOUR CUTE BLUE THEME! --- */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 40px 20px; 
            margin: 0;
            background: linear-gradient(135deg, #0b1c3d 0%, #4a90e2 100%);
            color: #333;
            min-height: 100vh;
        }
        .admin-container { 
            max-width: 600px; 
            margin: auto; 
            background: #ffffff; 
            padding: 30px 40px; 
            border-radius: 12px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); 
        }
        h2 { color: #0b1c3d; margin-top: 0; }
        
        .form-group { margin-bottom: 15px; }
        input[type="text"], input[type="number"], select { 
            width: 100%; padding: 10px; margin-top: 5px; 
            border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; 
        }
        
        button { 
            padding: 10px 20px; background: #28a745; color: white; 
            border: none; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.3s;
        }
        button:hover { background: #1e7e34; }
        
        .cancel-btn { 
            background: #6c757d; text-decoration: none; padding: 10px 20px; 
            color: white; border-radius: 5px; display: inline-block; margin-left: 10px; font-weight: bold; transition: 0.3s;
        }
        .cancel-btn:hover { background: #5a6268; }
    </style>
</head>
<body>

<div class="admin-container">
    <h2>✏️ Edit Product #<?php echo htmlspecialchars($product['id']); ?></h2>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label><strong>Product Name:</strong></label>
            <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label><strong>Category:</strong></label>
            <select name="category_id" required>
                <option value="">-- Select a Category --</option>
                <?php
                // Fetch categories and pre-select the one this product currently belongs to!
                $cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
                while ($cat = $cat_stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Check if this category matches the product's current category
                    $selected = ($cat['id'] == $product['category_id']) ? "selected" : "";
                    echo "<option value='" . $cat['id'] . "' " . $selected . ">" . htmlspecialchars($cat['name']) . "</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label><strong>Price (RM):</strong></label>
            <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
        </div>
        
        <div class="form-group">
            <label><strong>Stock Quantity:</strong></label>
            <input type="number" name="stock" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
        </div>
        
        <div class="form-group">
            <label><strong>Current Photo:</strong></label><br>
            <img src="../uploads/<?php echo htmlspecialchars($product['image_name']); ?>" width="120" style="border-radius: 8px; border: 1px solid #ccc; margin-top: 5px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);"><br><br>
            
            <label><strong>Upload New Photo</strong> (Leave blank to keep current photo):</label><br>
            <input type="file" name="product_image" accept="image/*" style="margin-top: 5px;">
        </div>
        
        <div style="margin-top: 25px;">
            <button type="submit" name="update_product">Update Product</button>
            <a href="products_crud.php" class="cancel-btn">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>