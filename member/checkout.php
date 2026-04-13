<?php
require_once '../lib/auth.php';
require_once '../lib/db.php';
require_once '../lib/helpers.php';

require_login(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Payment</title>
    <link rel="stylesheet" href="../css/mainstyle.css">
</head>
<body>
    <div class="page-container">
        <h2>Fake Payment Checkout</h2>
        <p>Enter your details to complete the order simulation.</p>

        <form action="process_payment.php" method="POST" style="max-width: 500px; margin-top: 20px;">
            <label>Shipping Address:</label><br>
            <textarea name="address" required style="width:100%; height:60px;"></textarea><br><br>

            <hr>
            <h3>Payment (Data Entry Only)</h3>
            
            <label>Cardholder Name:</label><br>
            <input type="text" name="card_name" required style="width:100%;"><br><br>

            <label>Card Number:</label><br>
            <input type="text" name="card_num" pattern="\d{16}" placeholder="16 digits" required style="width:100%;"><br><br>

            <div style="display:flex; gap:10px;">
                <div>
                    <label>Expiry:</label><br>
                    <input type="text" name="expiry" placeholder="MM/YY" maxlength="5" required style="width:60px;">
                </div>
                <div>
                    <label>CVV:</label><br>
                    <input type="password" name="cvv" maxlength="3" required style="width:50px;">
                </div>
            </div>

            <br>
            <button type="submit" class="btn" style="background: green; color: white; padding: 10px 20px; border: none; cursor: pointer;">
                Confirm & Pay
            </button>
        </form>
    </div>
</body>
</html>