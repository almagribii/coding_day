<?php
include 'includes/db_config.php';

$total_verified_teams = 0;

try {
    $pdo->exec("CALL count_verified_teams(@total);");
    
    $result = $pdo->query("SELECT @total AS total_verified");
    $data = $result->fetch();
    
    $total_verified_teams = $data['total_verified'];

    echo "Jumlah Tim Terverifikasi: " . $total_verified_teams;

} catch (PDOException $e) {
    echo "Error saat memanggil Stored Procedure: " . $e->getMessage();
}
?>