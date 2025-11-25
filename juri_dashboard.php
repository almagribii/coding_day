<?php
include 'includes/db_config.php';

$jury_id = 301; 
$submissions = [];

try {
    $stmt = $pdo->prepare("
        SELECT 
            s.id, t.team_name, s.gdrive_link, s.submitted_at, s.status, sc.score, sc.comments
        FROM submissions s
        JOIN teams t ON s.team_id = t.id
        LEFT JOIN scores sc ON s.id = sc.submission_id AND sc.jury_user_id = :jury_id
        WHERE s.status IN ('READY_TO_RATE', 'RATED')
        ORDER BY s.submitted_at DESC
    ");
    $stmt->execute([':jury_id' => $jury_id]);
    $submissions = $stmt->fetchAll();

} catch (\PDOException $e) {
    die("<h1>❌ DATABASE ERROR ❌</h1><p>Gagal memuat data penilaian: " . $e->getMessage() . "</p>");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Juri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-secondary shadow">
      <div class="container-fluid container">
        <a class="navbar-brand" href="index.php">
          <i class="bi bi-star-fill me-2"></i> Dashboard Penilaian Juri
        </a>
        <span class="navbar-text text-white">
          Selamat Datang, Juri (ID: <?= $jury_id ?>)
        </span>
      </div>
    </nav>

    <div class="container my-4">
        <h2 class="mb-4">Daftar Tugas Siap Dinilai ⭐</h2>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nama Tim</th>
                        <th>Link Proyek</th>
                        <th>Status</th>
                        <th>Aksi / Nilai Anda</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr><td colspan="5" class="text-center">Tidak ada tugas yang siap untuk dinilai.</td></tr>
                    <?php else: ?>
                        <?php foreach ($submissions as $sub): ?>
                        <tr>
                            <td><?= htmlspecialchars($sub['id']) ?></td>
                            <td><strong><?= htmlspecialchars($sub['team_name']) ?></strong></td>
                            <td>
                                <a href="<?= htmlspecialchars($sub['gdrive_link']) ?>" target="_blank" class="btn btn-sm btn-info">
                                    <i class="bi bi-box-arrow-up-right"></i> Lihat Proyek
                                </a>
                            </td>
                            <td>
                                <?php if ($sub['score']): ?>
                                    <span class="badge bg-primary">Sudah Dinilai</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Siap Dinilai</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="handle_scoring.php" method="POST">
                                    <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                                    <input type="hidden" name="jury_id" value="<?= $jury_id ?>">
                                    
                                    <div class="input-group">
                                        <span class="input-group-text">Nilai (0-100)</span>
                                        <input type="number" class="form-control" name="score" min="0" max="100" 
                                               value="<?= htmlspecialchars($sub['score'] ?? '') ?>" required>
                                    </div>
                                    <div class="form-text mb-2">
                                        Komentar: <textarea class="form-control form-control-sm" name="comments"><?= htmlspecialchars($sub['comments'] ?? '') ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <?= $sub['score'] ? 'Update Nilai' : 'Simpan Nilai' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>