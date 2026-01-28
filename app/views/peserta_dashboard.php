<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/mongo_config.php';

requireRole('PESERTA');

$user = getCurrentUser();
$pageTitle = 'Dashboard Peserta';

// Get team information
$team_id = null;
$team_name = 'Belum Terdaftar';
$is_verified = false;
$leader_id = null;

try {
    $userObjectId = new MongoDB\BSON\ObjectId($user['id']);
    
    // Get team info where user is leader
    $team_data = $teamsCollection->findOne([
        'leader_id' => $userObjectId
    ]);
    
    if ($team_data) {
        $team_id = (string)$team_data['_id'];
        $team_name = $team_data['team_name'];
        $is_verified = $team_data['is_verified'] ?? false;
        $leader_id = $team_data['leader_id'];
    }
    
    // Get submission history
    $submissions = [];
    if ($team_id) {
        $teamObjectId = new MongoDB\BSON\ObjectId($team_id);
        $submissionsCursor = $submissionsCollection->find(
            ['team_id' => $teamObjectId],
            ['sort' => ['submitted_at' => -1]]
        );
        $submissions = $submissionsCursor->toArray();
    }
    
    // Get latest scores with aggregation
    $scores = [];
    if ($team_id) {
        $teamObjectId = new MongoDB\BSON\ObjectId($team_id);
        
        $scoresPipeline = [
            [
                '$lookup' => [
                    'from' => 'submissions',
                    'localField' => 'submission_id',
                    'foreignField' => '_id',
                    'as' => 'submission'
                ]
            ],
            [
                '$unwind' => '$submission'
            ],
            [
                '$match' => [
                    'submission.team_id' => $teamObjectId
                ]
            ],
            [
                '$lookup' => [
                    'from' => 'users',
                    'localField' => 'jury_user_id',
                    'foreignField' => '_id',
                    'as' => 'jury'
                ]
            ],
            [
                '$unwind' => '$jury'
            ],
            [
                '$sort' => ['rated_at' => -1]
            ],
            [
                '$limit' => 5
            ],
            [
                '$project' => [
                    'score' => 1,
                    'comments' => 1,
                    'rated_at' => 1,
                    'jury_email' => '$jury.email'
                ]
            ]
        ];
        
        $scores = $scoresCollection->aggregate($scoresPipeline)->toArray();
    }
    
    // Calculate average score
    $avg_score = 0;
    if (!empty($scores)) {
        $avg_score = array_sum(array_column($scores, 'score')) / count($scores);
    }
    
} catch (Exception $e) {
    $error_message = 'Error: ' . $e->getMessage();
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
    background: radial-gradient(circle, rgba(88, 166, 255, 0.1), transparent);
    border-radius: 50%;
    z-index: 0;
}

.dashboard-header > div {
    position: relative;
    z-index: 1;
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
    font-size: 1.1rem;
    color: #e0e0e0;
}

.stat-box {
    background: linear-gradient(135deg, var(--card-bg) 0%, var(--secondary-bg) 100%);
    border: 2px solid var(--border-color);
    border-radius: 16px;
    padding: 2rem 1.5rem;
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
    background: radial-gradient(circle, rgba(88, 166, 255, 0.1), transparent);
    border-radius: 50%;
}

.stat-box:hover {
    transform: translateY(-8px) scale(1.02);
    border-color: var(--accent-blue-light);
    box-shadow: 0 16px 32px rgba(88, 166, 255, 0.15);
}

.stat-box i {
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
    color: var(--accent-blue-light);
}

.stat-box:hover i {
    transform: scale(1.15) rotateZ(-10deg);
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
    color: #c0c0c0;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 700;
    position: relative;
    z-index: 1;
}

.submission-item {
    background: linear-gradient(135deg, var(--card-bg) 0%, var(--secondary-bg) 100%);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.submission-item:hover {
    border-color: var(--accent-blue-light);
    transform: translateX(6px);
    box-shadow: 0 8px 16px rgba(88, 166, 255, 0.15);
}

.card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.card:hover {
    border-color: var(--accent-blue);
}

.card-body {
    color: #e0e0e0;
}

.card-header {
    background: linear-gradient(135deg, rgba(88, 166, 255, 0.05) 0%, rgba(137, 87, 229, 0.05) 100%);
    border-bottom: 2px solid var(--border-color);
}

.card-header h5 {
    color: #e0e0e0;
}

.form-control::placeholder {
    color: #a0a0a0 !important;
    opacity: 1;
}
</style>';

include __DIR__ . '/../../config/header.php';
?>

<div class="container my-5">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8 mb-3 mb-md-0">
                <h1 class="mb-2">
                    <i class="bi bi-laptop"></i> Dashboard Peserta
                </h1>
                <p class="mb-0">
                    <i class="bi bi-people-fill"></i> Tim: <strong><?= htmlspecialchars($team_name) ?></strong>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <?php if ($is_verified): ?>
                    <span class="status-badge status-verified">
                        <i class="bi bi-check-circle-fill"></i> TERDAFTAR
                    </span>
                <?php else: ?>
                    <span class="status-badge status-pending">
                        <i class="bi bi-clock-fill"></i> PENDING
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i><?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <!-- Statistics Grid -->
    <div class="row mb-5">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-box">
                <i class="bi bi-file-earmark-code" style="font-size: 2.5rem; color: var(--accent-blue);"></i>
                <div class="stat-value" style="color: var(--accent-blue);">
                    <?= count($submissions) ?>
                </div>
                <div class="stat-label">Submission</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-box">
                <i class="bi bi-star-fill" style="font-size: 2.5rem; color: var(--accent-yellow);"></i>
                <div class="stat-value" style="color: var(--accent-yellow);">
                    <?= number_format($avg_score, 1) ?>
                </div>
                <div class="stat-label">Rata-rata Nilai</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-box">
                <i class="bi bi-award-fill" style="font-size: 2.5rem; color: var(--accent-green);"></i>
                <div class="stat-value" style="color: var(--accent-green);">
                    <?= count($scores) ?>
                </div>
                <div class="stat-label">Penilaian Diterima</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-box">
                <i class="bi bi-shield-check" style="font-size: 2.5rem; color: var(--accent-purple);"></i>
                <div class="stat-value" style="color: var(--accent-purple);">
                    <?= $is_verified ? '✓' : '✗' ?>
                </div>
                <div class="stat-label">Verifikasi</div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="row"
        <!-- Left Column - Submission Form -->
        <div class="col-lg-8 mb-4">
            <?php if ($is_verified): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0" style="color: #e0e0e0;">
                            <i class="bi bi-cloud-upload"></i> Upload Submission
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="/submit" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="team_id" value="<?= $team_id ?>">
                            
                            <div class="mb-3">
                                <label for="gdrive_link" class="form-label" style="color: #e0e0e0;">
                                    <i class="bi bi-link-45deg"></i> Google Drive Link (Public Access)
                                </label>
                                <input type="url" class="form-control" id="gdrive_link" name="gdrive_link" 
                                       placeholder="https://drive.google.com/drive/folders/..." required>
                                <div class="form-text" style="color: #ffffff;">
                                    Pastikan link dapat diakses oleh siapa saja (Anyone with the link can view)
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Submit Proyek
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Submission History -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0" style="color: #ffffff;">
                            <i class="bi bi-clock-history"></i> Riwayat Submission
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($submissions)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: var(--text-muted);"></i>
                                <p class="text-muted mt-2">Belum ada submission</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($submissions as $sub): ?>
                                <div class="submission-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1" style="color: #e0e0e0;">
                                                <i class="bi bi-file-earmark-code" style="color: var(--accent-blue);"></i>
                                                Submission #<?= (string)$sub['_id'] ?>
                                            </h6>
                                            <p class="mb-2" style="font-size: 0.875rem; color: #b0b0b0;">
                                                <i class="bi bi-calendar"></i> <?= $sub['submitted_at']->toDateTime()->format('d M Y, H:i') ?>
                                            </p>
                                            <a href="<?= htmlspecialchars($sub['gdrive_link']) ?>" target="_blank" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-box-arrow-up-right"></i> Lihat Proyek
                                            </a>
                                        </div>
                                        <div>
                                            <?php
                                            $statusBadge = [
                                                'PENDING' => 'pending-status',
                                                'READY_TO_RATE' => 'bg-info',
                                                'RATED' => 'bg-success'
                                            ];
                                            $badge = $statusBadge[$sub['status']] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge <?= $badge ?>"><?= $sub['status'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning alert-permanent">
                    <h5>
                        <i class="bi bi-exclamation-triangle-fill"></i> Tim Belum Terverifikasi
                    </h5>
                    <p class="mb-0">Silakan tunggu verifikasi dari panitia sebelum dapat mengumpulkan submission.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Right Column - Scores -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0" style="color: #e0e0e0;">
                        <i class="bi bi-star-fill" style="color: var(--accent-orange);"></i> Nilai Terbaru
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($scores)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-star" style="font-size: 3rem; color: var(--text-muted);"></i>
                            <p class="text-muted mt-2">Belum ada penilaian</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($scores as $score): ?>
                            <div class="submission-item mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span style="font-size: 0.875rem; color: #ffffff !important; font-weight: 600;">
                                        <i class="bi bi-person"></i> <?= htmlspecialchars(explode('@', $score['jury_email'])[0]) ?>
                                    </span>
                                    <span class="badge bg-primary" style="font-size: 1rem;">
                                        <?= $score['score'] ?>/100
                                    </span>
                                </div>
                                <?php if ($score['comments']): ?>
                                    <p class="mb-1" style="font-size: 0.875rem; color: #d0d0d0;">
                                        <i class="bi bi-chat-left-text"></i> <?= htmlspecialchars($score['comments']) ?>
                                    </p>
                                <?php endif; ?>
                                <small style="color: #ffffff !important; font-weight: 500;">
                                    <i class="bi bi-clock"></i> <?= $score['rated_at']->toDateTime()->format('d M Y') ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Team Info -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0" style="color: #e0e0e0;">
                        <i class="bi bi-info-circle"></i> Info Tim
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong style="color: #e0e0e0;">Nama Tim:</strong><br>
                        <span style="color: #d0d0d0;"><?= htmlspecialchars($team_name) ?></span>
                    </div>
                    <div class="mb-2">
                        <strong style="color: #e0e0e0;">Status:</strong><br>
                        <?= $is_verified ? '<span class="text-success">✓ Terverifikasi</span>' : '<span class="text-warning">⏳ Menunggu Verifikasi</span>' ?>
                    </div>
                    <div>
                        <strong style="color: #e0e0e0;">Team ID:</strong><br>
                        <span style="color: #d0d0d0;">#<?= $team_id ?? '-' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/public/js/main.js"></script>
</body>
</html>
