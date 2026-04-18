<?php
session_start();
require_once 'lib/db.php';
require_once 'lib/helpers.php';

// Load language
$current_lang = $_SESSION['lang'] ?? 'en';
$lang_file = __DIR__ . "/lang/{$current_lang}.php";
require_once file_exists($lang_file) ? $lang_file : __DIR__ . "/lang/en.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add, Update, Remove
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action']     ?? '';
    $product_id = $_POST['product_id'] ?? null;

    if ($product_id) {
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $stock_data = $stmt->fetch();
        $max_stock  = $stock_data ? (int)$stock_data['stock_quantity'] : 0;

        if ($action === 'add') {
            $qty         = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            $current_qty = $_SESSION['cart'][$product_id] ?? 0;
            $new_qty     = $current_qty + $qty;
            if ($new_qty > $max_stock) {
                $_SESSION['cart'][$product_id] = $max_stock;
                $_SESSION['popup'] = "Only $max_stock available in stock.";
            } else {
                $_SESSION['cart'][$product_id] = $new_qty;
                $_SESSION['popup'] = "Added to cart!";
            }
        } elseif ($action === 'update') {
            $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            $_SESSION['cart'][$product_id] = min($qty, $max_stock);
        } elseif ($action === 'remove') {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    header("Location: cart.php");
    exit();
}

// Fetch full product details
$cart_items = [];
$cart_total = 0;

if (!empty($_SESSION['cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($_SESSION['cart']));
    $products_in_cart = $stmt->fetchAll();

    foreach ($products_in_cart as $product) {
        $qty               = $_SESSION['cart'][$product['id']];
        $subtotal          = $product['price'] * $qty;
        $cart_total       += $subtotal;
        $product['cart_qty'] = $qty;
        $product['subtotal'] = $subtotal;
        $cart_items[]      = $product;
    }
}
?>

<?php include 'header.php'; ?>

<div class="page-container">
    <h2>🛒 <?= $lang['cart'] ?></h2>

    <?php if (empty($cart_items)): ?>
        <p><?= $lang['empty_cart'] ?></p>
        <a href="index.php" class="btn-primary"><?= $lang['continue_shopping'] ?></a>

    <?php else: ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th><?= $lang['product'] ?></th>
                    <th><?= $lang['price'] ?></th>
                    <th><?= $lang['quantity'] ?></th>
                    <th><?= $lang['subtotal'] ?></th>
                    <th><?= $lang['remove'] ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                        <td>RM <?= number_format($item['price'], 2) ?></td>
                        <td>
                            <form action="cart.php" method="POST" class="form-inline">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="number" name="quantity"
                                       value="<?= $item['cart_qty'] ?>"
                                       min="1" max="<?= $item['stock_quantity'] ?>"
                                       class="qty-input"
                                       onchange="this.form.submit()">
                            </form>
                        </td>
                        <td><strong>RM <?= number_format($item['subtotal'], 2) ?></strong></td>
                        <td>
                            <form action="cart.php" method="POST" class="form-inline">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn-remove"><?= $lang['remove'] ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-summary mt-20">
            <p><strong><?= $lang['total'] ?>: RM <?= number_format($cart_total, 2) ?></strong></p>
            <a href="checkout.php" class="btn-success"><?= $lang['proceed_checkout'] ?></a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>