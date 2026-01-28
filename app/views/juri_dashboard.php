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
    border-color: rgba(88, 166, 255, 0.5);
    box-shadow: 0 16px 32px rgba(88, 166, 255, 0.15);
}

.stat-value {
    font-size: 2.8rem;
    font-weight: 800;
    margin: 1rem 0;
    position: relative;
    z-index: 1;
    color: var(--accent-blue-light);
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

.rating-card {
    background: linear-gradient(135deg, #161b22 0%, #0d1117 100%);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.75rem;
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
}

.rating-card:hover {
    border-color: rgba(88, 166, 255, 0.5);
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(88, 166, 255, 0.15);
}

.team-name {
    color: var(--accent-blue-light);
    font-size: 1.1rem;
    font-weight: 700;
}

.submission-meta {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin: 0.75rem 0;
}

.card-header {
    background: linear-gradient(135deg, rgba(30, 90, 100, 0.3) 0%, rgba(20, 60, 80, 0.3) 100%);
    border-bottom: 2px solid var(--border-color);
    color: var(--accent-blue-light);
    font-weight: 700;
}
</style>';

include __DIR__ . '/../../config/header.php';
?>

<div class="container my-5">
    <div class="dashboard-header">
        <h1 class="mb-2">
            <i class="bi bi-star-fill"></i> Dashboard Juri
        </h1>
        <p class="mb-0">Evaluasi submission dan berikan penilaian untuk tim peserta</p>
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
    <div class="row mb-5">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stat-box">
                <i class="bi bi-file-earmark-code" style="font-size: 2.5rem; color: var(--accent-blue);"></i>
                <div class="stat-value" style="color: var(--accent-blue);"><?= $stats['total'] ?></div>
                <div class="stat-label">Total Submission</div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stat-box">
                <i class="bi bi-check-circle" style="font-size: 2.5rem; color: var(--accent-green);"></i>
                <div class="stat-value" style="color: var(--accent-green);"><?= $stats['rated'] ?></div>
                <div class="stat-label">Sudah Dinilai</div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="stat-box">
                <i class="bi bi-hourglass-split" style="font-size: 2.5rem; color: var(--accent-orange);"></i>
                <div class="stat-value" style="color: var(--accent-orange);"><?= $stats['pending'] ?></div>
                <div class="stat-label">Menunggu Penilaian</div>
            </div>
        </div>
    </div>
    
    <!-- Submissions List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-list-check"></i> Daftar Submission untuk Dinilai</h5>
        </div>
        <div class="card-body p-4">
            <?php if (empty($submissions)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: var(--text-muted);"></i>
                    <p class="text-muted mt-3">Tidak ada submission yang perlu dinilai</p>
                </div>
            <?php else: ?>
                <?php foreach ($submissions as $sub): ?>
                    <div class="rating-card">
                        <div class="row align-items-center g-3">
                            <div class="col-lg-5">
                                <div class="team-name">
                                    <i class="bi bi-people-fill"></i>
                                    <?= htmlspecialchars($sub['team_name']) ?>
                                </div>
                                <div class="submission-meta">
                                    <i class="bi bi-calendar-event"></i> 
                                    <?= $sub['submitted_at']->toDateTime()->format('d M Y, H:i') ?>
                                </div>
                                <div class="submission-meta">
                                    <?php 
                                    if ($sub['status'] === 'READY_TO_RATE') {
                                        echo '<span style="color: var(--accent-orange);"><i class="bi bi-hourglass"></i> Menunggu Penilaian</span>';
                                    } else {
                                        echo '<span style="color: var(--accent-green);"><i class="bi bi-check-circle"></i> Sudah Dinilai</span>';
                                    }
                                    ?>
                                </div>
                                <div class="mt-3">
                                    <a href="<?= htmlspecialchars($sub['gdrive_link']) ?>" target="_blank" 
                                       class="btn btn-sm btn-enhanced btn-primary-enhanced">
                                        <i class="bi bi-box-arrow-up-right"></i> Buka Proyek
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <form action="/score" method="POST">
                                    <input type="hidden" name="submission_id" value="<?= (string)$sub['_id'] ?>">
                                    <input type="hidden" name="jury_id" value="<?= $user['id'] ?>">
                                    
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label class="form-label small mb-2"><i class="bi bi-star-fill"></i> Nilai</label>
                                            <input type="number" class="form-control" name="score" 
                                                   min="0" max="100" value="<?= $sub['score'] ?? '' ?>" required
                                                   placeholder="0-100" style="background: var(--secondary-bg); color: var(--text-white); border-color: var(--border-color);">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label small mb-2"><i class="bi bi-chat-left"></i> Feedback</label>
                                            <input type="text" class="form-control" name="comments" 
                                                   placeholder="Komentar..." value="<?= htmlspecialchars($sub['comments'] ?? '') ?>"
                                                   maxlength="500" style="background: var(--secondary-bg); color: var(--text-white); border-color: var(--border-color);">
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="submit" class="btn btn-enhanced btn-success-enhanced w-100">
                                                <i class="bi bi-save"></i> 
                                                <?= $sub['score'] ? 'Update' : 'Simpan' ?>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <?php if ($sub['score']): ?>
                                    <div class="mt-2 pt-3 border-top border-secondary small">
                                        <div class="text-success d-flex align-items-center gap-2">
                                            <i class="bi bi-check-circle-fill"></i> Nilai Anda: <strong><?= $sub['score'] ?>/100</strong>
                                        </div>
                                        <div class="mt-1" style="color: #ffffff !important;">
                                            <i class="bi bi-clock"></i> <?= $sub['rated_at']->toDateTime()->format('d M Y, H:i') ?>
                                        </div>
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
<script src="/public/js/main.js"></script>
</body>
</html>
