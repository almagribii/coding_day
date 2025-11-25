<?php
include 'includes/db_config.php';

$total_verified = 0;
$teams = [];
$admin_id = 101; 

try {
   
    $pdo->exec("CALL count_verified_teams(@total);");
    $result = $pdo->query("SELECT @total AS total_verified");
    $data = $result->fetch();
    $total_verified = $data['total_verified'] ?? 0;
    
    $stmt_teams = $pdo->query("SELECT id, team_name, is_verified FROM teams");
    $teams = $stmt_teams->fetchAll();

} catch (\PDOException $e) {
    die("<h1>❌ DATABASE ERROR ❌</h1><p>Gagal memuat dashboard: " . $e->getMessage() . "</p>");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Panitia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark shadow">
      <div class="container-fluid container">
        <a class="navbar-brand" href="index.php">
          <i class="bi bi-person-fill-lock me-2"></i> Admin Panel
        </a>
        <span class="navbar-text text-white">
          Selamat Datang, Panitia (ID: <?= $admin_id ?>)
        </span>
      </div>
    </nav>

    <div class="container my-4">
        <h2 class="mb-4">Dashboard Verifikasi Tim 📝</h2>

        <div class="card bg-success text-white mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Total Tim Terverifikasi</h5>
                <p class="card-text fs-1">
                    <?= $total_verified ?> 
                </p>
                <p class="card-text small">(Diambil via Stored Procedure)</p>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                Daftar Tim Peserta (Status)
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID Tim</th>
                            <th>Nama Tim</th>
                            <th>Status</th>
                            <th>Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teams as $team): ?>
                        <tr>
                            <td><?= htmlspecialchars($team['id']) ?></td>
                            <td><?= htmlspecialchars($team['team_name']) ?></td>
                            <td>
                                <?php if ($team['is_verified']): ?>
                                    <span class="badge bg-success">Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$team['is_verified']): ?>
                                <form action="handle_verification.php" method="POST">
                                    <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                    <input type="hidden" name="panitia_id" value="<?= $admin_id ?>"> 
                                    <button type="submit" class="btn btn-sm btn-success">Verifikasi</button>
                                </form>
                                <?php else: ?>
                                Telah Diverifikasi
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="mt-3"><a href="view_logs.php" class="btn btn-outline-secondary btn-sm">Lihat Log Audit (Hasil Trigger)</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>