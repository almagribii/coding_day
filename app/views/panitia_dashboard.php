<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/mongo_config.php';

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
            // Check if email already exists
            $existingUser = $usersCollection->findOne(['email' => $leader_email]);
            if ($existingUser) {
                $error_message = "Gagal: Email sudah terdaftar.";
            } else {
                // Insert user
                $dummy_hash = password_hash('password', PASSWORD_DEFAULT);
                $insertUserResult = $usersCollection->insertOne([
                    'email' => $leader_email,
                    'password_hash' => $dummy_hash,
                    'role' => 'PESERTA',
                    'created_at' => new MongoDB\BSON\UTCDateTime()
                ]);
                $new_leader_id = $insertUserResult->getInsertedId();

                // Insert team
                $teamsCollection->insertOne([
                    'team_name' => $new_team_name,
                    'leader_id' => $new_leader_id,
                    'is_verified' => false,
                    'created_at' => new MongoDB\BSON\UTCDateTime()
                ]);
                
                $success_message = "Tim " . htmlspecialchars($new_team_name) . " berhasil ditambahkan!";
            }
        } catch (Exception $e) {
            $error_message = "Gagal menambah tim: " . $e->getMessage();
        }
    } else {
        $error_message = "Nama tim dan email wajib diisi.";
    }
}

// Handle update team
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_team') {
    $team_id = $_POST['team_id'] ?? '';
    $updated_team_name = $_POST['team_name'] ?? '';
    $updated_leader_email = $_POST['leader_email'] ?? '';

    if (!empty($team_id) && !empty($updated_team_name) && !empty($updated_leader_email)) {
        try {
            $teamObjectId = new MongoDB\BSON\ObjectId($team_id);
            
            // Get team to find leader
            $team = $teamsCollection->findOne(['_id' => $teamObjectId]);
            if ($team) {
                // Check if email is already used by another user
                $existingUser = $usersCollection->findOne([
                    'email' => $updated_leader_email,
                    '_id' => ['$ne' => $team['leader_id']]
                ]);
                
                if ($existingUser) {
                    $error_message = "Gagal: Email sudah digunakan tim lain.";
                } else {
                    // Update team name
                    $teamsCollection->updateOne(
                        ['_id' => $teamObjectId],
                        ['$set' => ['team_name' => $updated_team_name]]
                    );
                    
                    // Update leader email
                    $usersCollection->updateOne(
                        ['_id' => $team['leader_id']],
                        ['$set' => ['email' => $updated_leader_email]]
                    );
                    
                    $success_message = "Tim berhasil diupdate!";
                }
            } else {
                $error_message = "Tim tidak ditemukan.";
            }
        } catch (Exception $e) {
            $error_message = "Gagal update tim: " . $e->getMessage();
        }
    } else {
        $error_message = "Data tidak lengkap untuk update tim.";
    }
}

// Handle delete team
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_team') {
    $team_id = $_POST['team_id'] ?? '';

    if (!empty($team_id)) {
        try {
            $teamObjectId = new MongoDB\BSON\ObjectId($team_id);
            
            // Get team data before deleting
            $team = $teamsCollection->findOne(['_id' => $teamObjectId]);
            
            if ($team) {
                // Delete all scores for submissions of this team
                $submissions = $submissionsCollection->find(['team_id' => $teamObjectId])->toArray();
                $submissionIds = array_map(function($s) { return $s['_id']; }, $submissions);
                
                if (!empty($submissionIds)) {
                    $scoresCollection->deleteMany(['submission_id' => ['$in' => $submissionIds]]);
                }
                
                // Delete submissions
                $submissionsCollection->deleteMany(['team_id' => $teamObjectId]);
                
                // Delete verification_logs
                $verificationLogsCollection->deleteMany(['team_id' => $teamObjectId]);
                
                // Delete team
                $teamsCollection->deleteOne(['_id' => $teamObjectId]);
                
                // Delete user (leader)
                $usersCollection->deleteOne(['_id' => $team['leader_id']]);
                
                $success_message = "Tim berhasil dihapus!";
            } else {
                $error_message = "Tim tidak ditemukan.";
            }
        } catch (Exception $e) {
            $error_message = "Gagal menghapus tim: " . $e->getMessage();
        }
    } else {
        $error_message = "ID tim tidak valid.";
    }
}

try {
    // Get statistics
    $total_teams = $teamsCollection->countDocuments([]);
    $total_verified = $teamsCollection->countDocuments(['is_verified' => true]);
    $total_submissions = $submissionsCollection->countDocuments([]);
    
    // Get all teams with aggregation to join with users and count submissions
    $teams = $teamsCollection->aggregate([
        [
            '$lookup' => [
                'from' => 'users',
                'localField' => 'leader_id',
                'foreignField' => '_id',
                'as' => 'leader'
            ]
        ],
        [
            '$unwind' => '$leader'
        ],
        [
            '$lookup' => [
                'from' => 'submissions',
                'localField' => '_id',
                'foreignField' => 'team_id',
                'as' => 'submissions'
            ]
        ],
        [
            '$addFields' => [
                'submission_count' => ['$size' => '$submissions']
            ]
        ],
        [
            '$project' => [
                '_id' => 1,
                'team_name' => 1,
                'is_verified' => 1,
                'leader_email' => '$leader.email',
                'submission_count' => 1
            ]
        ],
        [
            '$sort' => ['_id' => -1]
        ]
    ])->toArray();

    if (isset($_GET['success'])) {
        $success_message = "Tim berhasil diverifikasi!";
    }

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

$additionalCSS = '<style>
.dashboard-header {
    background: linear-gradient(135deg, rgba(88, 166, 255, 0.05) 0%, rgba(137, 87, 229, 0.05) 100%);
    padding: 2.5rem 2rem;
    margin-bottom: 2rem;
    border-radius: 16px;
    border: 2px solid var(--border-light);
    position: relative;
    overflow: hidden;
}

.dashboard-header::before {
    content: "";
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(88, 166, 255, 0.08), transparent);
    border-radius: 50%;
    z-index: 0;
}

.dashboard-header h1 {
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--accent-blue-light) 0%, var(--accent-purple-light) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 0.5rem 0;
    font-family: "JetBrains Mono", monospace;
}

.dashboard-header p {
    color: var(--text-light);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.stat-box {
    background: linear-gradient(135deg, var(--card-bg) 0%, var(--secondary-bg) 100%);
    border: 2px solid var(--border-color);
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.stat-box::before {
    content: "";
    position: absolute;
    top: -50%;
    right: -20%;
    width: 150px;
    height: 150px;
    background: radial-gradient(circle, rgba(88, 166, 255, 0.08), transparent);
    border-radius: 50%;
}

.stat-box:hover {
    transform: translateY(-8px) scale(1.02);
    border-color: var(--accent-blue-light);
    box-shadow: 0 16px 32px rgba(88, 166, 255, 0.15);
}

.stat-value {
    font-size: 2.8rem;
    font-weight: 800;
    margin: 1rem 0;
    position: relative;
    z-index: 1;
    background: linear-gradient(135deg, var(--accent-blue-light) 0%, var(--accent-purple-light) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 700;
    position: relative;
    z-index: 1;
}

.card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    transition: all 0.3s ease;
    margin-bottom: 2rem;
}

.card:hover {
    border-color: var(--accent-blue);
}

.card-header {
    background: linear-gradient(135deg, rgba(88, 166, 255, 0.05) 0%, rgba(137, 87, 229, 0.05) 100%);
    border-bottom: 2px solid var(--border-color);
    padding: 1.5rem;
    color: var(--accent-blue-light);
    font-weight: 700;
}

.card-body {
    padding: 1.5rem;
}

.form-control::placeholder {
    color: #a0a0a0 !important;
    opacity: 1;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    padding: 1rem;
    font-weight: 700;
    border-bottom: 2px solid var(--border-color);
    color: var(--accent-blue);
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table tbody tr {
    border-bottom: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.table tbody tr:hover {
    background: rgba(88, 166, 255, 0.05);
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
}

.team-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.team-name {
    font-weight: 600;
    color: var(--accent-blue-light);
}

.btn-group-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    white-space: nowrap;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.modal-content {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
}

.modal-header {
    background: linear-gradient(135deg, rgba(88, 166, 255, 0.05), rgba(137, 87, 229, 0.05));
    border-bottom: 2px solid var(--border-color);
    color: var(--accent-blue);
    font-weight: 700;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid var(--border-color);
}

.badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
}

.badge-verified {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.badge-pending {
    background: rgba(249, 115, 22, 0.2);
    color: #f97316;
}

.form-control, .form-select {
    background: var(--secondary-bg);
    border: 1px solid var(--border-color);
    color: var(--text-white);
}

.form-control:focus, .form-select:focus {
    background: var(--secondary-bg);
    border-color: var(--accent-blue);
    color: var(--text-white);
}

.form-label {
    color: var(--text-white);
    font-weight: 600;
}
</style>';

include __DIR__ . '/../../config/header.php';
?>

<div class="container my-5">
    <div class="dashboard-header">
        <h1 class="mb-2">
            <i class="bi bi-shield-lock"></i> Dashboard Panitia
        </h1>
        <p class="mb-0">Kelola tim dan verifikasi peserta yang mengikuti kompetisi</p>
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
            <i class="bi bi-people-fill" style="font-size: 2.5rem; color: var(--accent-blue);"></i>
            <div class="stat-value" style="color: var(--accent-blue);"><?= $total_teams ?></div>
            <div class="stat-label">Total Tim</div>
        </div>
        <div class="stat-box">
            <i class="bi bi-check-circle-fill" style="font-size: 2.5rem; color: var(--accent-green);"></i>
            <div class="stat-value" style="color: var(--accent-green);"><?= $total_verified ?></div>
            <div class="stat-label">Terverifikasi</div>
        </div>
        <div class="stat-box">
            <i class="bi bi-hourglass-split" style="font-size: 2.5rem; color: var(--accent-orange);"></i>
            <div class="stat-value" style="color: var(--accent-orange);"><?= $total_teams - $total_verified ?></div>
            <div class="stat-label">Menunggu Verifikasi</div>
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
                            <td><?= (string)$team['_id'] ?></td>
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
                                        <form method="POST" action="/verify" style="display:inline-block; margin: 0;">
                                            <input type="hidden" name="team_id" value="<?= (string)$team['_id'] ?>">
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
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= (string)$team['_id'] ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    
                                    <!-- Delete Button -->
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= (string)$team['_id'] ?>">
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
<div class="modal fade" id="editModal<?= (string)$team['_id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Tim</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_team">
                    <input type="hidden" name="team_id" value="<?= (string)$team['_id'] ?>">
                    
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
<div class="modal fade" id="deleteModal<?= (string)$team['_id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash"></i> Hapus Tim</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_team">
                    <input type="hidden" name="team_id" value="<?= (string)$team['_id'] ?>">
                    
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
<script src="/public/js/main.js"></script>
</body>
</html>
