<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db_config.php';

requireRole('PANITIA');

$user = getCurrentUser();
$pageTitle = 'Dashboard Panitia';

$total_teams = 0;
$total_verified = 0;
$total_submissions = 0;
$teams = [];
$success_message = '';
$error_message = '';

// Handle add team
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_team') {
    $new_team_name = $_POST['team_name'] ?? '';
    $leader_email = $_POST['leader_email'] ?? '';

    if (!empty($new_team_name) && !empty($leader_email)) {
        try {
            $pdo->beginTransaction();
            
            $dummy_hash = password_hash('password', PASSWORD_DEFAULT);
            $stmt_user = $pdo->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, 'PESERTA')");
            $stmt_user->execute([$leader_email, $dummy_hash]);
            $new_leader_id = $pdo->lastInsertId();

            $stmt_team = $pdo->prepare("INSERT INTO teams (team_name, leader_id, is_verified) VALUES (?, ?, 0)");
            $stmt_team->execute([$new_team_name, $new_leader_id]);
            
            $pdo->commit();
            $success_message = "Tim " . htmlspecialchars($new_team_name) . " berhasil ditambahkan!";
            
        } catch (\PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() === '23000') {
                $error_message = "Gagal: Email sudah terdaftar.";
            } else {
                $error_message = "Gagal menambah tim: " . $e->getMessage();
            }
        }
    } else {
        $error_message = "Nama tim dan email wajib diisi.";
    }
}

try {
    // Get statistics
    $stats = $pdo->query("
        SELECT 
            COUNT(*) as total_teams,
            SUM(is_verified) as verified_teams
        FROM teams
    ")->fetch();
    
    $total_teams = $stats['total_teams'] ?? 0;
    $total_verified = $stats['verified_teams'] ?? 0;
    
    $total_submissions = $pdo->query("SELECT COUNT(*) as total FROM submissions")->fetch()['total'];
    
    // Get all teams
    $stmt_teams = $pdo->query("
        SELECT t.id, t.team_name, t.is_verified, u.email AS leader_email,
               (SELECT COUNT(*) FROM submissions WHERE team_id = t.id) as submission_count
        FROM teams t
        JOIN users u ON t.leader_id = u.id
        ORDER BY t.id DESC
    ");
    $teams = $stmt_teams->fetchAll();

    if (isset($_GET['success'])) {
        $success_message = "Tim berhasil diverifikasi!";
    }

} catch (\PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$additionalCSS = '<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
</style>';

include __DIR__ . '/../../config/header.php';
?>

<div class="container my-4">
    <div class="dashboard-header">
        <h1 class="mb-2">
            <i class="bi bi-shield-lock" style="color: var(--accent-green);"></i>
            Dashboard Panitia
        </h1>
        <p class="text-muted mb-0">Kelola tim dan verifikasi peserta</p>
    </div>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i><?= $success_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-box">
            <i class="bi bi-people-fill" style="font-size: 2rem; color: var(--accent-blue);"></i>
            <div class="stat-value" style="color: var(--accent-blue);"><?= $total_teams ?></div>
            <div class="stat-label">Total Tim</div>
        </div>
        <div class="stat-box">
            <i class="bi bi-check-circle-fill" style="font-size: 2rem; color: var(--accent-green);"></i>
            <div class="stat-value" style="color: var(--accent-green);"><?= $total_verified ?></div>
            <div class="stat-label">Terverifikasi</div>
        </div>
        <div class="stat-box">
            <i class="bi bi-clock-fill" style="font-size: 2rem; color: var(--accent-orange);"></i>
            <div class="stat-value" style="color: var(--accent-orange);"><?= $total_teams - $total_verified ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-box">
            <i class="bi bi-file-earmark-code" style="font-size: 2rem; color: var(--accent-purple);"></i>
            <div class="stat-value" style="color: var(--accent-purple);"><?= $total_submissions ?></div>
            <div class="stat-label">Total Submission</div>
        </div>
    </div>
    
    <!-- Add Team Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Tambah Tim Baru</h5>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="action" value="add_team">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="team_name" placeholder="Nama Tim" required>
                </div>
                <div class="col-md-5">
                    <input type="email" class="form-control" name="leader_email" placeholder="Email Leader" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-plus"></i> Tambah</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Teams List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Daftar Tim</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Tim</th>
                            <th>Email Leader</th>
                            <th>Submission</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teams as $team): ?>
                        <tr>
                            <td><?= $team['id'] ?></td>
                            <td><strong><?= htmlspecialchars($team['team_name']) ?></strong></td>
                            <td><?= htmlspecialchars($team['leader_email']) ?></td>
                            <td><span class="badge bg-info"><?= $team['submission_count'] ?></span></td>
                            <td>
                                <?php if ($team['is_verified']): ?>
                                    <span class="badge bg-success">VERIFIED</span>
                                <?php else: ?>
                                    <span class="badge" style="background: var(--accent-orange); color: white;">PENDING</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$team['is_verified']): ?>
                                    <form method="POST" action="/coding-day-app/verify" style="display:inline;">
                                        <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                        <input type="hidden" name="admin_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-check"></i> Verifikasi
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="bi bi-check-all"></i> Sudah Verified
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/coding-day-app/public/js/main.js"></script>
</body>
</html>
