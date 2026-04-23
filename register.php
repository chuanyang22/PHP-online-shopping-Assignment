<?php
require_once 'lib/db.php';
require_once 'lib/helpers.php';

// Load language file
$locale = $_SESSION['lang'] ?? 'en';
$allowed_locales = ['en', 'my', 'cn'];
if (!in_array($locale, $allowed_locales)) $locale = 'en';
require_once "lang/{$locale}.php";

$errors      = [];
$success_msg = '';

// Catch the success message after the page refreshes
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_msg = $lang['reg_success'] . " <br><a href='login.php' class='auth-link-bold'>" . $lang['reg_click_login'] . "</a>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username         = sanitize($_POST['username']);
    $email            = sanitize($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Username validation
    if (empty($username)) {
        $errors['username'] = $lang['err_username_required'];
    } else if (strlen($username) < 2 || strlen($username) > 20) {
        $errors['username'] = $lang['err_username_length'];
    }

    // Email validation
    if (empty($email)) {
        $errors['email'] = $lang['err_email_required'];
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = $lang['err_email_invalid'];
    }

    // Password validation
    if (empty($password)) {
        $errors['password'] = $lang['err_password_required'];
    } else if (strlen($password) < 8) {
        $errors['password'] = $lang['err_password_length'];
    }

    // Confirm password validation
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = $lang['err_password_mismatch'];
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM member WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors['email'] = $lang['err_email_taken'];
        }
    }

    // Insert new member
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role            = 'Member';

        $sql  = "INSERT INTO member (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([$username, $email, $hashed_password, $role]);

            // Redirect to show the success message
            header("Location: register.php?success=1");
            exit;

        } catch (PDOException $e) {
            $errors['general'] = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($locale) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($lang['create_account']) ?></title>
    <link rel="stylesheet" href="css/mainstyle.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <div class="auth-title"><?= htmlspecialchars($lang['create_account']) ?></div>

        <?php if (!empty($success_msg)): ?>
            <div class="auth-success-box">
                <?= $success_msg ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="auth-error-box">
                <ul class="auth-error-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <input type="text"     name="username"         class="auth-input" placeholder="<?= htmlspecialchars($lang['ph_username']) ?>"         value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
            <input type="email"    name="email"            class="auth-input" placeholder="<?= htmlspecialchars($lang['ph_email']) ?>"            value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
            <input type="password" name="password"         class="auth-input" placeholder="<?= htmlspecialchars($lang['ph_password']) ?>">
            <input type="password" name="confirm_password" class="auth-input" placeholder="<?= htmlspecialchars($lang['ph_confirm_password']) ?>">

            <button type="submit" class="auth-btn"><?= htmlspecialchars($lang['reg_next_btn']) ?></button>
        </form>

        <div class="auth-footer-text">
            <?= htmlspecialchars($lang['already_account']) ?> <a href="login.php" class="link-primary"><?= htmlspecialchars($lang['login']) ?></a>
            <br><br>
            <a href="index.php" class="link-primary"><?= htmlspecialchars($lang['return_to_store']) ?></a>
        </div>
    </div>
</body>
</html>