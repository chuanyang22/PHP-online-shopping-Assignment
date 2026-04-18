<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Language Logic — set lang in session when ?lang= is used
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$current_lang = $_SESSION['lang'] ?? 'en';

// Load the correct language file
$lang_file = __DIR__ . "/lang/{$current_lang}.php";
if (file_exists($lang_file)) {
    require_once $lang_file;
} else {
    require_once __DIR__ . "/lang/en.php"; // fallback
}

require_once 'lib/db.php';
require_once 'lib/helpers.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Accessory Store</title>
    <link rel="stylesheet" href="css/mainstyle.css">
</head>
<body class="home-body">

    <div class="navbar">
        <div class="navbar-brand">
            <a href="index.php">🛍️ Online Store</a>
        </div>

        <div class="navbar-profile">
            <a href="index.php">🏠 <?= $lang['home'] ?></a>
            <span class="navbar-divider"></span>

            <!-- Language Switcher -->
            <span class="lang-links">
                <a href="?lang=en" class="<?= $current_lang == 'en' ? 'active-lang' : '' ?>">EN</a> |
                <a href="?lang=my" class="<?= $current_lang == 'my' ? 'active-lang' : '' ?>">MY</a> |
                <a href="?lang=cn" class="<?= $current_lang == 'cn' ? 'active-lang' : '' ?>">CN</a>
            </span>
            <span class="navbar-divider"></span>

            <!-- Dark Mode Toggle -->
            <button id="theme-toggle" class="btn-theme-nav" title="<?= $lang['toggle_theme'] ?>">🌓</button>
            <span class="navbar-divider"></span>

            <?php if (isset($_SESSION['username'])): ?>
                <a href="cart.php">🛒 <?= $lang['cart'] ?></a>
                <span class="navbar-divider"></span>

                <a href="member/order_history.php">📜 <?= $lang['my_orders'] ?></a>
                <span class="navbar-divider"></span>

                <a href="wishlist.php">❤️ <?= $lang['my_wishlist'] ?></a>
                <span class="navbar-divider"></span>

                <a href="profile.php">🧏‍♂️ <?= $lang['my_profile'] ?></a>
                <span class="navbar-divider"></span>

                <a href="logout.php"><?= $lang['logout'] ?></a>
            <?php else: ?>
                <a href="login.php"><?= $lang['login'] ?></a>
                <span class="navbar-divider"></span>
                <a href="register.php"><?= $lang['register'] ?></a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Toast popup -->
    <?php if (isset($_SESSION['popup'])): ?>
        <div class="toast-message" id="toast-msg"><?= htmlspecialchars($_SESSION['popup']) ?></div>
        <?php unset($_SESSION['popup']); ?>
        <script>
            setTimeout(function () {
                var t = document.getElementById('toast-msg');
                if (t) t.style.opacity = '0';
            }, 2500);
        </script>
    <?php endif; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;

            if (localStorage.getItem('theme') === 'dark') {
                body.classList.add('dark-mode');
            }

            themeToggle.addEventListener('click', () => {
                body.classList.toggle('dark-mode');
                localStorage.setItem('theme', body.classList.contains('dark-mode') ? 'dark' : 'light');
            });
        });
    </script>