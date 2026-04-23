<?php
session_start();
require_once 'lib/db.php';
require_once 'lib/helpers.php';

auth('Member');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_lang = $_SESSION['lang'] ?? 'en';
$lang_file = __DIR__ . "/lang/{$current_lang}.php";
require_once file_exists($lang_file) ? $lang_file : __DIR__ . "/lang/en.php";

// Fetch user info for display
$stmt = $pdo->prepare("SELECT * FROM member WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($lang['my_profile_hub'] ?? 'My Profile Hub') ?></title>
    <link rel="stylesheet" href="css/mainstyle.css">
</head>
<body class="auth-body">
    <div class="profile-card">
        <h2><?= htmlspecialchars($lang['my_profile'] ?? 'My Profile') ?></h2>
        <p>
            <a href="index.php" class="profile-link"><?= htmlspecialchars($lang['back_to_home'] ?? 'Back to Home') ?></a> | 
            <a href="logout.php" class="profile-link"><?= htmlspecialchars($lang['logout'] ?? 'Logout') ?></a> |
            <a href="member/order_history.php" class="profile-link"><?= htmlspecialchars($lang['history'] ?? 'History') ?></a> |
        </p>
        <br>

        <?php if (!empty($user['profile_photo']) && file_exists('uploads/' . $user['profile_photo'])): ?>
            <img src="uploads/<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile Picture" class="profile-pic profile-pic-custom">
        <?php else: ?>
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&size=150&background=random&color=fff"
                 alt="Default Avatar" class="profile-pic profile-pic-default">
        <?php endif; ?>

        <div class="profile-info">
            <h3><?= htmlspecialchars($lang['profile_welcome'] ?? 'Welcome') ?>, <?= htmlspecialchars($user['username']) ?>!</h3>
            <p><?= htmlspecialchars($lang['email'] ?? 'Email') ?>: <?= htmlspecialchars($user['email']) ?></p>
            <p><?= htmlspecialchars($lang['member_level'] ?? 'Member Level') ?>: <?= htmlspecialchars($user['role']) ?></p>
        </div>

        <hr class="profile-divider">

        <?php if (isset($_GET['photo_updated'])): ?>
            <div class="auth-success-msg">✅ <?= htmlspecialchars($lang['profile_photo_updated'] ?? 'Profile photo updated successfully!') ?></div>
        <?php endif; ?>

        <div class="btn-group-col">
            <a href="upload_photo.php">
                <button class="auth-btn btn-full-width"><?= htmlspecialchars($lang['upload_new_photo'] ?? 'Upload New Photo') ?></button>
            </a>
            <a href="change_password.php">
                <button class="auth-btn btn-full-width"><?= htmlspecialchars($lang['change_password'] ?? 'Change Password') ?></button>
            </a>
            <a href="edit_profile.php">
                <button class="auth-btn btn-full-width"><?= htmlspecialchars($lang['edit_profile_info'] ?? 'Edit Profile Info') ?></button>
            </a>
        </div>
    </div>
</body>
</html>