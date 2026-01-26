<?php

$host = 'localhost';
$db   = 'coding_day';
$user = 'root'; 
$pass = 'xampp'; 
$charset = 'utf8mb4';
$socket = '/opt/lampp/var/mysql/mysql.sock';

$dsn = "mysql:unix_socket=$socket;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Koneksi database gagal: " . $e->getMessage()); 
}
?>