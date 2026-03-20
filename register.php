<?php
require_once 'lib/db.php';
require_once 'lib/helpers.php';

$errors      = [];
$success_msg = '';  // ✅ BUG 8 fixed: was $sucess_msg

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username         = sanitize($_POST['username']);
    $email            = sanitize($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Username validation
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    } else if (strlen($username) < 3 || strlen($username) > 20) {
        $errors['username'] = "Username must be between 3 to 20 characters.";
    }

    // Email validation
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    // Password validation
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } else if (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters.";
    }

    // Confirm password validation
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM member WHERE email = ?");  // ✅ BUG 6 fixed: 'member' not 'user'
        $stmt->execute([$email]);  // ✅ BUG 2 fixed: was $emial
        if ($stmt->rowCount() > 0) {
            $errors['email'] = "This email is already registered.";  // ✅ BUG 3 fixed: was $errors['emial']
        }
    }

    // Insert new member
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role            = 'Member';  // ✅ BUG 4 fixed: was =-

        $sql  = "INSERT INTO member (username, email, password, role) VALUES (?, ?, ?, ?)";  // ✅ BUG 6+7 fixed
        $stmt = $pdo->prepare($sql);  // ✅ BUG 5 fixed: was $$sql

        try {
            $stmt->execute([$username, $email, $hashed_password, $role]);
            $success_msg = "Congratulations, you have successfully registered!";  // ✅ BUG 8 fixed
        } catch (PDOException $e) {
            $errors['general'] = "Something went wrong, please try again.";
        }
    }
}
?>



<?php
// 1. Load helpers FIRST — defines sanitize() and display_error()
require_once 'lib/helpers.php';
require_once 'lib/db.php';

// 2. Initialize defaults so variables are always defined
$errors      = [];
$success_msg = '';
$username    = '';
$email       = '';

// 3. Handle form submission right here, not in a separate require
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'register.php'; // only runs on POST, sets $errors/$success_msg
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/loginstyle.css">
    <script src="js/login.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Registration</title>
</head>
<body>
    
    <h2>Register an Account</h2>

    <?php if (!empty($success_msg)): ?>
        <p style='color:green;'><?=  $success_msg ?></p>
    <?php endif; ?>

<?php
require_once 'lib/helpers.php'; // ✅ display_error() comes FROM here only
// ❌ No function display_error() anywhere below this line
?>

<?php
require_once 'lib/helpers.php';
$errors = []; // default — prevents warnings if register.php isn't included
require_once 'register.php'; // this may overwrite $errors with real values
?>
    
    <form method='POST' action="register.php">
        <div>
            <label>Username:</label>
            <input type="text" name="username" value="<?= isset($username) ? sanitize($username) : '' ?>">
            <?php display_error($errors, 'username'); ?> 
        </div>
        <br>
        <div>
            <label>Email:</label>
            <input type="text" name="email" value="<?= isset($email) ? sanitize($email) : '' ?>">
            <?php display_error($errors, 'email'); ?>
        </div>
        <br>
        <div>
            <label>Password:</label>
            <input type="password" name="password">
            <?php display_error($errors, 'password'); ?>
        </div>
        <br>
        <div>
            <label>Confirm Password:</label>
            <input type="password" name="confirm_password">
            <?php display_error($errors, 'confirm_password'); ?>
        </div>
        <br>
        <button type="submit">Register</button>
    </form>
</body>
</html>