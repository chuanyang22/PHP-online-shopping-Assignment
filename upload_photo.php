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
$errors = [];
$success_msg = '';

// Handling the file upload logic
if (isset($_POST['upload_photo'])) {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_name = $_FILES['photo']['name'];
        
        // Allowed file types (security measure)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($file_tmp);

        if (in_array($file_type, $allowed_types)) {
            // Check file size (e.g., limit to 2MB)
            $file_size = $_FILES['photo']['size'];
            if ($file_size > 2 * 1024 * 1024) { // 2MB in bytes
                $errors['photo'] = "File is too large (max 2MB).";
            } else {
                // Generate unique filename (Prevents overwriting)
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
                
                // Destination path inside your 'uploads/' folder
                $destination = 'uploads/' . $new_filename;
                
                // Move the file from temp folder to your project folder
                if (move_uploaded_file($file_tmp, $destination)) {
                    // Update the user's record in the 'member' database
                    $stmt = $pdo->prepare("UPDATE member SET profile_photo = ? WHERE id = ?");
                    $stmt->execute([$new_filename, $user_id]);
                    
                    $success_msg = "Profile photo updated successfully!";
                } else {
                    $errors['photo'] = "Failed to move the uploaded file.";
                }
            }
        } else {
            $errors['photo'] = "Only JPG, PNG, and GIF files are allowed.";
        }
    } else {
        // General error handling (e.g., no file selected)
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors['photo'] = "Please select an image file first.";
        } else {
            $errors['photo'] = "Please select a valid image file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload New Photo</title>
    <link rel="stylesheet" href="css/mainstyle.css"> 
</head>
<body class="auth-body"> <div class="upload-card">
        <h2>Upload New Photo</h2>
        <p>
            <a href="profile.php" style="color: #ee4d2d; text-decoration: none; font-weight: bold;">Back to Profile</a>
        </p>
        <br>

        <?php if (!empty($success_msg)): ?>
            <div style="color: green; border: 1px solid green; background-color: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 14px;">
                <?= $success_msg ?> (Refreshing profile...)
            </div>
            <script>setTimeout(function(){ window.location.href = 'profile.php'; }, 2000);</script>
        <?php endif; ?>

        <form method="POST" action="upload_photo.php" enctype="multipart/form-data">
            
            <div id="photo-preview-area">
                
                <div id="preview-placeholder" class="preview-box-empty">
                    <span>Preview Area</span>
                </div>
                
                <img id="photo-preview" src="https://via.placeholder.com/200" alt="New Photo Preview">
            </div>

            <input type="file" name="photo" id="photo-input" accept="image/png, image/jpeg, image/gif">
            <br>
            
            <?php if (isset($errors['photo'])): ?>
                <div style="color: red; font-size: 13px; margin-bottom: 15px;"><?= $errors['photo'] ?></div>
            <?php endif; ?>

            <button type="submit" name="upload_photo" class="auth-btn">Upload Photo</button>
        </form>
    </div>

    <script>
        // Listen for when the "Choose File" input changes (user selects a file)
        document.getElementById('photo-input').onchange = function (evt) {
            const tgt = evt.target || window.event.srcElement,
                  files = tgt.files;
            
            const previewImage = document.getElementById('photo-preview');
            const previewPlaceholder = document.getElementById('preview-placeholder');
            
            // Check if FileReader is supported by the browser and a file was selected
            if (FileReader && files && files.length) {
                const fr = new FileReader();
                
                // When the browser finishes reading the selected file...
                fr.onload = function () {
                    // 1. Update the source of the hidden preview image tag
                    previewImage.src = fr.result;
                    
                    // 2. SWAP VISIBILITY: Hide placeholder, show image
                    previewImage.style.display = 'block'; 
                    previewPlaceholder.style.display = 'none'; 
                }
                
                // Read the selected file as a URL (base64 data)
                fr.readAsDataURL(files[0]);
            } else {
                // (Fallback) If reading fails or no file, revert to placeholder
                previewImage.style.display = 'none'; 
                previewPlaceholder.style.display = 'flex'; 
            }
        }
    </script>

</body>
</html>