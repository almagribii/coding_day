<?php
include 'includes/db_config.php';

$team_id = 8; 
$team_name = "alansin";
$is_verified = false; 

$last_submission = null;

try {
    $stmt_team = $pdo->prepare("SELECT is_verified FROM teams WHERE id = ?");
    $stmt_team->execute([$team_id]);
    $team_data = $stmt_team->fetch();
    
    if ($team_data) {
        $is_verified = $team_data['is_verified'];
    }

    // 2. Ambil Submission Terakhir
    $stmt_submission = $pdo->prepare("SELECT gdrive_link, submitted_at FROM submissions WHERE team_id = ? ORDER BY submitted_at DESC LIMIT 1");
    $stmt_submission->execute([$team_id]);
    $last_submission = $stmt_submission->fetch();

} catch (\PDOException $e) {
    die("<h1>❌ DATABASE ERROR ❌</h1><p>Gagal memuat data tim/submission: " . $e->getMessage() . "</p>");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Peserta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <nav class="navbar navbar-light bg-light shadow-sm">
        <div class="container-fluid container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-laptop me-2"></i> Dashboard Tim **<?= htmlspecialchars($team_name) ?>**
            </a>
        </div>
    </nav>

    <div class="container my-4">
        <h2 class="mb-4">Pengumpulan Hasil Lomba 💻</h2>

        <p>Status Tim: 
            <span class="badge <?= $is_verified ? 'bg-success' : 'bg-warning text-dark' ?>">
                <?= $is_verified ? 'VERIFIED' : 'PENDING' ?>
            </span>
        </p>

        <?php if ($is_verified): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    Kumpulkan Proyek Anda
                </div>
                <div class="card-body">
                    <form action="handle_submission.php" method="POST">
                        <input type="hidden" name="team_id" value="<?= $team_id ?>">
                        
                        <div class="mb-3">
                            <label for="gdrive_link" class="form-label">Tautan Google Drive (Wajib Public Link):</label>
                            <input type="url" class="form-control" id="gdrive_link" name="gdrive_link" 
                                   placeholder="Contoh: https://drive.google.com/drive/folders/..." required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Kumpulkan Tugas</button>
                    </form>
                </div>
            </div>

            <h3 class="mb-3">Riwayat Pengumpulan Terakhir</h3>
            <div class="alert alert-secondary shadow-sm">
                <?php if ($last_submission): ?>
                    <p>Dikumpulkan pada: <strong><?= htmlspecialchars($last_submission['submitted_at']) ?></strong></p>
                    <p>Link: <a href="<?= htmlspecialchars($last_submission['gdrive_link']) ?>" target="_blank" class="alert-link">Lihat Proyek</a></p>
                <?php else: ?>
                    <p>Belum ada tugas yang dikumpulkan.</p>
                <?php endif; ?>
            </div>
            
        <?php else: ?>
            <div class="alert alert-warning mt-4">
                Tim Anda masih dalam status **PENDING**. Silakan tunggu verifikasi dari Panitia.
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>