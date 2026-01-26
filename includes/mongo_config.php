<?php
// includes/mongo_config.php

// Memanggil library MongoDB hasil composer
// Menggunakan __DIR__ agar path-nya akurat dimanapun file ini dipanggil
require_once __DIR__ . '/../vendor/autoload.php'; 

$logCollection = null;

try {
    // 1. Konek ke MongoDB
    $mongoClient = new MongoDB\Client("mongodb://localhost:27017");
    
    // 2. Pilih Database 'coding_day_logs' & Collection 'activity_logs'
    // (Database ini akan otomatis dibuat oleh MongoDB saat data pertama masuk)
    $logCollection = $mongoClient->coding_day_logs->activity_logs;
    
} catch (Exception $e) {
    // Kosongkan saja. Jika MongoDB error, kita tidak mau merusak aplikasi utama (MySQL).
}
?>