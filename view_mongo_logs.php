<?php
include 'includes/mongo_config.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Activity Logs (NoSQL)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4">📂 System Activity Logs (NoSQL Data)</h2>
    <div class="alert alert-info">
        Data di bawah ini diambil langsung dari <strong>MongoDB</strong>. 
        Fitur ini mencatat aktivitas user secara real-time terpisah dari MySQL.
    </div>
    
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Waktu</th>
                <th>Event</th>
                <th>Detail Data (JSON Format)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($logCollection) {
                // Ambil semua data, urutkan dari yang paling baru
                $dataLogs = $logCollection->find([], ['sort' => ['waktu' => -1]]);

                foreach ($dataLogs as $doc) {
                    $waktu = $doc['waktu']->toDateTime()->format('d M Y, H:i:s');
                    
                    echo "<tr>";
                    echo "<td>{$waktu}</td>";
                    echo "<td><span class='badge bg-primary'>{$doc['event']}</span></td>";
                    
                    // Hapus ID internal mongo biar tampilan rapi
                    unset($doc['_id']);
                    unset($doc['waktu']);
                    
                    // Tampilkan sisa data sebagai JSON
                    echo "<td><pre class='m-0'>" . json_encode($doc, JSON_PRETTY_PRINT) . "</pre></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3' class='text-danger'>Gagal terhubung ke MongoDB. Cek service-nya!</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <a href="index.php" class="btn btn-secondary mt-3">Kembali ke Dashboard</a>
</div>
</body>
</html>