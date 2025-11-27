<?php
include 'includes/db_config.php';

$admin_id = 101; 

$total_verified = 0;
$teams = [];
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_team') {
    $new_team_name = $_POST['team_name'] ?? '';
    $leader_email = $_POST['leader_email'] ?? '';

    if (!empty($new_team_name) && !empty($leader_email)) {
        try {
            $pdo->beginTransaction();
            
            $dummy_hash = password_hash('password123', PASSWORD_DEFAULT);
            $stmt_user = $pdo->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, 'PESERTA')");
            $stmt_user->execute([$leader_email, $dummy_hash]);
            $new_leader_id = $pdo->lastInsertId();

            $stmt_team = $pdo->prepare("INSERT INTO teams (team_name, leader_id, is_verified) VALUES (?, ?, 0)");
            $stmt_team->execute([$new_team_name, $new_leader_id]);
            
            $pdo->commit();
            $success_message = "Tim" . htmlspecialchars($new_team_name) . "berhasil ditambahkan!";
            
        } catch (\PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() === '23000') { // Duplicate entry
                $error_message = "Gagal: Email Leader sudah terdaftar.";
            } else {
                $error_message = "Gagal menambah tim: " . $e->getMessage();
            }
        }
    } else {
        $error_message = "Nama tim dan email leader wajib diisi.";
    }
}

try {
    
    $pdo->exec("CALL count_verified_teams(@total);");
    $result = $pdo->query("SELECT @total AS total_verified");
    $data = $result->fetch();
    $total_verified = $data['total_verified'] ?? 0;
    
    $stmt_teams = $pdo->query("
        SELECT t.id, t.team_name, t.is_verified, u.email AS leader_email
        FROM teams t
        JOIN users u ON t.leader_id = u.id
        ORDER BY t.id DESC
    ");
    $teams = $stmt_teams->fetchAll();

    if (isset($_GET['success'])) {
        $success_message = "Tim berhasil diverifikasi! (Log dicatat oleh Trigger)";
    }
    if (isset($_GET['error'])) {
         $error_message = "Terjadi kesalahan pada aksi sebelumnya.";
    }

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
        <a class="navbar-brand" href="panitia_dashboard.php">
          <i class="bi bi-person-fill-lock me-2"></i> Admin Panel - Panitia (ID: <?= $admin_id ?>)
        </a>
      </div>
    </nav>

    <div class="container my-4">
        <h2 class="mb-4">Dashboard Administrasi Lomba 📝</h2>

        <?php if ($success_message): ?> <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div> <?php endif; ?>
        <?php if ($error_message): ?> <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div> <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-5">
                <div class="card bg-success text-white shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Total Tim Terverifikasi (Hasil Stored Procedure)</h5>
                        <p class="card-text fs-1">
                            <?= $total_verified ?> 
                        </p>
                        <p class="card-text small">(`CALL count_verified_teams`) dijalankan.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card shadow-lg h-100">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-person-plus-fill me-1"></i> Form Pendaftaran Tim Baru
                    </div>
                    <div class="card-body">
                        <form method="POST" action="panitia_dashboard.php">
                            <input type="hidden" name="action" value="add_team">
                            <div class="mb-2">
                                <label for="team_name" class="form-label small">Nama Tim:</label>
                                <input type="text" class="form-control" id="team_name" name="team_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="leader_email" class="form-label small">Email Leader (Login Peserta):</label>
                                <input type="email" class="form-control" id="leader_email" name="leader_email" required>
                                <div class="form-text">Password default: `password123`</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Tambahkan Tim</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-light">
                Daftar Tim Peserta (Verifikasi)
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID Tim</th>
                            <th>Nama Tim</th>
                            <th>Email Leader</th>
                            <th>Status</th>
                            <th>Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teams as $team): ?>
                        <tr>
                            <td><?= htmlspecialchars($team['id']) ?></td>
                            <td><?= htmlspecialchars($team['team_name']) ?></td>
                            <td><?= htmlspecialchars($team['leader_email']) ?></td>
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
                                <small class="text-success">Sudah Diverifikasi</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="mt-3 text-end"><a href="view_logs.php" class="btn btn-outline-secondary btn-sm">Lihat Log Audit (Hasil Trigger)</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>