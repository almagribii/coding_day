<?php
require 'vendor/autoload.php'; // Memanggil library hasil install composer tadi

try {
    // Coba panggil class Mongo
    // Jika driver (dll) belum terpasang, baris ini akan langsung ERROR
    $client = new MongoDB\Client("mongodb://localhost:27017");
    
    // Coba ping database
    $client->admin->command(['ping' => 1]);

    echo "<h1>✅ SUKSES!</h1>";
    echo "<p>Driver MongoDB terdeteksi dan Koneksi Berhasil.</p>";
    echo "<p>Versi Library: " . \MongoDB\Client::VERSION . "</p>";

} catch (Exception $e) {
    echo "<h1>❌ GAGAL / ERROR</h1>";
    echo "<p>Pesan Error: " . $e->getMessage() . "</p>";
    echo "<hr>";
    echo "<h3>Solusi Jika Error 'Class MongoDB\Client not found':</h3>";
    echo "<ul>
            <li>Pastikan file <b>php_mongodb.dll</b> sudah ada di folder <code>xampp/php/ext/</code></li>
            <li>Pastikan sudah menulis <code>extension=mongodb</code> di file <b>php.ini</b></li>
            <li>Jangan lupa <b>RESTART APACHE</b> di XAMPP Control Panel.</li>
          </ul>";
}
?>