<?php
session_start();
require_once 'lib/db.php';
require_once 'lib/helpers.php';

// ==========================================
// NEW: AUTO-LOGIN VIA COOKIE
// ==========================================
// If they aren't logged in, but they HAVE a remember me cookie...
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {
    // Automatically set their session using the cookie!
    $_SESSION['user_id'] = $_COOKIE['remember_user'];
    
    // Send them straight to the profile page
    header("Location: profile.php");
    exit();
}

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors['general'] = "Please enter both email and password.";
    } else {
        // Fetch the user from the database
        $stmt = $pdo->prepare("SELECT * FROM member WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // --- NEW: TEMPORARY BLOCKING CHECK ---
            if ($user['lockout_time'] !== null) {
                $lockout_time = strtotime($user['lockout_time']);
                $current_time = time();

                if ($current_time < $lockout_time) {
                    $minutes_left = ceil(($lockout_time - $current_time) / 60);
                    $errors['general'] = "Account locked due to too many failed attempts. Please try again in $minutes_left minute(s).";
                } else {
                    // Time is up! Unlock the account
                    $stmt = $pdo->prepare("UPDATE member SET failed_attempts = 0, lockout_time = NULL WHERE id = ?");
                    $stmt->execute([$user['id']]);
                }
            }

            // Only proceed if there are no lockout errors yet
            if (empty($errors)) {
                // Check if the password matches
                if (password_verify($password, $user['password'])) {
                    // Login Success! Reset attempts to 0.
                    $stmt = $pdo->prepare("UPDATE member SET failed_attempts = 0, lockout_time = NULL WHERE id = ?");
                    $stmt->execute([$user['id']]);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    if (isset($_POST['remember_me'])) {
                        // Creates a cookie that lasts for 30 days
                        setcookie('remember_user', $user['id'], time() + (86400 * 30), "/");
                    }

                    header("Location: profile.php?login_success=1");
                    exit();
                } else {
                    // --- NEW: FAILED ATTEMPT LOGIC ---
                    $attempts = $user['failed_attempts'] + 1;
                    
                    if ($attempts >= 3) {
                        // Lock them out for 5 minutes (300 seconds)
                        $lockout_until = date('Y-m-d H:i:s', time() + 300);
                        $stmt = $pdo->prepare("UPDATE member SET failed_attempts = ?, lockout_time = ? WHERE id = ?");
                        $stmt->execute([$attempts, $lockout_until, $user['id']]);
                        $errors['general'] = "Too many failed attempts. Account locked for 5 minutes.";
                    } else {
                        // Just record the failed attempt
                        $stmt = $pdo->prepare("UPDATE member SET failed_attempts = ? WHERE id = ?");
                        $stmt->execute([$attempts, $user['id']]);
                        $chances_left = 3 - $attempts;
                        $errors['general'] = "Incorrect password. You have $chances_left attempt(s) left.";
                    }
                }
            }
        } else {
            $errors['general'] = "Account not found. <a href='register.php'>Please register here.</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/mainstyle.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/login.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
</head>
<body class="auth-body">

    <div class="auth-card">
        <div class="auth-title">Log In</div>

        <?php if (isset($errors['general'])): ?>
            <div class="auth-error">
                <?= $errors['general'] ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="text" name="email" class="auth-input" placeholder="Email" value="<?= isset($email) ? sanitize($email) : '' ?>">
            
           <input type="password" name="password" class="auth-input" placeholder="Password">

    <div style="text-align: left; margin-bottom: 15px; font-size: 14px; color: #555;">
        <input type="checkbox" name="remember_me" id="remember_me">
        <label for="remember_me">Remember Me</label>
    </div>

    <button type="submit" class="auth-btn">LOG IN</button>

    <div style="text-align: center; margin-top: 15px;">
    <a href="forgot_password.php" style="color: #5c2bff; font-size: 14px; text-decoration: underline;">Forgot Password?</a>
    </div>
            
        </form>

        <div class="auth-footer">
            New to our store? <a href="register.php">Sign Up</a>
        </div>
    </div>

</body>
</html>