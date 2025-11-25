<?php
include 'includes/db_config.php'; 

$logs = [];
try {
    $stmt = $pdo->query("
        SELECT 
            vl.id, vl.verified_at, t.team_name, vl.admin_id
        FROM 
            verification_logs vl
        JOIN 
            teams t ON vl.team_id = t.id
        ORDER BY 
            vl.verified_at DESC
    ");
    $logs = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("<h1>❌ DATABASE ERROR ❌</h1><p>Gagal mengambil data log: " . $e->getMessage() . "</p>");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Verifikasi Tim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container my-4">
        <h2 class="mb-4">History Log Verifikasi Tim ✅</h2>
        <p class="text-muted">Data ini adalah hasil input otomatis dari **Trigger** database.</p>

        <a href="panitia_dashboard.php" class="btn btn-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>

        <?php if (count($logs) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Log</th>
                            <th>Nama Tim</th>
                            <th>ID Panitia (Pemicu)</th>
                            <th>Waktu Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['id']) ?></td>
                            <td><strong><?= htmlspecialchars($log['team_name']) ?></strong></td>
                            <td><?= htmlspecialchars($log['admin_id']) ?></td>
                            <td><?= htmlspecialchars($log['verified_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info mt-4">Belum ada aktivitas verifikasi yang dicatat oleh Trigger.</div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>