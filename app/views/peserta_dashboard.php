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
    background: linear-gradient(135deg, #161b22 0%, #1f2937 100%);
    padding: 2rem 0;
    margin-bottom: 2rem;
    border-radius: 12px;
    border: 1px solid var(--border-color);
}
.stat-box {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
}
.stat-box:hover {
    transform: translateY(-4px);
    border-color: var(--accent-blue);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
}
.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0.5rem 0;
}
.stat-label {
    color: var(--text-muted);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.submission-item {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}
.submission-item:hover {
    border-color: var(--accent-blue);
    transform: translateX(4px);
}
</style>';

include __DIR__ . '/../../config/header.php';
?>

<div class="container my-4">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="bi bi-laptop" style="color: var(--accent-blue);"></i>
                        Dashboard Peserta
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="bi bi-people-fill"></i> Tim: <strong><?= htmlspecialchars($team_name) ?></strong>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php if ($is_verified): ?>
                        <span class="badge bg-success" style="font-size: 1rem; padding: 0.5rem 1rem;">
                            <i class="bi bi-check-circle-fill"></i> VERIFIED
                        </span>
                    <?php else: ?>
                        <span class="badge" style="background: var(--accent-orange); color: white; font-size: 1rem; padding: 0.5rem 1rem;">
                            <i class="bi bi-clock-fill"></i> PENDING
                        </span>
                    <?php endif; ?>
                </div>
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
    
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-box">
                <i class="bi bi-file-earmark-code" style="font-size: 2rem; color: var(--accent-blue);"></i>
                <div class="stat-value" style="color: var(--accent-blue);">
                    <?= count($submissions) ?>
                </div>
                <div class="stat-label">Total Submissions</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-box">
                <i class="bi bi-star-fill" style="font-size: 2rem; color: var(--accent-orange);"></i>
                <div class="stat-value" style="color: var(--accent-orange);">
                    <?= number_format($avg_score, 1) ?>
                </div>
                <div class="stat-label">Rata-rata Nilai</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-box">
                <i class="bi bi-trophy-fill" style="font-size: 2rem; color: var(--accent-green);"></i>
                <div class="stat-value" style="color: var(--accent-green);">
                    <?= count($scores) ?>
                </div>
                <div class="stat-label">Penilaian Masuk</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-box">
                <i class="bi bi-shield-check" style="font-size: 2rem; color: var(--accent-purple);"></i>
                <div class="stat-value" style="color: var(--accent-purple);">
                    <?= $is_verified ? 'YES' : 'NO' ?>
                </div>
                <div class="stat-label">Status Verifikasi</div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="row">
        <!-- Left Column - Submission Form -->
        <div class="col-lg-8 mb-4">
            <?php if ($is_verified): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-cloud-upload"></i> Upload Submission
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="/coding-day-app/submit" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="team_id" value="<?= $team_id ?>">
                            
                            <div class="mb-3">
                                <label for="gdrive_link" class="form-label">
                                    <i class="bi bi-link-45deg"></i> Google Drive Link (Public Access)
                                </label>
                                <input type="url" class="form-control" id="gdrive_link" name="gdrive_link" 
                                       placeholder="https://drive.google.com/drive/folders/..." required>
                                <div class="form-text">
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
                        <h5 class="mb-0">
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
                                            <h6 class="mb-1">
                                                <i class="bi bi-file-earmark-code" style="color: var(--accent-blue);"></i>
                                                Submission #<?= (string)$sub['_id'] ?>
                                            </h6>
                                            <p class="text-muted mb-2" style="font-size: 0.875rem;">
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
                    <h5 class="mb-0">
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
                                    <span class="text-muted" style="font-size: 0.875rem;">
                                        <i class="bi bi-person"></i> <?= htmlspecialchars(explode('@', $score['jury_email'])[0]) ?>
                                    </span>
                                    <span class="badge bg-primary" style="font-size: 1rem;">
                                        <?= $score['score'] ?>/100
                                    </span>
                                </div>
                                <?php if ($score['comments']): ?>
                                    <p class="mb-1" style="font-size: 0.875rem;">
                                        <i class="bi bi-chat-left-text"></i> <?= htmlspecialchars($score['comments']) ?>
                                    </p>
                                <?php endif; ?>
                                <small class="text-muted">
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
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Info Tim
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Nama Tim:</strong><br>
                        <?= htmlspecialchars($team_name) ?>
                    </div>
                    <div class="mb-2">
                        <strong>Status:</strong><br>
                        <?= $is_verified ? '<span class="text-success">✓ Terverifikasi</span>' : '<span class="text-warning">⏳ Menunggu Verifikasi</span>' ?>
                    </div>
                    <div>
                        <strong>Team ID:</strong><br>
                        #<?= $team_id ?? '-' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/coding-day-app/public/js/main.js"></script>
</body>
</html>
