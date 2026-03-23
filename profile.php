<?php
session_start();
require_once 'lib/db.php';
require_once 'lib/helpers.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info for display
$stmt = $pdo->prepare("SELECT * FROM member WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile Hub</title>
    <link rel="stylesheet" href="css/mainstyle.css"> 
</head>
<body class="auth-body"> <div class="profile-card">
        <h2>My Profile</h2>
        <p>
            <a href="index.php" style="color: #3d1ac7; text-decoration: underline;">Back to Home</a> | 
            <a href="logout.php" style="color: #3d1ac7; text-decoration: underline;">Logout</a>
        </p>
        <br>

        <div id="current-photo">
            <?php if (!empty($user['profile_photo'])): ?>
                <img src="uploads/<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile Picture" class="profile-pic">
            <?php else: ?>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&size=150&background=random&color=fff" alt="Default Avatar" class="profile-pic" style="border-radius: 50%;">
            <?php endif; ?>
        </div>
        
        <div class="profile-info">
            <h3>Welcome, <?= sanitize($user['username']) ?>!</h3>
            <p>Email: <?= sanitize($user['email']) ?></p>
            <p>Member Level: <?= sanitize($user['role']) ?></p>
        </div>

        <hr class="profile-divider">

        <div>
            <a href="upload_photo.php"><button class="auth-btn" style="margin-bottom: 10px;">Upload New Photo</button></a>
            <a href="change_password.php"><button class="auth-btn">Change Password</button></a>
        </div>
    </div>

    <?php 
    if (isset($_GET['login_success']) && $_GET['login_success'] == 1):
    ?>
        <script>
            alert("Login successful! Welcome to your Profile.");
        </script>
    <?php endif; ?>

</body>
</html>