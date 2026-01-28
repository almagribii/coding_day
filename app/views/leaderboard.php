<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/mongo_config.php';

$pageTitle = 'Leaderboard - Coding Day 2026';

try {
    // Get team rankings with average scores using MongoDB aggregation
    $leaderboardPipeline = [
        [
            '$match' => [
                'is_verified' => true
            ]
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
                'submission_ids' => '$submissions._id'
            ]
        ],
        [
            '$lookup' => [
                'from' => 'scores',
                'localField' => 'submission_ids',
                'foreignField' => 'submission_id',
                'as' => 'scores'
            ]
        ],
        [
            '$addFields' => [
                'total_submissions' => ['$size' => '$submissions'],
                'avg_score' => ['$avg' => '$scores.score'],
                'max_score' => ['$max' => '$scores.score'],
                'min_score' => ['$min' => '$scores.score'],
                'total_ratings' => ['$size' => '$scores']
            ]
        ],
        [
            '$project' => [
                '_id' => 1,
                'team_name' => 1,
                'is_verified' => 1,
                'total_submissions' => 1,
                'avg_score' => 1,
                'max_score' => 1,
                'min_score' => 1,
                'total_ratings' => 1,
                'has_score' => ['$cond' => [['$gt' => ['$avg_score', null]], 1, 0]]
            ]
        ],
        [
            '$sort' => [
                'has_score' => -1,  // Teams with scores first
                'avg_score' => -1,  // Then by score descending
                'total_submissions' => -1,
                'team_name' => 1
            ]
        ]
    ];
    
    $rankings = $teamsCollection->aggregate($leaderboardPipeline)->toArray();
    
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
}

$additionalCSS = '<style>
.leaderboard-header {
    background: linear-gradient(135deg, rgba(88, 166, 255, 0.05) 0%, rgba(137, 87, 229, 0.05) 100%);
    padding: 3rem 2rem;
    text-align: center;
    margin-bottom: 3rem;
    border-radius: 16px;
    border: 2px solid var(--border-light);
    position: relative;
    overflow: hidden;
}

.leaderboard-header::before {
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

.leaderboard-header h1 {
    font-size: 3rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--accent-blue-light) 0%, var(--accent-purple-light) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 0.5rem 0;
    font-family: "JetBrains Mono", monospace;
}

.leaderboard-header p {
    font-size: 1.1rem;
    color: var(--text-light);
}

.rank-item {
    background: linear-gradient(135deg, var(--card-bg) 0%, var(--secondary-bg) 100%);
    border: 2px solid var(--border-color);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    gap: 2rem;
    position: relative;
    overflow: hidden;
}

.rank-item::before {
    content: "";
    position: absolute;
    top: -50%;
    right: -10%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(88, 166, 255, 0.05), transparent);
    border-radius: 50%;
    z-index: 0;
}

.rank-item:hover {
    transform: translateY(-6px);
    border-color: var(--accent-blue-light);
    box-shadow: 0 16px 32px rgba(88, 166, 255, 0.15);
}

.rank-badge {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 800;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}

.rank-1 { 
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    color: #1a1a1a;
    box-shadow: 0 12px 24px rgba(255, 215, 0, 0.4);
}

.rank-2 { 
    background: linear-gradient(135deg, #e8e8e8 0%, #ffffff 100%);
    color: #1a1a1a;
    box-shadow: 0 12px 24px rgba(200, 200, 200, 0.4);
}

.rank-3 { 
    background: linear-gradient(135deg, #cd7f32 0%, #d4a574 100%);
    color: #fff;
    box-shadow: 0 12px 24px rgba(205, 127, 50, 0.4);
}

.rank-other { 
    background: linear-gradient(135deg, #161b22, #0d1117);
    border: 2px solid var(--border-color);
    color: var(--text-white);
}

.team-info {
    flex-grow: 1;
    position: relative;
    z-index: 1;
}

.team-info h5 {
    font-weight: 700;
    font-size: 1.25rem;
    color: var(--text-light);
    margin: 0 0 0.5rem 0;
}

.team-meta {
    color: var(--text-muted);
    font-size: 0.95rem;
    margin: 0.5rem 0;
}

.badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}

.score-display {
    font-size: 3rem;
    font-weight: 800;
    color: var(--accent-blue-light);
    text-align: right;
    line-height: 1;
    position: relative;
    z-index: 1;
}

.score-label {
    color: var(--text-muted);
    font-size: 0.85rem;
    text-align: right;
    margin-top: 0.5rem;
}

@media (max-width: 768px) {
    .rank-item {
        flex-direction: column;
        text-align: center;
        padding: 1.5rem;
    }
    
    .score-display {
        font-size: 2.5rem;
        text-align: center;
    }
    
    .score-label {
        text-align: center;
    }
    
    .rank-badge {
        width: 70px;
        height: 70px;
        font-size: 1.75rem;
    }
}

.no-results {
    text-align: center;
    padding: 3rem 0;
}

.no-results i {
    font-size: 5rem;
    color: var(--text-muted);
    margin-bottom: 1rem;
    opacity: 0.5;
}

.btn-back {
    background: linear-gradient(135deg, var(--accent-blue), #0077cc);
    color: white;
    border: none;
    padding: 0.875rem 1.75rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-back:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 180, 255, 0.3);
    color: white;
    text-decoration: none;
}
</style>';

include __DIR__ . '/../../config/header.php';
?>

<div class="container my-5">
    <div class="leaderboard-header">
        <h1 class="mb-3">
            <i class="bi bi-trophy-fill"></i> LEADERBOARD
        </h1>
        <p class="mb-0">Ranking tim berdasarkan rata-rata nilai dari juri</p>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($rankings)): ?>
        <div class="no-results">
            <i class="bi bi-trophy"></i>
            <p class="text-muted mt-3">Belum ada tim yang mendapatkan nilai</p>
        </div>
    <?php else: ?>
        <?php 
        $ranked_count = 0;
        foreach ($rankings as $index => $team): 
            if ($team['avg_score'] !== null) {
                $ranked_count++;
            }
            $rank = $team['avg_score'] !== null ? $ranked_count : null;
            $rankClass = ($rank && $rank <= 3) ? "rank-{$rank}" : 'rank-other';
            $medal = $rank && $rank <= 3 ? ['🥇', '🥈', '🥉'][$rank - 1] : '';
        ?>
            <div class="rank-item">
                <div class="rank-badge <?= $rankClass ?>">
                    <?= $medal ?: ($rank ? "#{$rank}" : "—") ?>
                </div>
                <div class="team-info">
                    <h5>
                        <?= htmlspecialchars($team['team_name']) ?>
                        <?php if (!$team['is_verified']): ?>
                            <span class="badge" style="background: rgba(249, 115, 22, 0.2); color: #f97316; margin-left: 0.5rem;">
                                <i class="bi bi-info-circle"></i> Belum Terverifikasi
                            </span>
                        <?php endif; ?>
                    </h5>
                    <div class="team-meta">
                        <i class="bi bi-file-earmark-code"></i> 
                        <strong><?= $team['total_submissions'] ?></strong> Submissions
                        <span style="color: var(--border-color); margin: 0 0.5rem;">•</span>
                        <i class="bi bi-star-fill"></i> 
                        <strong><?= $team['total_ratings'] ?></strong> Penilaian
                    </div>
                    <?php if ($team['total_ratings'] > 0): ?>
                        <div class="mt-2">
                            <span class="badge" style="background: rgba(0, 180, 255, 0.2); color: var(--accent-blue);">
                                <i class="bi bi-arrow-up"></i> Max: <?= number_format($team['max_score'], 1) ?>
                            </span>
                            <span class="badge" style="background: rgba(249, 115, 22, 0.2); color: #f97316;">
                                <i class="bi bi-arrow-down"></i> Min: <?= number_format($team['min_score'], 1) ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="mt-2">
                            <span class="badge" style="background: rgba(156, 163, 175, 0.2); color: var(--text-muted);">
                                <i class="bi bi-hourglass"></i> Menunggu Penilaian
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if ($team['avg_score'] !== null): ?>
                        <div class="score-display">
                            <?= number_format($team['avg_score'], 1) ?>
                        </div>
                        <div class="score-label">Rata-rata Nilai</div>
                    <?php else: ?>
                        <div class="score-display" style="color: var(--text-muted);">—</div>
                        <div class="score-label">Belum Dinilai</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <div class="text-center mt-5">
        <a href="<?= isLoggedIn() ? ($_SESSION['role'] === 'PESERTA' ? 'peserta_dashboard.php' : ($_SESSION['role'] === 'PANITIA' ? 'panitia_dashboard.php' : 'juri_dashboard.php')) : 'index.php' ?>" 
           class="btn-back">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/public/js/main.js"></script>
</body>
</html>
