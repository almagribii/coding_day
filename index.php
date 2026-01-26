<?php
/**
 * Coding Day 2026 - Application Entry Point
 * Simple router for all requests
 */

// Define base paths
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');

// Get the URI
$uri = $_SERVER['REQUEST_URI'];
$uri = str_replace('/coding-day-app', '', $uri);
$uri = strtok($uri, '?'); // Remove query string
$uri = rtrim($uri, '/');
$uri = $uri ?: '/';

// Route to the appropriate page
if ($uri === '/' || $uri === '') {
    require_once APP_PATH . '/views/index.php';
} elseif ($uri === '/login') {
    require_once APP_PATH . '/views/login.php';
} elseif ($uri === '/logout') {
    require_once CONFIG_PATH . '/auth.php';
    logoutUser();
} elseif ($uri === '/leaderboard') {
    require_once APP_PATH . '/views/leaderboard.php';
} elseif ($uri === '/peserta') {
    require_once APP_PATH . '/views/peserta_dashboard.php';
} elseif ($uri === '/panitia') {
    require_once APP_PATH . '/views/panitia_dashboard.php';
} elseif ($uri === '/juri') {
    require_once APP_PATH . '/views/juri_dashboard.php';
} elseif ($uri === '/submit') {
    require_once APP_PATH . '/controllers/handle_submission.php';
} elseif ($uri === '/verify') {
    require_once APP_PATH . '/controllers/handle_verification.php';
} elseif ($uri === '/score') {
    require_once APP_PATH . '/controllers/handle_scoring.php';
} else {
    // Default to home
    require_once APP_PATH . '/views/index.php';
}
?>
