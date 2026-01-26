<?php
/**
 * Coding Day 2026 - Application Entry Point
 * 
 * Main router untuk semua request
 * Struktur direktori:
 * - app/views/ : Halaman dan dashboard
 * - app/controllers/ : Handler untuk submission, scoring, verification
 * - config/ : Database, auth, dan konfigurasi lainnya
 * - public/ : CSS, JS, dan assets
 * - migrations/ : Database schema dan seeds
 */

// Define base paths
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Load configuration files
require_once CONFIG_PATH . '/db_config.php';
require_once CONFIG_PATH . '/mongo_config.php';
require_once CONFIG_PATH . '/auth.php';
require_once CONFIG_PATH . '/header.php';

// Get request URI
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = str_replace('/coding-day-app', '', $request_uri);
$request_uri = rtrim($request_uri, '/') ?: '/';

// Define routes
$routes = [
    '/' => 'app/views/index.php',
    '/login' => 'app/views/login.php',
    '/logout' => 'app/views/logout.php',
    '/leaderboard' => 'app/views/leaderboard.php',
    '/peserta' => 'app/views/peserta_dashboard.php',
    '/panitia' => 'app/views/panitia_dashboard.php',
    '/juri' => 'app/views/juri_dashboard.php',
    '/submit' => 'app/controllers/handle_submission.php',
    '/verify' => 'app/controllers/handle_verification.php',
    '/score' => 'app/controllers/handle_scoring.php',
    '/stats' => 'app/controllers/handle_stats.php',
];

// Route the request
if (isset($routes[$request_uri]) && file_exists($routes[$request_uri])) {
    require_once $routes[$request_uri];
} else {
    // Default to home
    require_once 'app/views/index.php';
}
?>
