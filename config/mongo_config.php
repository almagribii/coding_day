<?php
// includes/mongo_config.php

// Initialize MongoDB collection variable
$logCollection = null;

// Check if MongoDB extension is available
if (extension_loaded('mongodb')) {
    try {
        // Memanggil library MongoDB hasil composer
        // Menggunakan __DIR__ agar path-nya akurat dimanapun file ini dipanggil
        require_once __DIR__ . '/../vendor/autoload.php'; 
        
        // 1. Konek ke MongoDB
        $mongoClient = new MongoDB\Client("mongodb://localhost:27017");
        
        // 2. Pilih Database 'coding_day_logs' & Collection 'activity_logs'
        // (Database ini akan otomatis dibuat oleh MongoDB saat data pertama masuk)
        $logCollection = $mongoClient->coding_day_logs->activity_logs;
        
    } catch (Exception $e) {
        // Kosongkan saja. Jika MongoDB error, kita tidak mau merusak aplikasi utama (MySQL).
        error_log("MongoDB connection failed: " . $e->getMessage());
    }
} else {
    // MongoDB extension not loaded - application will work with MySQL only
    error_log("MongoDB extension not available - logging to MongoDB disabled");
}
?>