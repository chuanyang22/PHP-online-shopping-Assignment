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
        echo '<span class="error-text">' . sanitize($errors[$field]) . '</span>';
    }
}

/**
 * Reliable order status for display when DB value is missing/empty (invalid ENUM, etc.).
 * PayPal checkout rows usually have paypal_order_id / paypal_capture_id set.
 */
function order_status_normalized(array $order) {
    $raw = '';
    if (isset($order['status'])) {
        $raw = trim((string) $order['status']);
    } elseif (isset($order['STATUS'])) {
        $raw = trim((string) $order['STATUS']);
    }
    if ($raw !== '') {
        if (strcasecmp($raw, 'Completed') === 0) {
            return 'Delivered';
        }
        return $raw;
    }
    if (!empty($order['paypal_capture_id']) || !empty($order['paypal_order_id'])) {
        return 'Paid';
    }
    return 'Pending';
}

/** Fulfillment progress for the member status bar: 0–3, or -1 if cancelled. */
function order_fulfillment_progress($status) {
    $s = strtolower(trim((string) $status));
    if ($s === 'cancelled') {
        return -1;
    }
    if (in_array($s, ['delivered', 'completed'], true)) {
        return 3;
    }
    if ($s === 'shipped') {
        return 2;
    }
    if (in_array($s, ['paid', 'processing'], true)) {
        return 1;
    }
    return 0;
}

function auth($required_role = null) {
    // 1. First, check if they are logged in at all
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // 2. If you asked for a specific role (like 'Member' or 'Admin'), check it!
    if ($required_role !== null) {
        // Check if their session role matches the required role
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
            // If they are not the right role, kick them back to the homepage
            echo "<script>
                    alert('Access Denied: You do not have permission to view this page.');
                    window.location.href = 'index.php';
                  </script>";
            exit();
        }
    }
}

// lib/helpers.php

// We wrap it in function_exists to prevent duplicate errors
if (!function_exists('auth')) {
    
    // The auth() function protects pages from unauthorized access
    function auth($required_role = null) {
        
        // 1. If the user is not logged in at all, kick them to the login page
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        // 2. If the page requires a specific role (like 'Admin' or 'Member')
        if ($required_role !== null) {
            
            // If the user's role doesn't match the required role, kick them to the homepage
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
                
                // Optional: You can echo a script here to show an alert, or just silently redirect
                header("Location: index.php");
                exit();
            }
        }
    }
    
}
?>