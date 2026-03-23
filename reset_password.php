<?php
session_start();
require_once 'lib/db.php';

$error_msg = "";
$success_msg = "";
$token = $_GET['token'] ?? '';

// If there's no token in the URL, kick them out
if (empty($token)) {
    die("Invalid request. Missing token.");
}

// Check if the token exists in the database AND hasn't expired
$stmt = $pdo->prepare("SELECT id FROM member WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("This password reset link is invalid or has expired. <a href='forgot_password.php'>Request a new one</a>.");
}

// Handle the form submission for the new password
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error_msg = "Passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error_msg = "Password must be at least 6 characters long.";
    } else {
        // Hash the new password securely
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password in the database and CLEAR the reset token so it can't be used again
        $stmt = $pdo->prepare("UPDATE member SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$hashed_password, $user['id']]);

        $success_msg = "Password has been successfully reset! Redirecting to login...";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Password</title>
    <link rel="stylesheet" href="css/mainstyle.css">
</head>
<body class="auth-body">

    <div class="auth-card">
        <div class="auth-title" style="font-size: 24px;">New Password</div>

        <?php if (!empty($error_msg)): ?>
            <div style="color: red; font-size: 13px; margin-bottom: 15px;"><?= $error_msg ?></div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div style="color: #155724; background-color: #d4edda; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-size: 14px;">
                <?= $success_msg ?>
            </div>
            <script>setTimeout(function(){ window.location.href = 'login.php'; }, 3000);</script>
        <?php else: ?>

            <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
                <input type="password" name="new_password" class="auth-input" placeholder="New Password" required>
                <input type="password" name="confirm_password" class="auth-input" placeholder="Confirm New Password" required>
                <button type="submit" class="auth-btn">Save New Password</button>
            </form>

        <?php endif; ?>

    </div>

</body>
</html>