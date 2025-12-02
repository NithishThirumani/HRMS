<?php
/**
 * Centralized Session Manager
 * Handles all session operations and prevents duplicate session_start() calls
 */

// Only start session if one isn't already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Function to safely start a session
 * @return bool True if session was started, false if already active
 */
function safeSessionStart() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
        return true;
    }
    return false;
}

/**
 * Function to check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['email']) || isset($_SESSION['admin_id']) || isset($_SESSION['eid']);
}

/**
 * Function to get current user role
 * @return string|null User role or null if not set
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Function to get current user email
 * @return string|null User email or null if not set
 */
function getCurrentUserEmail() {
    return $_SESSION['email'] ?? null;
}

/**
 * Function to get current user ID
 * @return string|null User ID or null if not set
 */
function getCurrentUserId() {
    return $_SESSION['eid'] ?? $_SESSION['admin_id'] ?? null;
}
?> 