<?php
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Function to require specific role
function requireRole($allowedRoles) {
    requireLogin();
    if (!in_array($_SESSION['role'], (array)$allowedRoles)) {
        header('Location: index.php');
        exit;
    }
}

// Function to login user
function loginUser($userId, $email, $role) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['login_time'] = time();
}

// Function to logout user
function logoutUser() {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// Function to get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role']
    ];
}

// Handle logout
if (isset($_GET['logout'])) {
    logoutUser();
}
?>
