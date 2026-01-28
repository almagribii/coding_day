<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/mongo_config.php';

requireRole('JURI');

$user = getCurrentUser();
$pageTitle = 'Dashboard Juri';

$submissions = [];
$stats = ['total' => 0, 'rated' => 0, 'pending' => 0];

try {
    $userObjectId = new MongoDB\BSON\ObjectId($user['id']);
    
    // Get submission statistics - ALL submissions
    $stats['total'] = $submissionsCollection->countDocuments([
        'status' => ['$in' => ['READY_TO_RATE', 'RATED']]
    ]);
    
    $stats['rated'] = $submissionsCollection->countDocuments([
        'status' => 'RATED'
    ]);
    
    $stats['pending'] = $submissionsCollection->countDocuments([
        'status' => 'READY_TO_RATE'
    ]);
    
    // Get all submissions with aggregation for team name and jury's score
    $submissionsPipeline = [
        [
            '$match' => [
                'status' => ['$in' => ['READY_TO_RATE', 'RATED']]
            ]
        ],
        [
            '$lookup' => [
                'from' => 'teams',
                'localField' => 'team_id',
                'foreignField' => '_id',
                'as' => 'team'
            ]
        ],
        [
            '$unwind' => '$team'
        ],
        [
            '$lookup' => [
                'from' => 'scores',
                'let' => ['submissionId' => '$_id'],
                'pipeline' => [
                    [
                        '$match' => [
                            '$expr' => [
                                '$and' => [
                                    ['$eq' => ['$submission_id', '$$submissionId']],
                                    ['$eq' => ['$jury_user_id', $userObjectId]]
                                ]
                            ]
                        ]
                    ]
                ],
                'as' => 'jury_score'
            ]
        ],
        [
            '$addFields' => [
                'score' => ['$arrayElemAt' => ['$jury_score.score', 0]],
                'comments' => ['$arrayElemAt' => ['$jury_score.comments', 0]],
                'rated_at' => ['$arrayElemAt' => ['$jury_score.rated_at', 0]]
            ]
        ],
        [
            '$project' => [
                '_id' => 1,
                'team_id' => 1,
                'team_name' => '$team.team_name',
                'gdrive_link' => 1,
                'submitted_at' => 1,
                'status' => 1,
                'score' => 1,
                'comments' => 1,
                'rated_at' => 1
            ]
        ],
        [
            '$sort' => [
                'score' => 1,  // null values first (unrated)
                'submitted_at' => -1
            ]
        ]
    ];
    
    $submissions = $submissionsCollection->aggregate($submissionsPipeline)->toArray();

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

$additionalCSS = '<style>
.rating-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}
.rating-card:hover {
    border-color: var(--accent-blue);
    transform: translateY(-2px);
}
</style>';

include __DIR__ . '/../../config/header.php';
?>

<div class="container my-4">
    <div class="dashboard-header">
        <h1 class="mb-2">
            <i class="bi bi-star-fill" style="color: var(--accent-orange);"></i>
            Dashboard Juri
        </h1>
        <p class="text-muted mb-0">Berikan penilaian untuk submission peserta</p>
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
        <div class="col-md-4 mb-3">
            <div class="stat-box">
                <i class="bi bi-file-earmark-code" style="font-size: 2rem; color: var(--accent-blue);"></i>
                <div class="stat-value" style="color: var(--accent-blue);"><?= $stats['total'] ?></div>
                <div class="stat-label">Total Submission</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-box">
                <i class="bi bi-check-circle" style="font-size: 2rem; color: var(--accent-green);"></i>
                <div class="stat-value" style="color: var(--accent-green);"><?= $stats['rated'] ?></div>
                <div class="stat-label">Sudah Dinilai</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-box">
                <i class="bi bi-clock" style="font-size: 2rem; color: var(--accent-orange);"></i>
                <div class="stat-value" style="color: var(--accent-orange);"><?= $stats['pending'] ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>
    
    <!-- Submissions List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-check"></i> Daftar Submission</h5>
        </div>
        <div class="card-body">
            <?php if (empty($submissions)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: var(--text-muted);"></i>
                    <p class="text-muted mt-3">Tidak ada submission yang perlu dinilai</p>
                </div>
            <?php else: ?>
                <?php foreach ($submissions as $sub): ?>
                    <div class="rating-card">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <h6 class="mb-2">
                                    <i class="bi bi-people-fill" style="color: var(--accent-blue);"></i>
                                    <strong><?= htmlspecialchars($sub['team_name']) ?></strong>
                                </h6>
                                <small class="text-muted d-block mb-2">
                                    <i class="bi bi-calendar"></i> 
                                    <?= $sub['submitted_at']->toDateTime()->format('d M Y, H:i') ?>
                                </small>
                                <small class="text-muted d-block mb-3">
                                    <?php 
                                    $status_text = [
                                        'READY_TO_RATE' => '⏳ Menunggu Penilaian',
                                        'RATED' => '✓ Sudah Dinilai'
                                    ];
                                    echo ($status_text[$sub['status']] ?? $sub['status']);
                                    ?>
                                </small>
                                <a href="<?= htmlspecialchars($sub['gdrive_link']) ?>" target="_blank" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-box-arrow-up-right"></i> Buka Proyek
                                </a>
                            </div>
                            <div class="col-md-7">
                                <form action="/coding-day-app/score" method="POST" class="row g-2 align-items-end">
                                    <input type="hidden" name="submission_id" value="<?= (string)$sub['_id'] ?>">
                                    <input type="hidden" name="jury_id" value="<?= $user['id'] ?>">
                                    
                                    <div class="col-md-3">
                                        <label class="form-label small mb-1"><i class="bi bi-star-fill"></i> Nilai</label>
                                        <input type="number" class="form-control" name="score" 
                                               min="0" max="100" value="<?= $sub['score'] ?? '' ?>" required
                                               placeholder="0-100">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small mb-1"><i class="bi bi-chat-left"></i> Komentar</label>
                                        <input type="text" class="form-control" name="comments" 
                                               placeholder="Feedback..." value="<?= htmlspecialchars($sub['comments'] ?? '') ?>"
                                               maxlength="500">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="bi bi-save"></i> 
                                            <?= $sub['score'] ? 'Update' : 'Simpan' ?>
                                        </button>
                                    </div>
                                </form>
                                <?php if ($sub['score']): ?>
                                    <div class="mt-2 pt-2 border-top border-secondary">
                                        <small class="text-success d-block">
                                            <i class="bi bi-check-circle-fill"></i> Nilai Anda: <strong><?= $sub['score'] ?>/100</strong>
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-clock"></i> <?= $sub['rated_at']->toDateTime()->format('d M Y') ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/coding-day-app/public/js/main.js"></script>
</body>
</html>
