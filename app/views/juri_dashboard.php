<?php
require_once 'includes/auth.php';
require_once 'includes/db_config.php';

requireRole('JURI');

$user = getCurrentUser();
$pageTitle = 'Dashboard Juri';

$submissions = [];
$stats = ['total' => 0, 'rated' => 0, 'pending' => 0];

try {
    // Get submission statistics - ALL submissions (tidak filtered by juri)
    $stmt_stats = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'RATED' THEN 1 ELSE 0 END) as rated,
            SUM(CASE WHEN status = 'READY_TO_RATE' THEN 1 ELSE 0 END) as pending
        FROM submissions
        WHERE status IN ('READY_TO_RATE', 'RATED')
    ");
    $stats = $stmt_stats->fetch();
    
    // Get all submissions available for juri to rate
    // Show all submissions with status READY_TO_RATE or RATED
    // Show current juri's score if exists
    $stmt = $pdo->prepare("
        SELECT 
            s.id, 
            s.team_id,
            t.team_name, 
            s.gdrive_link, 
            s.submitted_at, 
            s.status, 
            sc.score, 
            sc.comments,
            sc.rated_at
        FROM submissions s
        JOIN teams t ON s.team_id = t.id
        LEFT JOIN scores sc ON s.id = sc.submission_id AND sc.jury_user_id = ?
        WHERE s.status IN ('READY_TO_RATE', 'RATED')
        ORDER BY 
            CASE WHEN sc.score IS NULL THEN 0 ELSE 1 END ASC,
            s.submitted_at DESC
    ");
    $stmt->execute([$user['id']]);
    $submissions = $stmt->fetchAll();

} catch (\PDOException $e) {
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

include 'includes/header.php';
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
                                    <?= date('d M Y, H:i', strtotime($sub['submitted_at'])) ?>
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
                                <form action="handle_scoring.php" method="POST" class="row g-2 align-items-end">
                                    <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
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
                                            <i class="bi bi-clock"></i> <?= date('d M Y', strtotime($sub['rated_at'])) ?>
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
<script src="assets/js/main.js"></script>
</body>
</html>
