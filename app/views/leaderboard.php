<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db_config.php';

$pageTitle = 'Leaderboard - Coding Day 2026';

try {
    // Get team rankings with average scores
    // Include teams even if they don't have ratings yet
    $stmt = $pdo->query("
        SELECT 
            t.id,
            t.team_name,
            t.is_verified,
            COUNT(DISTINCT s.id) as total_submissions,
            AVG(sc.score) as avg_score,
            MAX(sc.score) as max_score,
            MIN(sc.score) as min_score,
            COUNT(DISTINCT sc.id) as total_ratings
        FROM teams t
        LEFT JOIN submissions s ON t.id = s.team_id
        LEFT JOIN scores sc ON s.id = sc.submission_id
        WHERE t.is_verified = 1
        GROUP BY t.id, t.team_name, t.is_verified
        ORDER BY 
            CASE WHEN avg_score IS NULL THEN 1 ELSE 0 END ASC,
            avg_score DESC, 
            total_submissions DESC,
            t.team_name ASC
    ");
    $rankings = $stmt->fetchAll();
    
} catch (\PDOException $e) {
    $error = 'Error: ' . $e->getMessage();
}

$additionalCSS = '<style>
.leaderboard-header {
    background: linear-gradient(135deg, #161b22 0%, #1f2937 100%);
    padding: 3rem 0;
    text-align: center;
    margin-bottom: 2rem;
    border-radius: 12px;
    border: 1px solid var(--border-color);
}
.rank-item {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}
.rank-item:hover {
    transform: translateY(-4px);
    border-color: var(--accent-blue);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
}
.rank-badge {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    flex-shrink: 0;
}
.rank-1 { background: linear-gradient(135deg, #FFD700, #FFA500); color: #1a1a1a; text-shadow: 0 1px 2px rgba(0,0,0,0.3); }
.rank-2 { background: linear-gradient(135deg, #E8E8E8, #B0B0B0); color: #1a1a1a; text-shadow: 0 1px 2px rgba(0,0,0,0.3); }
.rank-3 { background: linear-gradient(135deg, #CD7F32, #8B4513); color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.3); }
.rank-other { background: var(--secondary-bg); border: 2px solid var(--border-color); color: var(--text-white); }
.team-info {
    flex-grow: 1;
}
.score-display {
    font-size: 2rem;
    font-weight: 700;
    color: var(--accent-green);
}
</style>';

include __DIR__ . '/../../config/header.php';
?>

<div class="container my-4">
    <div class="leaderboard-header">
        <h1 class="mb-3">
            <i class="bi bi-trophy-fill" style="color: #FFD700;"></i>
            Leaderboard
        </h1>
        <p class="text-muted mb-0">Ranking Tim Berdasarkan Rata-rata Nilai</p>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if (empty($rankings)): ?>
        <div class="text-center py-5">
            <i class="bi bi-trophy" style="font-size: 5rem; color: var(--text-muted);"></i>
            <p class="text-muted mt-3">Belum ada tim yang mendapatkan nilai</p>
        </div>
    <?php else: ?>
        <?php 
        $ranked_count = 0;
        foreach ($rankings as $index => $team): 
            // Only count for ranking if has score
            if ($team['avg_score'] !== null) {
                $ranked_count++;
            }
            $rank = $team['avg_score'] !== null ? $ranked_count : null;
            $rankClass = ($rank && $rank <= 3) ? "rank-{$rank}" : 'rank-other';
            $medal = $rank && $rank <= 3 ? ['🥇', '🥈', '🥉'][$rank - 1] : '';
        ?>
            <div class="rank-item <?= $team['avg_score'] === null ? 'opacity-75' : '' ?>">
                <div class="rank-badge <?= $rankClass ?>">
                    <?= $medal ?: ($rank ? "#{$rank}" : "—") ?>
                </div>
                <div class="team-info">
                    <h5 class="mb-1">
                        <?= htmlspecialchars($team['team_name']) ?>
                        <?php if (!$team['is_verified']): ?>
                            <small class="badge ms-2" style="background: var(--accent-orange); color: white;">Not Verified</small>
                        <?php endif; ?>
                    </h5>
                    <div class="text-muted small">
                        <i class="bi bi-file-earmark-code"></i> <?= $team['total_submissions'] ?> Submissions &nbsp;|&nbsp;
                        <i class="bi bi-star-fill"></i> <?= $team['total_ratings'] ?> Ratings
                    </div>
                    <?php if ($team['total_ratings'] > 0): ?>
                        <div class="mt-2">
                            <span class="badge bg-info me-1">Max: <?= number_format($team['max_score'], 1) ?></span>
                            <span class="badge" style="background: var(--accent-orange); color: white;">Min: <?= number_format($team['min_score'], 1) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="mt-2">
                            <span class="badge bg-secondary">Menunggu penilaian...</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-end">
                    <?php if ($team['avg_score'] !== null): ?>
                        <div class="score-display"><?= number_format($team['avg_score'], 1) ?></div>
                        <small class="text-muted">Rata-rata</small>
                    <?php else: ?>
                        <div class="score-display" style="color: var(--text-muted);">—</div>
                        <small class="text-muted">Belum Dinilai</small>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <div class="text-center mt-4">
        <a href="<?= isLoggedIn() ? ($_SESSION['role'] === 'PESERTA' ? 'peserta_dashboard.php' : ($_SESSION['role'] === 'PANITIA' ? 'panitia_dashboard.php' : 'juri_dashboard.php')) : 'index.php' ?>" 
           class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/coding-day-app/public/js/main.js"></script>
</body>
</html>
