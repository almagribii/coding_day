<?php
/**
 * PHP Built-in Server Router
 * This routes all requests through index.php for proper handling
 */

$requestUri = $_SERVER['REQUEST_URI'];

// Remove the application base path
$requestUri = str_replace('/coding-day-app', '', $requestUri);
$requestUri = parse_url($requestUri, PHP_URL_PATH);

// Handle query string and fragments
if (strpos($requestUri, '?') !== false) {
    $requestUri = strtok($requestUri, '?');
}
if (strpos($requestUri, '#') !== false) {
    $requestUri = strtok($requestUri, '#');
}

// Serve static files directly
$staticExtensions = ['.js', '.css', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.woff', '.woff2', '.ttf', '.eot', '.ico'];
foreach ($staticExtensions as $ext) {
    if (substr($requestUri, -strlen($ext)) === $ext) {
        $filePath = __DIR__ . $requestUri;
        if (file_exists($filePath)) {
            return false; // Let PHP serve the static file
        }
    }
}

// Handle logout route specifically
if ($requestUri === '/logout') {
    require_once __DIR__ . '/config/auth.php';
    logoutUser();
    exit;
}

// Check if file exists directly (for explicit requests to app views/controllers)
if (preg_match('/\.(php|html)$/', $requestUri)) {
    // Try to serve from app/views or app/controllers
    $appPath = str_replace(['/login', '/logout', '/leaderboard', '/peserta', '/panitia', '/juri', '/submit', '/verify', '/score'], '', $requestUri);
    
    $filePath = __DIR__ . $requestUri;
    if (file_exists($filePath) && is_file($filePath)) {
        return false; // Let PHP serve the file
    }
}

// Route everything else through index.php
$_GET['REQUEST_URI'] = $requestUri;
require_once __DIR__ . '/index.php';
?>
