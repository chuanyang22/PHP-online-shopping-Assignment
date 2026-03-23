<?php
session_start();
session_unset();
session_destroy();

// If you used the "Remember Me" cookie, delete it too!
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, "/");
}

header("Location: index.php");
exit();