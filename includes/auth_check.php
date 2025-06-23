<?php
// includes/auth_check.php
function checkAuth() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['UserID'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: login.php");
        exit();
    }
    
    // Verify session timeout (30 minutes)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit();
    }
    
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
    
    // Regenerate session ID periodically to prevent fixation
    if (!isset($_SESSION['last_regeneration']) || 
        (time() - $_SESSION['last_regeneration']) > 300) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}