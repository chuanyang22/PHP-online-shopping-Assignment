<?php
session_start();
require_once 'lib/db.php';
require_once 'lib/helpers.php';

// NEW: Bring in PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Check if the email exists in the database
    $stmt = $pdo->prepare("SELECT id FROM member WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // 1. Generate a random, secure token
        $token = bin2hex(random_bytes(32));
        
        // 2 & 3. Save to database using MySQL's clock (1 hour expiration)
        $stmt = $pdo->prepare("UPDATE member SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
        $stmt->execute([$token, $user['id']]);

        // 4. SEND THE REAL EMAIL USING PHPMAILER
        $reset_link = "http://localhost/your_project_folder/reset_password.php?token=" . $token; 

        // Require the core PHPMailer files you downloaded
        require 'PHPMailer/src/Exception.php';
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);

        try {
            // Server settings for Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ahkong463@gmail.com'; // PUT YOUR GMAIL HERE
            $mail->Password   = 'rvwx tifg eckv bfpi';  // PUT YOUR APP PASSWORD HERE (No spaces)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Who it is from and who it is to
            $mail->setFrom('ahkong463@gmail.com', 'Online Store Admin');
            $mail->addAddress($email); // The user's email from the form

            // Email Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                    <h2>Reset Your Password</h2>
                    <p>We received a request to reset your password. Click the button below to choose a new one:</p>
                    <a href='$reset_link' style='background-color: #37c624; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px;'>Reset Password</a>
                    <p style='margin-top: 20px; font-size: 12px; color: #777;'>If you did not request this, please ignore this email.</p>
                </div>
            ";

            $mail->send();
            
            $message = "<div style='color: #155724; background-color: #d4edda; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-size: 14px;'>
                            <strong>Success!</strong> An email has been sent to $email with reset instructions.
                        </div>";
                        
        } catch (Exception $e) {
            $message = "<div style='color: #3dca24; background-color: #f8d7da; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-size: 14px;'>
                            <strong>Message could not be sent.</strong> Mailer Error: {$mail->ErrorInfo}
                        </div>";
        }
    } else {
        $message = "<div style='color: #50d62c; background-color: #f8d7da; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-size: 14px;'>
                        <strong>Error:</strong> Email address not found in our system.
                    </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/mainstyle.css">
</head>
<body class="auth-body">

    <div class="auth-card">
        <div class="auth-title" style="font-size: 24px;">Reset Password</div>
        
        <p style="font-size: 14px; color: #555; margin-bottom: 20px;">
            Enter your email address and we will send you a link to reset your password.
        </p>

        <?= $message ?>

        <form method="POST" action="forgot_password.php">
            <input type="email" name="email" class="auth-input" placeholder="Your Email Address" required>
            <button type="submit" class="auth-btn">Send Reset Link</button>
        </form>

        <div class="auth-footer">
            Remembered it? <a href="login.php">Back to Login</a>
        </div>
    </div>

</body>
</html>