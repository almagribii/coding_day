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

// Handle update team
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_team') {
    $team_id = filter_var($_POST['team_id'] ?? 0, FILTER_VALIDATE_INT);
    $updated_team_name = $_POST['team_name'] ?? '';
    $updated_leader_email = $_POST['leader_email'] ?? '';

    if ($team_id && !empty($updated_team_name) && !empty($updated_leader_email)) {
        try {
            $pdo->beginTransaction();
            
            // Update team name
            $stmt_team = $pdo->prepare("UPDATE teams SET team_name = ? WHERE id = ?");
            $stmt_team->execute([$updated_team_name, $team_id]);
            
            // Update leader email
            $stmt_leader = $pdo->prepare("
                UPDATE users u
                JOIN teams t ON u.id = t.leader_id
                SET u.email = ?
                WHERE t.id = ?
            ");
            $stmt_leader->execute([$updated_leader_email, $team_id]);
            
            $pdo->commit();
            $success_message = "Tim berhasil diupdate!";
            
        } catch (\PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() === '23000') {
                $error_message = "Gagal: Email sudah digunakan tim lain.";
            } else {
                $error_message = "Gagal update tim: " . $e->getMessage();
            }
        }
    } else {
        $error_message = "Data tidak lengkap untuk update tim.";
    }
}

// Handle delete team
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_team') {
    $team_id = filter_var($_POST['team_id'] ?? 0, FILTER_VALIDATE_INT);

    if ($team_id) {
        try {
            $pdo->beginTransaction();
            
            // Disable foreign key checks temporarily for cascade delete
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Get leader_id before deleting team
            $stmt_get = $pdo->prepare("SELECT leader_id FROM teams WHERE id = ?");
            $stmt_get->execute([$team_id]);
            $leader_data = $stmt_get->fetch();
            
            if ($leader_data) {
                // Get all submission IDs for this team
                $stmt_get_subs = $pdo->prepare("SELECT id FROM submissions WHERE team_id = ?");
                $stmt_get_subs->execute([$team_id]);
                $submission_ids = $stmt_get_subs->fetchAll(PDO::FETCH_COLUMN);
                
                // Delete scores for each submission
                if (!empty($submission_ids)) {
                    $placeholders = implode(',', array_fill(0, count($submission_ids), '?'));
                    $stmt_score = $pdo->prepare("DELETE FROM scores WHERE submission_id IN ($placeholders)");
                    $stmt_score->execute($submission_ids);
                }
                
                // Delete submissions
                $stmt_sub = $pdo->prepare("DELETE FROM submissions WHERE team_id = ?");
                $stmt_sub->execute([$team_id]);
                
                // Delete verification_logs
                $stmt_verif = $pdo->prepare("DELETE FROM verification_logs WHERE team_id = ?");
                $stmt_verif->execute([$team_id]);
                
                // Delete team
                $stmt_team = $pdo->prepare("DELETE FROM teams WHERE id = ?");
                $stmt_team->execute([$team_id]);
                
                // Delete user (leader)
                $stmt_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt_user->execute([$leader_data['leader_id']]);
                
                // Re-enable foreign key checks
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                
                $pdo->commit();
                $success_message = "Tim berhasil dihapus!";
            } else {
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                $pdo->rollBack();
                $error_message = "Tim tidak ditemukan.";
            }
            
        } catch (\PDOException $e) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            $pdo->rollBack();
            $error_message = "Gagal menghapus tim: " . $e->getMessage();
        }
    } else {
        $error_message = "ID tim tidak valid.";
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

.dashboard-header {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #30363d;
}

.card {
    margin-bottom: 2rem;
    border: 1px solid #30363d;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-header {
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #161b22 0%, #1c2128 100%);
    border-bottom: 1px solid #30363d;
}

.card-body {
    padding: 1.5rem;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    padding: 1rem;
    font-weight: 600;
    border-bottom: 2px solid #30363d;
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
}

/* Action buttons styling */
.btn-group-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    white-space: nowrap;
}

td .btn-sm {
    margin: 0.25rem;
}

/* Form styling */
.form-control {
    padding: 0.625rem 0.875rem;
}

/* Alert styling */
.alert {
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    border-radius: 0.5rem;
}

/* Modal improvements */
.modal-content {
    border-radius: 0.75rem;
    border: 1px solid #30363d;
}

.modal-header {
    padding: 1.25rem 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
}

/* Badge spacing */
.badge {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 600;
}

/* Stat box improvements */
.stat-box {
    padding: 1.75rem;
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
                                <div class="btn-group-actions">
                                    <?php if (!$team['is_verified']): ?>
                                        <form method="POST" action="/coding-day-app/verify" style="display:inline-block; margin: 0;">
                                            <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                            <input type="hidden" name="admin_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check"></i> Verifikasi
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-all"></i> Verified
                                        </span>
                                    <?php endif; ?>
                                    
                                    <!-- Edit Button -->
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $team['id'] ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    
                                    <!-- Delete Button -->
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $team['id'] ?>">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modals -->
<?php foreach ($teams as $team): ?>
<div class="modal fade" id="editModal<?= $team['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Tim</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_team">
                    <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Tim</label>
                        <input type="text" class="form-control" name="team_name" value="<?= htmlspecialchars($team['team_name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email Leader</label>
                        <input type="email" class="form-control" name="leader_email" value="<?= htmlspecialchars($team['leader_email']) ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Delete Modals -->
<?php foreach ($teams as $team): ?>
<div class="modal fade" id="deleteModal<?= $team['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash"></i> Hapus Tim</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_team">
                    <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i> 
                        <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                    </div>
                    
                    <p>Apakah Anda yakin ingin menghapus tim berikut?</p>
                    <ul>
                        <li><strong>Nama Tim:</strong> <?= htmlspecialchars($team['team_name']) ?></li>
                        <li><strong>Email Leader:</strong> <?= htmlspecialchars($team['leader_email']) ?></li>
                        <li><strong>Submission:</strong> <?= $team['submission_count'] ?> file</li>
                    </ul>
                    <p class="text-danger">
                        <small><i class="bi bi-info-circle"></i> Semua data submission dan scores tim ini akan ikut terhapus.</small>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Ya, Hapus Tim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/coding-day-app/public/js/main.js"></script>
</body>
</html>
