<?php
// config/mongo_config.php

// Initialize MongoDB collection variables
$mongoClient = null;
$usersCollection = null;
$teamsCollection = null;
$submissionsCollection = null;
$scoresCollection = null;
$verificationLogsCollection = null;
$activityLogsCollection = null;

// Auto-load composer dependencies
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    die("Composer autoload not found. Please run: composer install");
}

// Check if MongoDB extension is available
if (!extension_loaded('mongodb')) {
    // Provide helpful error message with instructions
    $error_msg = "
    <h2>MongoDB Extension Not Available</h2>
    <p>The PHP MongoDB extension is not loaded in your PHP installation.</p>
    <h3>For LAMPP/XAMPP PHP 8.0 users:</h3>
    <pre style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>
# Install dependencies
sudo apt-get install -y autoconf build-essential libssl-dev

# Download MongoDB extension 1.12.0 (compatible with PHP 8.0)
cd /tmp
wget https://pecl.php.net/get/mongodb-1.12.0.tgz
tar xzf mongodb-1.12.0.tgz
cd mongodb-1.12.0

# Configure and compile for LAMPP PHP 8.0
/opt/lampp/bin/phpize
./configure --with-php-config=/opt/lampp/bin/php-config
make
sudo make install

# Enable extension
echo 'extension=mongodb.so' | sudo tee -a /opt/lampp/etc/php.ini

# Restart LAMPP
sudo /opt/lampp/lampp restart
    </pre>
    
    <h3>For system PHP:</h3>
    <pre style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>
sudo apt-get install -y php-mongodb
# or
sudo pecl install mongodb
    </pre>
    
    <p><strong>Current PHP Version:</strong> " . PHP_VERSION . "</p>
    <p><strong>PHP Binary:</strong> " . PHP_BINARY . "</p>
    ";
    
    die($error_msg);
}

try {
    // 1. Konek ke MongoDB
    $mongoClient = new MongoDB\Client("mongodb://localhost:27017");
    
    // 2. Pilih Database 'coding_day' & Collections
    $db = $mongoClient->coding_day;
    
    // Main application collections
    $usersCollection = $db->users;
    $teamsCollection = $db->teams;
    $submissionsCollection = $db->submissions;
    $scoresCollection = $db->scores;
    $verificationLogsCollection = $db->verification_logs;
    
    // Activity logs collection
    $activityLogsCollection = $db->activity_logs;
    
    // Create indexes for better performance (only once)
    try {
        // Users: unique email index
        $usersCollection->createIndex(['email' => 1], ['unique' => true]);
        
        // Teams: indexes
        $teamsCollection->createIndex(['leader_id' => 1]);
        $teamsCollection->createIndex(['is_verified' => 1]);
        
        // Submissions: indexes
        $submissionsCollection->createIndex(['team_id' => 1]);
        $submissionsCollection->createIndex(['status' => 1]);
        
        // Scores: indexes
        $scoresCollection->createIndex(['submission_id' => 1]);
        $scoresCollection->createIndex(['jury_user_id' => 1]);
        
        // Verification logs: indexes
        $verificationLogsCollection->createIndex(['team_id' => 1]);
        $verificationLogsCollection->createIndex(['admin_id' => 1]);
    } catch (Exception $e) {
        // Ignore duplicate index errors
        if (strpos($e->getMessage(), 'Index already exists') === false) {
            error_log("MongoDB index creation warning: " . $e->getMessage());
        }
    }
    
} catch (Exception $e) {
    error_log("MongoDB connection failed: " . $e->getMessage());
    die("<h2>MongoDB Connection Failed</h2><p>Error: " . htmlspecialchars($e->getMessage()) . "</p><p>Please ensure MongoDB is running: <code>sudo systemctl start mongod</code></p>");
}
?>