<?php

/**
 * Sanitizes user input to prevent XSS attacks.
 * ALWAYS wrap user data in this before echoing it to the HTML.
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Displays an error message for a specific form field.
 */
function display_error($errors, $field) {
    if (isset($errors[$field])) {
        // Outputting a clean, custom CSS class 'error-text' (You will style this in style.css)
        echo '<span class="error-text" style="color: red; font-size: 0.85em;">' . sanitize($errors[$field]) . '</span>';
    }
}
?>